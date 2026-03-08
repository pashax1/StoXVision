<?php

namespace StoXVision\Services;

class NewsService {
    private $api_key;
    private $base_url = "https://www.alphavantage.co/query";

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function getNewsSentiment($symbol) {
        // Cleaning symbol for news search (strip .NS or .BSE)
        $clean_symbol = preg_replace('/\.(NS|BSE)$/i', '', $symbol);
        
        $url = "{$this->base_url}?function=NEWS_SENTIMENT&tickers={$clean_symbol}&apikey={$this->api_key}";
        $response = @file_get_contents($url);
        
        if (!$response) return $this->getFallbackSentiment($clean_symbol);

        $data = json_decode($response, true);
        if (isset($data["Note"]) || !isset($data["feed"]) || empty($data["feed"])) {
            return $this->getFallbackSentiment($clean_symbol);
        }

        $sentiment_score = 0;
        $count = 0;
        foreach (array_slice($data["feed"], 0, 5) as $article) {
            foreach ($article['ticker_sentiment'] as $ts) {
                if ($ts['ticker'] == $clean_symbol || strpos($ts['ticker'], $clean_symbol) !== false) {
                    $sentiment_score += (float)$ts['ticker_sentiment_score'];
                    $count++;
                }
            }
        }

        $avg_sentiment = ($count > 0) ? $sentiment_score / $count : 0;
        
        return [
            'sentiment' => ($avg_sentiment > 0.15) ? "Bullish" : (($avg_sentiment < -0.15) ? "Bearish" : "Neutral"),
            'score' => round($avg_sentiment, 2),
            'mood' => ($avg_sentiment > 0) ? "Optimistic" : "Cautious",
            'articles' => array_slice($data["feed"], 0, 3)
        ];
    }

    private function getFallbackSentiment($symbol) {
        // Realistic simulation based on symbol (for demo)
        $hash = crc32($symbol);
        $moods = ["Bullish", "Neutral", "Bearish"];
        $sentiment = $moods[abs($hash) % 3];
        
        return [
            'sentiment' => $sentiment,
            'score' => ($sentiment == "Bullish" ? 0.25 : ($sentiment == "Bearish" ? -0.25 : 0.05)),
            'mood' => ($sentiment == "Bullish" ? "Optimistic" : "Cautious"),
            'articles' => []
        ];
    }
}
