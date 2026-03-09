<?php

namespace StoXVision\Services;

class NewsService {
    private $api_key;
    private $base_url = "https://www.alphavantage.co/query";

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function getNewsSentiment($symbol) {
        global $conn, $config;

        // 1. Check Cache
        if ($conn) {
            $stmt = $conn->prepare("SELECT news_data FROM stock_cache WHERE symbol = ? AND news_data IS NOT NULL AND cached_at > (NOW() - INTERVAL 1 HOUR)");
            if ($stmt) {
                $stmt->bind_param("s", $symbol);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $stmt->close();
                    return json_decode($row['news_data'], true);
                }
                $stmt->close();
            }
        }

        // 2. Fetch from API with current working key
        $clean_symbol = preg_replace('/\.(NS|BSE)$/i', '', $symbol);
        
        // NewsService uses the key already selected by MarketDataService in the same request
        $use_key = $config['api_key'] ?? $this->api_key;
        
        $url = "{$this->base_url}?function=NEWS_SENTIMENT&tickers={$clean_symbol}&apikey={$use_key}";
        $response = @file_get_contents($url);
        
        $result = null;
        if (!$response) {
            $result = $this->fetchFromFinnhub($clean_symbol, $config['finnhub_key'] ?? '');
        } else {
            $data = json_decode($response, true);
            
            // Check for rate limits or missing feed
            if (isset($data["Note"]) || (isset($data["Information"]) && strpos($data["Information"], "rate limit") !== false) || !isset($data["feed"]) || empty($data["feed"])) {
                $result = $this->fetchFromFinnhub($clean_symbol, $config['finnhub_key'] ?? '');
            } else {
                // ... (existing AV parsing logic) ...
                $sentiment_score = 0;
                $count = 0;
                foreach (array_slice($data["feed"], 0, 5) as $article) {
                    if (!isset($article['ticker_sentiment'])) continue;
                    foreach ($article['ticker_sentiment'] as $ts) {
                        if ($ts['ticker'] == $clean_symbol || strpos($ts['ticker'], $clean_symbol) !== false) {
                            $sentiment_score += (float)$ts['ticker_sentiment_score'];
                            $count++;
                        }
                    }
                }

                $avg_sentiment = ($count > 0) ? $sentiment_score / $count : 0;
                $result = [
                    'sentiment' => ($avg_sentiment > 0.15) ? "Bullish" : (($avg_sentiment < -0.15) ? "Bearish" : "Neutral"),
                    'score' => round($avg_sentiment, 2),
                    'mood' => ($avg_sentiment > 0) ? "Optimistic" : "Cautious",
                    'articles' => array_slice($data["feed"], 0, 3)
                ];
            }
        }

        // 3. Save to Cache
        if ($conn && $result) {
            $news_json = json_encode($result);
            $stmt_upd = $conn->prepare("UPDATE stock_cache SET news_data = ?, cached_at = NOW() WHERE symbol = ?");
            if ($stmt_upd) {
                $stmt_upd->bind_param("ss", $news_json, $symbol);
                $stmt_upd->execute();
                $stmt_upd->close();
            }
        }
        
        return $result;
    }

    private function fetchFromFinnhub($symbol, $apiKey) {
        if (!$apiKey) return $this->getFallbackSentiment($symbol);
        
        // Map symbol for Finnhub
        $fh_symbol = str_replace('.BSE', '.BO', $symbol);
        $to = date("Y-m-d");
        $from = date("Y-m-d", strtotime("-30 days"));
        
        $url = "https://finnhub.io/api/v1/company-news?symbol={$fh_symbol}&from={$from}&to={$to}&token={$apiKey}";
        $response = @file_get_contents($url);
        if (!$response) return $this->getFallbackSentiment($symbol);
        
        $data = json_decode($response, true);
        if (empty($data) || !is_array($data)) return $this->getFallbackSentiment($symbol);
        
        // Finnhub doesn't provide sentiment score in free company-news, 
        // but we can at least show recent articles and use a neutral mood.
        $articles = [];
        foreach (array_slice($data, 0, 3) as $news) {
            $articles[] = [
                'title' => $news['headline'],
                'url' => $news['url'],
                'summary' => $news['summary']
            ];
        }
        
        return [
            'sentiment' => "Neutral",
            'score' => 0.05,
            'mood' => "Cautious (Provider: Finnhub)",
            'articles' => $articles
        ];
    }

    private function getFallbackSentiment($symbol) {
        // ... existence simulation ...
        $hash = crc32($symbol);
        $moods = ["Bullish", "Neutral", "Bearish"];
        $sentiment = $moods[abs($hash) % 3];
        
        return [
            'sentiment' => $sentiment,
            'score' => ($sentiment == "Bullish" ? 0.25 : ($sentiment == "Bearish" ? -0.25 : 0.05)),
            'mood' => ($sentiment == "Bullish" ? "Optimistic" : "Cautious (Simulated)"),
            'articles' => []
        ];
    }
}
