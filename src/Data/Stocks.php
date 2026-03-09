<?php

namespace StoXVision\Data;

class Stocks {

    /**
     * Returns the full stock directory.
     * Format: [ 'common_name' => ['symbol' => 'TICKER.NS', 'name' => 'Full Company Name', 'sector' => 'Sector'] ]
     */
    public static function getDirectory(): array {
        return [
            // ── IT & Technology ────────────────────────────────────────────────
            'TCS'            => ['symbol' => 'TCS.BSE',        'name' => 'Tata Consultancy Services',     'sector' => 'IT'],
            'TATA CONSULTANCY' => ['symbol' => 'TCS.BSE',      'name' => 'Tata Consultancy Services',     'sector' => 'IT'],
            'INFY'           => ['symbol' => 'INFY.BSE',       'name' => 'Infosys Ltd',                   'sector' => 'IT'],
            'INFOSYS'        => ['symbol' => 'INFY.BSE',       'name' => 'Infosys Ltd',                   'sector' => 'IT'],
            'WIPRO'          => ['symbol' => 'WIPRO.BSE',      'name' => 'Wipro Ltd',                     'sector' => 'IT'],
            'HCLTECH'        => ['symbol' => 'HCLTECH.BSE',    'name' => 'HCL Technologies',              'sector' => 'IT'],
            'HCL'            => ['symbol' => 'HCLTECH.BSE',    'name' => 'HCL Technologies',              'sector' => 'IT'],
            'TECHM'          => ['symbol' => 'TECHM.BSE',      'name' => 'Tech Mahindra',                 'sector' => 'IT'],
            'TECH MAHINDRA'  => ['symbol' => 'TECHM.BSE',      'name' => 'Tech Mahindra',                 'sector' => 'IT'],
            'LTI'            => ['symbol' => 'LTIM.BSE',       'name' => 'LTIMindtree',                   'sector' => 'IT'],
            'LTIMINDTREE'    => ['symbol' => 'LTIM.BSE',       'name' => 'LTIMindtree',                   'sector' => 'IT'],
            'MPHASIS'        => ['symbol' => 'MPHASIS.BSE',    'name' => 'Mphasis Ltd',                   'sector' => 'IT'],
            'PERSISTENT'     => ['symbol' => 'PERSISTENT.BSE', 'name' => 'Persistent Systems',            'sector' => 'IT'],
            'COFORGE'        => ['symbol' => 'COFORGE.BSE',    'name' => 'Coforge Ltd',                   'sector' => 'IT'],

            // ── Banking & Finance ───────────────────────────────────────────────
            'HDFCBANK'       => ['symbol' => 'HDFCBANK.BSE',   'name' => 'HDFC Bank',                     'sector' => 'Banking'],
            'HDFC BANK'      => ['symbol' => 'HDFCBANK.BSE',   'name' => 'HDFC Bank',                     'sector' => 'Banking'],
            'HDFC'           => ['symbol' => 'HDFCBANK.BSE',   'name' => 'HDFC Bank',                     'sector' => 'Banking'],
            'ICICIBANK'      => ['symbol' => 'ICICIBANK.BSE',  'name' => 'ICICI Bank',                    'sector' => 'Banking'],
            'ICICI'          => ['symbol' => 'ICICIBANK.BSE',  'name' => 'ICICI Bank',                    'sector' => 'Banking'],
            'SBIN'           => ['symbol' => 'SBIN.BSE',       'name' => 'State Bank of India',           'sector' => 'Banking'],
            'SBI'            => ['symbol' => 'SBIN.BSE',       'name' => 'State Bank of India',           'sector' => 'Banking'],
            'STATE BANK'     => ['symbol' => 'SBIN.BSE',       'name' => 'State Bank of India',           'sector' => 'Banking'],
            'AXISBANK'       => ['symbol' => 'AXISBANK.BSE',   'name' => 'Axis Bank',                     'sector' => 'Banking'],
            'AXIS'           => ['symbol' => 'AXISBANK.BSE',   'name' => 'Axis Bank',                     'sector' => 'Banking'],
            'AXIS BANK'      => ['symbol' => 'AXISBANK.BSE',   'name' => 'Axis Bank',                     'sector' => 'Banking'],
            'KOTAKBANK'      => ['symbol' => 'KOTAKBANK.BSE',  'name' => 'Kotak Mahindra Bank',           'sector' => 'Banking'],
            'KOTAK'          => ['symbol' => 'KOTAKBANK.BSE',  'name' => 'Kotak Mahindra Bank',           'sector' => 'Banking'],
            'INDUSINDBK'     => ['symbol' => 'INDUSINDBK.BSE', 'name' => 'IndusInd Bank',                 'sector' => 'Banking'],
            'INDUSIND'       => ['symbol' => 'INDUSINDBK.BSE', 'name' => 'IndusInd Bank',                 'sector' => 'Banking'],
            'BANDHANBNK'     => ['symbol' => 'BANDHANBNK.BSE', 'name' => 'Bandhan Bank',                  'sector' => 'Banking'],
            'FEDERALBNK'     => ['symbol' => 'FEDERALBNK.BSE', 'name' => 'Federal Bank',                  'sector' => 'Banking'],
            'FEDERAL BANK'   => ['symbol' => 'FEDERALBNK.BSE', 'name' => 'Federal Bank',                  'sector' => 'Banking'],
            'YESBANK'        => ['symbol' => 'YESBANK.BSE',    'name' => 'Yes Bank',                      'sector' => 'Banking'],
            'YES BANK'       => ['symbol' => 'YESBANK.BSE',    'name' => 'Yes Bank',                      'sector' => 'Banking'],
            'PNB'            => ['symbol' => 'PNB.BSE',        'name' => 'Punjab National Bank',          'sector' => 'Banking'],
            'PUNJAB NATIONAL' => ['symbol' => 'PNB.BSE',       'name' => 'Punjab National Bank',          'sector' => 'Banking'],
            'BAJFINANCE'     => ['symbol' => 'BAJFINANCE.BSE', 'name' => 'Bajaj Finance',                 'sector' => 'NBFC'],
            'BAJAJ FINANCE'  => ['symbol' => 'BAJFINANCE.BSE', 'name' => 'Bajaj Finance',                 'sector' => 'NBFC'],
            'BAJAJFINSV'     => ['symbol' => 'BAJAJFINSV.BSE', 'name' => 'Bajaj Finserv',                 'sector' => 'NBFC'],
            'BAJAJ FINSERV'  => ['symbol' => 'BAJAJFINSV.BSE', 'name' => 'Bajaj Finserv',                 'sector' => 'NBFC'],
            'MUTHOOTFIN'     => ['symbol' => 'MUTHOOTFIN.BSE', 'name' => 'Muthoot Finance',               'sector' => 'NBFC'],
            'SBICARD'        => ['symbol' => 'SBICARD.BSE',    'name' => 'SBI Cards',                     'sector' => 'NBFC'],

            // ── Energy & Oil ────────────────────────────────────────────────────
            'RELIANCE'       => ['symbol' => 'RELIANCE.BSE',   'name' => 'Reliance Industries',           'sector' => 'Energy'],
            'RIL'            => ['symbol' => 'RELIANCE.BSE',   'name' => 'Reliance Industries',           'sector' => 'Energy'],
            'ONGC'           => ['symbol' => 'ONGC.BSE',       'name' => 'Oil & Natural Gas Corp',        'sector' => 'Energy'],
            'IOC'            => ['symbol' => 'IOC.BSE',        'name' => 'Indian Oil Corporation',        'sector' => 'Energy'],
            'BPCL'           => ['symbol' => 'BPCL.BSE',       'name' => 'Bharat Petroleum',              'sector' => 'Energy'],
            'GAIL'           => ['symbol' => 'GAIL.BSE',       'name' => 'GAIL India',                    'sector' => 'Energy'],
            'COALINDIA'      => ['symbol' => 'COALINDIA.BSE',  'name' => 'Coal India',                    'sector' => 'Energy'],
            'COAL INDIA'     => ['symbol' => 'COALINDIA.BSE',  'name' => 'Coal India',                    'sector' => 'Energy'],
            'ADANIGREEN'     => ['symbol' => 'ADANIGREEN.BSE', 'name' => 'Adani Green Energy',            'sector' => 'Energy'],
            'ADANIPORTS'     => ['symbol' => 'ADANIPORTS.BSE', 'name' => 'Adani Ports & SEZ',             'sector' => 'Infrastructure'],
            'ADANIENT'       => ['symbol' => 'ADANIENT.BSE',   'name' => 'Adani Enterprises',             'sector' => 'Conglomerate'],
            'ADANI'          => ['symbol' => 'ADANIENT.BSE',   'name' => 'Adani Enterprises',             'sector' => 'Conglomerate'],
            'NTPC'           => ['symbol' => 'NTPC.BSE',       'name' => 'NTPC Ltd (Power)',              'sector' => 'Energy'],
            'POWERGRID'      => ['symbol' => 'POWERGRID.BSE',  'name' => 'Power Grid Corporation',        'sector' => 'Energy'],
            'TATAPOWER'      => ['symbol' => 'TATAPOWER.BSE',  'name' => 'Tata Power',                    'sector' => 'Energy'],

            // ── Automobile ──────────────────────────────────────────────────────
            'MARUTI'         => ['symbol' => 'MARUTI.BSE',     'name' => 'Maruti Suzuki',                 'sector' => 'Auto'],
            'MARUTI SUZUKI'  => ['symbol' => 'MARUTI.BSE',     'name' => 'Maruti Suzuki',                 'sector' => 'Auto'],
            'TATAMOTORS'     => ['symbol' => 'TATAMOTORS.BSE', 'name' => 'Tata Motors',                   'sector' => 'Auto'],
            'TATA MOTORS'    => ['symbol' => 'TATAMOTORS.BSE', 'name' => 'Tata Motors',                   'sector' => 'Auto'],
            'M&M'            => ['symbol' => 'M&M.BSE',        'name' => 'Mahindra & Mahindra',           'sector' => 'Auto'],
            'MAHINDRA'       => ['symbol' => 'M&M.BSE',        'name' => 'Mahindra & Mahindra',           'sector' => 'Auto'],
            'HEROMOTOCO'     => ['symbol' => 'HEROMOTOCO.BSE', 'name' => 'Hero MotoCorp',                 'sector' => 'Auto'],
            'HERO'           => ['symbol' => 'HEROMOTOCO.BSE', 'name' => 'Hero MotoCorp',                 'sector' => 'Auto'],
            'BAJAJ-AUTO'     => ['symbol' => 'BAJAJ-AUTO.BSE', 'name' => 'Bajaj Auto',                    'sector' => 'Auto'],
            'BAJAJ AUTO'     => ['symbol' => 'BAJAJ-AUTO.BSE', 'name' => 'Bajaj Auto',                    'sector' => 'Auto'],
            'EICHERMOT'      => ['symbol' => 'EICHERMOT.BSE',  'name' => 'Eicher Motors (Royal Enfield)', 'sector' => 'Auto'],
            'ROYAL ENFIELD'  => ['symbol' => 'EICHERMOT.BSE',  'name' => 'Eicher Motors (Royal Enfield)', 'sector' => 'Auto'],
            'ASHOKLEY'       => ['symbol' => 'ASHOKLEY.BSE',   'name' => 'Ashok Leyland',                 'sector' => 'Auto'],

            // ── Pharma & Healthcare ─────────────────────────────────────────────
            'SUNPHARMA'      => ['symbol' => 'SUNPHARMA.BSE',  'name' => 'Sun Pharmaceutical',            'sector' => 'Pharma'],
            'SUN PHARMA'     => ['symbol' => 'SUNPHARMA.BSE',  'name' => 'Sun Pharmaceutical',            'sector' => 'Pharma'],
            'DRREDDY'        => ['symbol' => 'DRREDDY.BSE',    'name' => "Dr. Reddy's Laboratories",      'sector' => 'Pharma'],
            'DR REDDY'       => ['symbol' => 'DRREDDY.BSE',    'name' => "Dr. Reddy's Laboratories",      'sector' => 'Pharma'],
            'CIPLA'          => ['symbol' => 'CIPLA.BSE',      'name' => 'Cipla Ltd',                     'sector' => 'Pharma'],
            'DIVISLAB'       => ['symbol' => 'DIVISLAB.BSE',   'name' => "Divi's Laboratories",          'sector' => 'Pharma'],
            'BIOCON'         => ['symbol' => 'BIOCON.BSE',     'name' => 'Biocon Ltd',                    'sector' => 'Pharma'],
            'APOLLOHOSP'     => ['symbol' => 'APOLLOHOSP.BSE', 'name' => 'Apollo Hospitals',              'sector' => 'Healthcare'],
            'APOLLO'         => ['symbol' => 'APOLLOHOSP.BSE', 'name' => 'Apollo Hospitals',              'sector' => 'Healthcare'],
            'FORTIS'         => ['symbol' => 'FORTIS.BSE',     'name' => 'Fortis Healthcare',             'sector' => 'Healthcare'],
            'LUPIN'          => ['symbol' => 'LUPIN.BSE',      'name' => 'Lupin Ltd',                     'sector' => 'Pharma'],
            'AUROPHARMA'     => ['symbol' => 'AUROPHARMA.BSE', 'name' => 'Aurobindo Pharma',              'sector' => 'Pharma'],
            'TORNTPHARM'     => ['symbol' => 'TORNTPHARM.BSE', 'name' => 'Torrent Pharmaceuticals',       'sector' => 'Pharma'],

            // ── Consumer Goods & FMCG ───────────────────────────────────────────
            'HINDUNILVR'     => ['symbol' => 'HINDUNILVR.BSE', 'name' => 'Hindustan Unilever (HUL)',      'sector' => 'FMCG'],
            'HUL'            => ['symbol' => 'HINDUNILVR.BSE', 'name' => 'Hindustan Unilever (HUL)',      'sector' => 'FMCG'],
            'HINDUSTAN UNILEVER' => ['symbol' => 'HINDUNILVR.BSE', 'name' => 'Hindustan Unilever',        'sector' => 'FMCG'],
            'ITC'            => ['symbol' => 'ITC.BSE',        'name' => 'ITC Ltd',                       'sector' => 'FMCG'],
            'NESTLEIND'      => ['symbol' => 'NESTLEIND.BSE',  'name' => 'Nestle India',                  'sector' => 'FMCG'],
            'NESTLE'         => ['symbol' => 'NESTLEIND.BSE',  'name' => 'Nestle India',                  'sector' => 'FMCG'],
            'BRITANNIA'      => ['symbol' => 'BRITANNIA.BSE',  'name' => 'Britannia Industries',          'sector' => 'FMCG'],
            'DABUR'          => ['symbol' => 'DABUR.BSE',      'name' => 'Dabur India',                   'sector' => 'FMCG'],
            'COLPAL'         => ['symbol' => 'COLPAL.BSE',     'name' => 'Colgate-Palmolive India',       'sector' => 'FMCG'],
            'COLGATE'        => ['symbol' => 'COLPAL.BSE',     'name' => 'Colgate-Palmolive India',       'sector' => 'FMCG'],
            'MARICO'         => ['symbol' => 'MARICO.BSE',     'name' => 'Marico Ltd',                    'sector' => 'FMCG'],
            'GODREJCP'       => ['symbol' => 'GODREJCP.BSE',   'name' => 'Godrej Consumer Products',      'sector' => 'FMCG'],
            'GODREJ'         => ['symbol' => 'GODREJCP.BSE',   'name' => 'Godrej Consumer Products',      'sector' => 'FMCG'],
            'EMAMILTD'       => ['symbol' => 'EMAMILTD.BSE',   'name' => 'Emami Ltd',                     'sector' => 'FMCG'],
            'TATACONSUM'     => ['symbol' => 'TATACONSUM.BSE', 'name' => 'Tata Consumer Products',        'sector' => 'FMCG'],

            // ── Metals & Mining ─────────────────────────────────────────────────
            'TATASTEEL'      => ['symbol' => 'TATASTEEL.BSE',  'name' => 'Tata Steel',                    'sector' => 'Metals'],
            'TATA STEEL'     => ['symbol' => 'TATASTEEL.BSE',  'name' => 'Tata Steel',                    'sector' => 'Metals'],
            'JSWSTEEL'       => ['symbol' => 'JSWSTEEL.BSE',   'name' => 'JSW Steel',                     'sector' => 'Metals'],
            'JSW'            => ['symbol' => 'JSWSTEEL.BSE',   'name' => 'JSW Steel',                     'sector' => 'Metals'],
            'HINDALCO'       => ['symbol' => 'HINDALCO.BSE',   'name' => 'Hindalco Industries',           'sector' => 'Metals'],
            'VEDL'           => ['symbol' => 'VEDL.BSE',       'name' => 'Vedanta Ltd',                   'sector' => 'Metals'],
            'VEDANTA'        => ['symbol' => 'VEDL.BSE',       'name' => 'Vedanta Ltd',                   'sector' => 'Metals'],
            'NMDC'           => ['symbol' => 'NMDC.BSE',       'name' => 'NMDC Ltd',                      'sector' => 'Metals'],
            'SAIL'           => ['symbol' => 'SAIL.BSE',       'name' => 'Steel Authority of India',      'sector' => 'Metals'],

            // ── Infrastructure & Real Estate ────────────────────────────────────
            'LT'             => ['symbol' => 'LT.BSE',         'name' => 'Larsen & Toubro',               'sector' => 'Infrastructure'],
            'LARSEN'         => ['symbol' => 'LT.BSE',         'name' => 'Larsen & Toubro',               'sector' => 'Infrastructure'],
            'ULTRACEMCO'     => ['symbol' => 'ULTRACEMCO.BSE', 'name' => 'UltraTech Cement',              'sector' => 'Cement'],
            'ULTRATECH'      => ['symbol' => 'ULTRACEMCO.BSE', 'name' => 'UltraTech Cement',              'sector' => 'Cement'],
            'SHREECEM'       => ['symbol' => 'SHREECEM.BSE',   'name' => 'Shree Cement',                  'sector' => 'Cement'],
            'AMBUJACEM'      => ['symbol' => 'AMBUJACEM.BSE',  'name' => 'Ambuja Cements',                'sector' => 'Cement'],
            'AMBUJA'         => ['symbol' => 'AMBUJACEM.BSE',  'name' => 'Ambuja Cements',                'sector' => 'Cement'],
            'ACC'            => ['symbol' => 'ACC.BSE',        'name' => 'ACC Ltd (Cement)',              'sector' => 'Cement'],
            'DLF'            => ['symbol' => 'DLF.BSE',        'name' => 'DLF Ltd',                       'sector' => 'Real Estate'],
            'GODREJPROP'     => ['symbol' => 'GODREJPROP.BSE', 'name' => 'Godrej Properties',             'sector' => 'Real Estate'],
            'OBEROIRLTY'     => ['symbol' => 'OBEROIRLTY.BSE', 'name' => 'Oberoi Realty',                 'sector' => 'Real Estate'],
            'PRESTIGE'       => ['symbol' => 'PRESTIGE.BSE',   'name' => 'Prestige Estates',              'sector' => 'Real Estate'],

            // ── Telecom ─────────────────────────────────────────────────────────
            'BHARTIARTL'     => ['symbol' => 'BHARTIARTL.BSE', 'name' => 'Bharti Airtel',                 'sector' => 'Telecom'],
            'AIRTEL'         => ['symbol' => 'BHARTIARTL.BSE', 'name' => 'Bharti Airtel',                 'sector' => 'Telecom'],
            'BHARTI'         => ['symbol' => 'BHARTIARTL.BSE', 'name' => 'Bharti Airtel',                 'sector' => 'Telecom'],
            'IDEA'           => ['symbol' => 'IDEA.BSE',       'name' => 'Vodafone Idea',                 'sector' => 'Telecom'],
            'VODAFONE'       => ['symbol' => 'IDEA.BSE',       'name' => 'Vodafone Idea',                 'sector' => 'Telecom'],

            // ── Insurance ───────────────────────────────────────────────────────
            'HDFCLIFE'       => ['symbol' => 'HDFCLIFE.BSE',   'name' => 'HDFC Life Insurance',           'sector' => 'Insurance'],
            'SBILIFE'        => ['symbol' => 'SBILIFE.BSE',    'name' => 'SBI Life Insurance',            'sector' => 'Insurance'],
            'ICICIPRULI'     => ['symbol' => 'ICICIPRULI.BSE', 'name' => 'ICICI Prudential Life',         'sector' => 'Insurance'],
            'GICRE'          => ['symbol' => 'GICRE.BSE',      'name' => 'General Insurance Corp',        'sector' => 'Insurance'],
            'NIACL'          => ['symbol' => 'NIACL.BSE',      'name' => 'New India Assurance',           'sector' => 'Insurance'],

            // ── Exchange Traded Products (Indices) ──────────────────────────────
            'NIFTY'          => ['symbol' => '^NSEI',         'name' => 'Nifty 50 Index',               'sector' => 'Index'],
            'SENSEX'         => ['symbol' => '^BSESN',        'name' => 'BSE Sensex Index',             'sector' => 'Index'],
            'ASIANPAINT'     => ['symbol' => 'ASIANPAINT.BSE', 'name' => 'Asian Paints',                'sector' => 'Consumer Goods'],
            'TITAN'          => ['symbol' => 'TITAN.BSE',      'name' => 'Titan Company',               'sector' => 'Consumer Goods'],
            'GRASIM'         => ['symbol' => 'GRASIM.BSE',     'name' => 'Grasim Industries',           'sector' => 'Conglomerate'],
            'UPL'            => ['symbol' => 'UPL.BSE',        'name' => 'UPL Ltd',                     'sector' => 'Chemicals'],

        ];
    }

    /**
     * Search directory by partial name/key match.
     * Returns up to $limit results sorted by relevance.
     */
    public static function search(string $query, int $limit = 8): array {
        $query = strtoupper(trim($query));
        if (strlen($query) < 1) return [];

        $results = [];
        $seen_symbols = [];

        foreach (self::getDirectory() as $key => $stock) {
            // Skip duplicate symbols (same ticker, different alias)
            if (in_array($stock['symbol'], $seen_symbols)) continue;

            $score = 0;

            // Exact key match — highest priority
            if ($key === $query) $score = 100;
            // Key starts with query
            elseif (strpos($key, $query) === 0) $score = 80;
            // Name starts with query
            elseif (stripos($stock['name'], $query) === 0) $score = 70;
            // Key contains query
            elseif (strpos($key, $query) !== false) $score = 50;
            // Name contains query
            elseif (stripos($stock['name'], $query) !== false) $score = 30;

            if ($score > 0) {
                $results[] = [
                    'symbol'   => $stock['symbol'],
                    'name'     => $stock['name'],
                    'sector'   => $stock['sector'],
                    'score'    => $score
                ];
                $seen_symbols[] = $stock['symbol'];
            }
        }

        // Sort by relevance
        usort($results, fn($a, $b) => $b['score'] - $a['score']);

        return array_slice($results, 0, $limit);
    }

    /**
     * Returns a list of stocks in the Nifty 50 index.
     * Note: This is a representative list for this application.
     */
    public static function getNifty50(): array {
        $nifty50_keys = [
            'RELIANCE', 'TCS', 'HDFCBANK', 'ICICIBANK', 'INFY', 
            'HINDUNILVR', 'ITC', 'SBIN', 'BHARTIARTL', 'KOTAKBANK',
            'LT', 'AXISBANK', 'BAJFINANCE', 'ASIANPAINT', 'MARUTI',
            'SUNPHARMA', 'TITAN', 'HCLTECH', 'ADANIENT', 'TATAMOTORS',
            'JSWSTEEL', 'NTPC', 'TATASTEEL', 'POWERGRID', 'M&M',
            'ULTRACEMCO', 'ONGC', 'ADANIPORTS', 'NESTLEIND', 'GRASIM',
            'BRITANNIA', 'COALINDIA', 'HDFCLIFE', 'SBILIFE', 'DRREDDY',
            'BAJAJFINSV', 'WIPRO', 'CIPLA', 'TATASTAGE', 'DIVISLAB',
            'BPCL', 'APOLLOHOSP', 'EICHERMOT', 'HEROMOTOCO', 'INDUSINDBK',
            'TECHM', 'BAJAJ-AUTO', 'ADANIGREEN', 'UPL', 'SHREECEM'
        ];
        
        $directory = self::getDirectory();
        $results = [];
        foreach ($nifty50_keys as $key) {
            if (isset($directory[$key])) {
                $results[] = $directory[$key];
            }
        }
        return $results;
    }

    /**
     * Returns a list of stocks in the BSE Sensex index.
     * Note: This is a representative list for this application.
     */
    public static function getSensex(): array {
        $sensex_keys = [
            'RELIANCE', 'TCS', 'HDFCBANK', 'ICICIBANK', 'INFY',
            'HINDUNILVR', 'ITC', 'SBIN', 'BHARTIARTL', 'KOTAKBANK',
            'LT', 'AXISBANK', 'BAJFINANCE', 'ASIANPAINT', 'MARUTI',
            'SUNPHARMA', 'TITAN', 'HCLTECH', 'TATAMOTORS', 'NTPC',
            'TATASTEEL', 'POWERGRID', 'M&M', 'ULTRACEMCO', 'NESTLEIND',
            'INDUSINDBK', 'TECHM', 'BAJAJ-AUTO', 'BAJAJFINSV', 'WIPRO'
        ];
        
        $directory = self::getDirectory();
        $results = [];
        foreach ($sensex_keys as $key) {
            if (isset($directory[$key])) {
                $results[] = $directory[$key];
            }
        }
        return $results;
    }

    /**
     * Resolve a user input to the correct NSE ticker symbol.
     * Returns the best-matching symbol, or null if nothing found.
     */
    public static function resolve(string $query): ?string {
        $query_upper = strtoupper(trim($query));

        // If already has a dot (e.g. TCS.NS, TCS.BO) — return as-is
        if (strpos($query_upper, '.') !== false) {
            return $query_upper;
        }

        $directory = self::getDirectory();

        // 1. Exact key match
        if (isset($directory[$query_upper])) {
            return $directory[$query_upper]['symbol'];
        }

        // 2. Search for best match
        $matches = self::search($query_upper, 1);
        if (!empty($matches)) {
            return $matches[0]['symbol'];
        }

        // 3. Fallback — append .BSE and hope for the best
        return $query_upper . '.BSE';
    }
}
