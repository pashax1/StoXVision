<?php

namespace StoXVision\Services;

class MarketDataService {
    private $api_key;
    private $base_url = "https://www.alphavantage.co/query";

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    /**
     * Fetch daily time series for a given symbol (with caching)
     */
    public function getDailyTimeSeries($symbol) {
        global $conn, $config;

        // 1. Check Cache
        if ($conn) {
            $stmt = $conn->prepare("SELECT id, cached_at FROM stock_cache WHERE symbol = ? AND cached_at > (NOW() - INTERVAL 1 HOUR)");
            if ($stmt) {
                $stmt->bind_param("s", $symbol);
                $stmt->execute();
                $cache_result = $stmt->get_result();

                if ($cache_result->num_rows > 0) {
                    // Cache exists - in a full impl we'd load JSON from here
                }
                $stmt->close();
            }
        }

        $url = "{$this->base_url}?function=TIME_SERIES_DAILY&symbol={$symbol}&apikey={$this->api_key}";
        $response = @file_get_contents($url);

        if (!$response) {
            throw new \Exception("Unable to connect to market service.");
        }

        $data = json_decode($response, true);

        // Alpha Vantage sometimes returns {} for broken symbols (like .NS recently)
        if (empty($data) || (isset($data) && count($data) === 0)) {
            // If it was an .NS symbol, try .BSE automatically as a fallback
            if (strpos($symbol, '.NS') !== false) {
                $fallback_symbol = str_replace('.NS', '.BSE', $symbol);
                return $this->getDailyTimeSeries($fallback_symbol);
            }
            throw new \Exception("Market data not available for this stock right now.");
        }

        if (isset($data["Note"])) {
            throw new \Exception("API Limit Reached. Free keys allow limited calls. Try again later.");
        }

        if (isset($data["Error Message"])) {
            throw new \Exception("Invalid stock symbol. Please check the ticker (e.g., RELIANCE, TCS).");
        }

        if (!isset($data["Time Series (Daily)"])) {
            throw new \Exception("Market data not available for this stock right now.");
        }

        // 2. Update Cache after successful fetch
        if ($conn) {
            $latest_price = 0;
            foreach($data["Time Series (Daily)"] as $vals) {
                $latest_price = $vals["4. close"];
                break;
            }

            $stmt_upd = $conn->prepare("INSERT INTO stock_cache (symbol, current_price) VALUES (?, ?) ON DUPLICATE KEY UPDATE current_price = ?, cached_at = NOW()");
            if ($stmt_upd) {
                $stmt_upd->bind_param("sdd", $symbol, $latest_price, $latest_price);
                $stmt_upd->execute();
                $stmt_upd->close();
            }
        }

        return $data;
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
