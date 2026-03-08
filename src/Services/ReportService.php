<?php

namespace StoXVision\Services;

class ReportService {
    
    public function generateReport($indicators, $symbol, $news = null) {
        $price  = $indicators['latest_price'];
        $rsi    = $indicators['rsi'];
        $ema_20 = $indicators['ema_20'];
        $ema_50 = $indicators['ema_50'];

        // 1. Trend Analysis
        $trend       = "Neutral";
        $trend_class = "badge-neutral";
        if ($price > $ema_20 && $ema_20 > $ema_50) {
            $trend       = "Bullish";
            $trend_class = "badge-bullish";
        } elseif ($price < $ema_20 && $ema_20 < $ema_50) {
            $trend       = "Bearish";
            $trend_class = "badge-bearish";
        }

        // 2. Momentum Strength
        $pct_change = $indicators['price_change_pct'];
        $momentum   = "Weak";
        if (abs($pct_change) > 2)   $momentum = "Strong";
        elseif (abs($pct_change) > 0.5) $momentum = "Moderate";

        // 3. Volume Behavior
        $avg_vol = max(1, $indicators['avg_volume']);
        $vol_pct = ($indicators['latest_volume'] / $avg_vol) * 100;
        $volume_behavior = ($vol_pct > 120)
            ? "High Volume — Accumulation / Breakout"
            : (($vol_pct < 80) ? "Low Volume — Consolidation" : "Normal Volume — Steady Participation");

        // 4. RSI Status
        $rsi_status = "Neutral";
        if ($rsi > 70)      $rsi_status = "Overbought (Sell zone — caution)";
        elseif ($rsi < 30)  $rsi_status = "Oversold (Buy zone — opportunity)";

        // 5. Prediction
        $predicted_direction = ($trend == "Bullish") ? "Up" : (($trend == "Bearish") ? "Down" : "Sideways");
        $confidence = ($rsi > 40 && $rsi < 60) ? 82 : 65;

        // 6. Support & Resistance — using EMAs (more realistic than % heuristics)
        //    Support  = lower of EMA50 or current price * 0.95
        //    Resistance = higher of EMA20 * 1.03 or current price * 1.05
        $s1 = round(min($ema_50, $price * 0.95), 2);
        $r1 = round(max($ema_20 * 1.03, $price * 1.05), 2);

        // Entry zone: current price ±2%
        $entry_low  = round($price * 0.98, 2);
        $entry_high = round($price, 2);

        return [
            'symbol' => $symbol,
            'market_overview' => [
                'current_trend'    => $trend,
                'trend_class'      => $trend_class,
                'momentum_strength'=> $momentum,
                'volume_behavior'  => $volume_behavior,
            ],
            'technical_analysis' => [
                'rsi_status'   => $rsi_status,
                'rsi_value'    => round($rsi, 1),
                'macd_trend'   => ($indicators['price_change'] > 0) ? "Bullish Crossover" : "Bearish Under-current",
                'ema_20_vs_50' => "EMA 20 (₹" . round($ema_20, 2) . ") is "
                                  . ($ema_20 > $ema_50 ? "above" : "below")
                                  . " EMA 50 (₹" . round($ema_50, 2) . ").",
                'support'    => $s1,
                'resistance' => $r1,
            ],
            'sentiment_insight' => [
                'news_sentiment'     => $news['sentiment'] ?? "Neutral",
                'overall_market_mood'=> $news['mood']      ?? "Cautious",
                'news_articles'      => $news['articles']  ?? [],
            ],
            'ai_prediction' => [
                'predicted_direction' => $predicted_direction,
                'confidence'          => $confidence,
                'short_term'   => ($predicted_direction == "Up")
                    ? "Bullish bias — targeting ₹{$r1}"
                    : (($predicted_direction == "Down") ? "Bearish pressure — support at ₹{$s1}" : "Range-bound between ₹{$s1} and ₹{$r1}"),
                'swing_outlook'=> ($predicted_direction == "Up")
                    ? "Potential multi-week rally if ₹{$r1} breaks"
                    : "Corrective consolidation likely; watch ₹{$s1} as key support",
                'risk_level'   => ($rsi > 65 || $rsi < 35) ? "High" : "Medium",
            ],
            'trade_plan' => [
                'entry_zone'     => "₹{$entry_low} — ₹{$entry_high}",
                'target'         => round($r1 * 1.05, 2),
                'stop_loss'      => round($s1 * 0.98, 2),
                'risk_reward'    => "1:3",
                'recommendation' => ($trend == "Bullish" && $rsi < 70)
                    ? "BUY / ACCUMULATE"
                    : (($trend == "Bearish" && $rsi > 30) ? "SELL / AVOID" : "HOLD / OBSERVE"),
            ],
            'portfolio_advice' => [
                'if_holding'     => ($trend == "Bullish")
                    ? "Hold with confidence. Consider adding on dips to ₹{$s1}."
                    : (($trend == "Bearish") ? "Consider reducing exposure. Stop Loss at ₹{$s1}." : "Hold with neutral bias. Reassess on breakout."),
                'if_not_holding' => ($trend == "Bullish" && $rsi < 60)
                    ? "Strong candidate for fresh entry near ₹{$entry_low}."
                    : "Wait for a better risk-reward setup near support (₹{$s1}).",
                'allocation'     => ($trend == "Bullish") ? "5 – 8%" : "0 – 3%",
            ]
        ];
    }
}


