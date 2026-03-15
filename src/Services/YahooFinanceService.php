<?php

namespace StoXVision\Services;

class YahooFinanceService {
    
    public function getDailyTimeSeries($symbol) {
        // Map symbol to Yahoo format (.BSE -> .BO, .NS stays .NS)
        $yh_symbol = str_replace('.BSE', '.BO', $symbol);
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$yh_symbol}?interval=1d&range=60d";
        
        $options = [
            "http" => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n",
                "timeout" => 5
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if (!$response) return null;
        
        $data = json_decode($response, true);
        if (!isset($data['chart']['result'][0])) return null;
        
        $result = $data['chart']['result'][0];
        $timestamps = $result['timestamp'];
        $quotes = $result['indicators']['quote'][0];
        
        $normalized = [
            "Meta Data" => [
                "1. Information" => "High-reliability fallback (Yahoo Finance)",
                "2. Symbol" => $symbol
            ],
            "Time Series (Daily)" => []
        ];
        
        for ($i = count($timestamps) - 1; $i >= 0; $i--) {
            $date = date("Y-m-d", $timestamps[$i]);
            $normalized["Time Series (Daily)"][$date] = [
                "1. open" => (string)($quotes['open'][$i] ?? 0),
                "2. high" => (string)($quotes['high'][$i] ?? 0),
                "3. low" => (string)($quotes['low'][$i] ?? 0),
                "4. close" => (string)($quotes['close'][$i] ?? 0),
                "5. volume" => (string)($quotes['volume'][$i] ?? 0)
            ];
        }
        
        return $normalized;
    }
}
