<?php
// Lightweight ai_query parser: extracts price numbers, style, htype and city tokens
function parse_ai_query($query) {
    $q = trim(mb_strtolower($query));
    $out = ['ai_query' => $query, 'style' => null, 'minRent' => null, 'maxRent' => null, 'city' => null, 'htype' => null];

    if ($q === '') return $out;

    // style keywords
    if (preg_match('/\b(vintage|retro|old|classic)\b/', $q, $m)) {
        $out['style'] = 'vintage';
    } elseif (preg_match('/\b(modern|new|contemporary|sleek)\b/', $q, $m)) {
        $out['style'] = 'modern';
    }

    // price patterns: "under 15000", "below 15000", "< 15000", "15000-30000", "from 5000 to 15000"
    if (preg_match('/(?:under|below|<)\s*(\d{3,7})/', $q, $m)) {
        $out['maxRent'] = (int)$m[1];
    }
    if (preg_match('/(?:over|above|>)\s*(\d{3,7})/', $q, $m)) {
        $out['minRent'] = (int)$m[1];
    }
    if (preg_match('/(\d{3,7})\s*[-–]\s*(\d{3,7})/', $q, $m)) {
        $out['minRent'] = (int)$m[1];
        $out['maxRent'] = (int)$m[2];
    }
    if (preg_match('/from\s*(\d{3,7})\s*(?:to|-)\s*(\d{3,7})/', $q, $m)) {
        $out['minRent'] = (int)$m[1];
        $out['maxRent'] = (int)$m[2];
    }

    // htype keywords
    if (preg_match('/\b(studio|bedsitter|bedsit|one bedroom|1 bedroom|two bedroom|2 bedroom|three bedroom|3 bedroom)\b/', $q, $m)) {
        $map = [
            'studio' => 'STUDIO', 'bedsitter' => 'BEDSITTER', 'bedsit' => 'BEDSITTER',
            'one bedroom' => 'ONE_BEDROOM','1 bedroom' => 'ONE_BEDROOM',
            'two bedroom' => 'TWO_BEDROOM','2 bedroom' => 'TWO_BEDROOM',
            'three bedroom' => 'THREE_BEDROOM','3 bedroom' => 'THREE_BEDROOM'
        ];
        $key = $m[1];
        if (isset($map[$key])) $out['htype'] = $map[$key];
    }

    // basic city recognition - match against a small built-in list (expandable)
    $cities = ['nairobi','mombasa','kisumu','nakuru','eldoret','thika','meru','kitale','garissa'];
    foreach ($cities as $c) {
        if (mb_strpos($q, $c) !== false) {
            $out['city'] = ucfirst($c);
            break;
        }
    }

    return $out;
}
