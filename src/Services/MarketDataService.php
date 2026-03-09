<?php

namespace StoXVision\Services;

use StoXVision\Services\YahooFinanceService;

class MarketDataService {
    private $api_key;
    private $base_url = "https://www.alphavantage.co/query";

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    /**
     * Fetch daily time series for a given symbol (with caching)
     */
    public function getDailyTimeSeries($symbol, $allow_fallback = true) {
        global $conn, $config;

        // 1. Check Cache
        if ($conn) {
            $stmt = $conn->prepare("SELECT raw_data, cached_at FROM stock_cache WHERE symbol = ? AND cached_at > (NOW() - INTERVAL 1 HOUR)");
            if ($stmt) {
                $stmt->bind_param("s", $symbol);
                $stmt->execute();
                $cache_result = $stmt->get_result();
                if ($cache_result->num_rows > 0) {
                    $cached = $cache_result->fetch_assoc();
                    $stmt->close();
                    return json_decode($cached['raw_data'], true);
                }
                $stmt->close();
            }
        }

        // 2. Fetch from API with automatic rotation
        $keys = $config['api_keys'] ?? [$this->api_key];
        $last_error = "";
        $startIndex = $_SESSION['current_key_idx'] ?? 0;
        $keyCount = count($keys);

        $success_data = null;
        $is_rate_limit = false;

        for ($i = 0; $i < $keyCount; $i++) {
            $idx = ($startIndex + $i) % $keyCount;
            $key = $keys[$idx];
            
            $url = "{$this->base_url}?function=TIME_SERIES_DAILY&symbol={$symbol}&apikey={$key}";
            $response = @file_get_contents($url);

            if (!$response) {
                $last_error = "Connection failed";
                continue;
            }

            $data = json_decode($response, true);

            // Handle API Rate Limits
            if (isset($data["Note"]) && strpos($data["Note"], "frequency") !== false) {
                $last_error = "Frequency limit (5/min). Retrying next key...";
                $_SESSION['current_key_idx'] = ($idx + 1) % $keyCount;
                continue;
            }

            if ((isset($data["Note"]) && strpos($data["Note"], "daily") !== false) || 
                (isset($data["Information"]) && strpos($data["Information"], "25 requests per day") !== false)) {
                $is_rate_limit = true;
                $last_error = "Daily API limit reached on key " . substr($key, 0, 4) . "***";
                $_SESSION['current_key_idx'] = ($idx + 1) % $keyCount;
                continue; 
            }

            // Handle Invalid Symbols specifically
            if (isset($data["Error Message"]) || (isset($data["Information"]) && strpos($data["Information"], "Invalid API call") !== false)) {
                $last_error = "Invalid symbol: $symbol. Please check the ticker.";
                break; // Don't try 10 keys for an invalid symbol!
            }

            if (!isset($data["Time Series (Daily)"])) {
                if (isset($data["Information"])) {
                    $last_error = "API Info: " . $data["Information"];
                } elseif (isset($data["Note"])) {
                    $last_error = "API Note: " . $data["Note"];
                } else {
                    $last_error = "Market data not available for $symbol.";
                }
                continue;
            }

            // Success!
            $success_data = $data;
            $_SESSION['current_key_idx'] = $idx;
            $config['api_key'] = $key;
            $this->api_key = $key;
            break;
        }

        // 2b. Final Fallback to Finnhub if AV fails
        if (!$success_data && isset($config['finnhub_key'])) {
            $success_data = $this->fetchFromFinnhub($symbol, $config['finnhub_key']);
        }
        
        // 2c. Last Resort: Yahoo Finance (High reliability for Indian stocks)
        if (!$success_data) {
            $yahoo = new YahooFinanceService();
            $success_data = $yahoo->getDailyTimeSeries($symbol);
        }

        // 3. Symbol Fallback Logic (Swap .NS <-> .BSE)
        if (!$success_data && $allow_fallback) {
            // If we hit here, it means AV, Finnhub, AND Yahoo failed for the current symbol.
            // We try the other exchange suffix.
            $fallback_symbol = null;
            if (strpos($symbol, '.NS') !== false) {
                $fallback_symbol = str_replace('.NS', '.BSE', $symbol);
            } elseif (strpos($symbol, '.BSE') !== false) {
                $fallback_symbol = str_replace('.BSE', '.NS', $symbol);
            }

            if ($fallback_symbol) {
                return $this->getDailyTimeSeries($fallback_symbol, false);
            }
        }

        if (!$success_data) {
            throw new \Exception($last_error ?: "Market data not available.");
        }

        // 4. Update Cache
        if ($conn) {
            $latest_price = 0;
            $raw_json = json_encode($success_data);
            foreach($success_data["Time Series (Daily)"] as $vals) {
                $latest_price = $vals["4. close"];
                break;
            }
            $stmt_upd = $conn->prepare("INSERT INTO stock_cache (symbol, current_price, raw_data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE current_price = ?, raw_data = ?, cached_at = NOW()");
            if ($stmt_upd) {
                $stmt_upd->bind_param("sdssd", $symbol, $latest_price, $raw_json, $latest_price, $raw_json);
                $stmt_upd->execute();
                $stmt_upd->close();
            }
        }

        return $success_data;
    }

    /**
     * Backup fetcher using Finnhub API (Generous free tier)
     * Normalizes data to Alpha Vantage format for compatibility
     */
    private function fetchFromFinnhub($symbol, $apiKey) {
        // Map symbol to Finnhub format (e.g. RELIANCE.BSE -> RELIANCE.BO for some providers, 
        // but Finnhub often uses NSE:RELIANCE or just RELIANCE.NS)
        $fh_symbol = str_replace('.BSE', '.BO', $symbol);
        
        $to = time();
        $from = $to - (60 * 60 * 24 * 60); // 60 days of data
        $url = "https://finnhub.io/api/v1/stock/candle?symbol={$fh_symbol}&resolution=D&from={$from}&to={$to}&token={$apiKey}";
        
        $response = @file_get_contents($url);
        if (!$response) return null;
        
        $data = json_decode($response, true);
        if (!isset($data['s']) || $data['s'] !== 'ok') return null;
        
        // Normalize to Alpha Vantage format
        $normalized = [
            "Meta Data" => ["2. Symbol" => $symbol, "1. Information" => "Multi-source fallback (Finnhub)"],
            "Time Series (Daily)" => []
        ];
        
        // Finnhub returns arrays of t, o, h, l, c, v
        for ($i = count($data['t']) - 1; $i >= 0; $i--) {
            $date = date("Y-m-d", $data['t'][$i]);
            $normalized["Time Series (Daily)"][$date] = [
                "1. open" => (string)$data['o'][$i],
                "2. high" => (string)$data['h'][$i],
                "3. low" => (string)$data['l'][$i],
                "4. close" => (string)$data['c'][$i],
                "5. volume" => (string)$data['v'][$i]
            ];
        }
        
        return $normalized;
    }

    /**
     * Calculate technical indicators from time series data
     */
    public function calculateIndicators($time_series) {
        $dates = [];
        $closes = [];
        $volumes = [];
        $highs = [];
        $lows = [];

        $count = 0;
        foreach ($time_series as $date => $values) {
            if ($count == 50) break; // Get 50 days for EMA 50
            $dates[] = date("M d", strtotime($date));
            $closes[] = floatval($values["4. close"]);
            $volumes[] = intval($values["5. volume"]);
            $highs[] = floatval($values["2. high"]);
            $lows[] = floatval($values["3. low"]);
            $count++;
        }

        // Reverse to chronological order for calculation
        $dates = array_reverse($dates);
        $closes = array_reverse($closes);
        $volumes = array_reverse($volumes);
        $highs = array_reverse($highs);
        $lows = array_reverse($lows);

        $latest_price = end($closes);
        $prev_price = (count($closes) > 1) ? $closes[count($closes)-2] : $latest_price;
        $price_change = $latest_price - $prev_price;
        $price_change_pct = ($prev_price != 0) ? ($price_change / $prev_price) * 100 : 0;

        $ma_20 = (count($closes) >= 20) ? array_sum(array_slice($closes, -20)) / 20 : array_sum($closes) / count($closes);
        $ma_50 = (count($closes) >= 50) ? array_sum(array_slice($closes, -50)) / 50 : array_sum($closes) / count($closes);

        // Simple RSI (14-day)
        $rsi = $this->calculateRSI($closes, 14);

        // EMA Calculation
        $ema_20 = $this->calculateEMA($closes, 20);
        $ema_50 = $this->calculateEMA($closes, 50);

        return [
            'dates' => array_slice($dates, -20), // Return last 20 for chart
            'closes' => array_slice($closes, -20),
            'latest_price' => $latest_price,
            'price_change' => $price_change,
            'price_change_pct' => $price_change_pct,
            'ma_20' => $ma_20,
            'ma_50' => $ma_50,
            'ema_20' => $ema_20,
            'ema_50' => $ema_50,
            'rsi' => $rsi,
            'latest_volume' => end($volumes),
            'avg_volume' => array_sum($volumes) / count($volumes),
            'high' => end($highs),
            'low' => end($lows)
        ];
    }

    private function calculateRSI($prices, $period = 14) {
        if (count($prices) < $period + 1) return 50;

        $gains = [];
        $losses = [];

        for ($i = 1; $i < count($prices); $i++) {
            $diff = $prices[$i] - $prices[$i - 1];
            if ($diff > 0) {
                $gains[] = $diff;
                $losses[] = 0;
            } else {
                $gains[] = 0;
                $losses[] = abs($diff);
            }
        }

        $avg_gain = array_sum(array_slice($gains, -$period)) / $period;
        $avg_loss = array_sum(array_slice($losses, -$period)) / $period;

        if ($avg_loss == 0) return 100;
        $rs = $avg_gain / $avg_loss;
        return 100 - (100 / (1 + $rs));
    }

    private function calculateEMA($prices, $period) {
        if (count($prices) < $period) return array_sum($prices) / count($prices);

        $k = 2 / ($period + 1);
        $ema = array_sum(array_slice($prices, 0, $period)) / $period; // Start with SMA

        for ($i = $period; $i < count($prices); $i++) {
            $ema = ($prices[$i] * $k) + ($ema * (1 - $k));
        }

        return $ema;
    }
}
