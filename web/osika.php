<?php

function suitSort($cardA, $cardB) {
    if ($cardA == 'a') {
        return -1;
    }
    if ($cardA == 'k') {
        return ($cardB == 'a') ? 1 : -1;
    }
    if ($cardA == 'q') {
        return (in_array($cardB, array('a', 'k'))) ? 1 : -1;
    }
    if ($cardA == 'j') {
        return (in_array($cardB, array('a', 'k', 'q'))) ? 1 : -1;
    }
    if ($cardA == 't') {
        return (in_array($cardB, array('a', 'k', 'q', 'j'))) ? 1 : -1;
    }
    if ($cardA == '9') {
        return (in_array($cardB, array('a', 'k', 'q', 'j', 't'))) ? 1 : -1;
    }
    return 1;
}

function sortHand(&$suit) {
    $temp = str_split($suit);
    usort($temp, 'suitSort');
    $suit = implode('', $temp);
}

function honor($suit) {
    $jack = 0;
    if (strpos($suit, 'j') === 0) {
        $jack = 0.125;
    }
    return substr_count($suit, 'a')*1.125+substr_count($suit, 'k')*0.8125+substr_count($suit, 'q')*0.4375+$jack;
}

function honorPlus($suit) {
    $dummy = array();
    $count = preg_match_all('/a|k|q|j/', $suit, $dummy);
    if ($count < 2) {
        return 0;
    }
    return $count*0.25;
}

function honorShort($suit) {
    $len = strlen($suit);
    if (!$len || $len > 2) {
        return 0;
    }
    $count = preg_match_all('/a|k|q|j/', $suit, $dummy);
    if ($len == 1) {
        return ($count)*(-0.125);
    }
    return $count*(-0.0625);
}

function blot($length) {
    if ($length < 4) {
        return 0;
    }
    switch ($length) {
        case 4:
            return 0.4375;
        case 5:
            return 1.5;
        case 6:
            return 2.75;
        case 7:
            return 3.9375;
    }
    if ($length >= 8) {
        return $length - 3;
    }
}

function blotPlus($suit) {
    $dummy = array();
    return 0.125*((strlen($suit) > 3) && substr_count($suit, 't') && preg_match_all('/a|k|q/', $suit, $dummy))+(substr_count($suit, 't9'))*0.0625;
}

function quickTricks($hand) {
    $hand = '|'.$hand;
    $high = substr_count($hand, 'a')+substr_count($hand, 'k');
    $low = substr_count($hand, 'q')+substr_count($hand, 'j')-substr_count($hand, '|j');
    $diff = $high - $low;
    if ($diff > 2) {
        return 0.125;
    }
    if ($diff > 1) {
        return 0.0625;
    }
    if ($diff < -2) {
        return -0.125;
    }
    if ($diff < -1) {
        return -0.0625;
    }
    return 0;
}

function luBlots($hand) {
    $nonshort = '';
    foreach ($hand as $suit) {
        if (strlen($suit) >= 3) {
            $nonshort .= $suit;
        }
    }
    $dummy = array();
    $count = preg_match_all('/t|9/', $nonshort, $dummy)-1;
    if ($count > 0) {
        return 0.0625;
    }
    if ($count < 0) {
        return -0.0625;
    }
    return 0;
}

function shortSuit($distribution) {
    $suits = array();
    foreach ($distribution as $suit) {
        if ($suit <= 3) {
            $suits[] = $suit;
        }
    }
    if (count($suits) < 2) {
        return 0;
    }
    sort($suits);
    $diff = $suits[1] - $suits[0];
    return ($diff > 1) ? 0.0625 : 0;
}

function majorSuit($distribution) {
    if ((($distribution[0] + $distribution[1]) >= 8) && ($distribution[0] > 2) && ($distribution[1] > 2)) {
        return 0.0625;
    }
    return 0;
}

function localization($distribution, $result) {
    $strength = array();
    $length = array();
    foreach ($result['lh'] as $index => $value) {
        if (is_numeric($index)) {
            if ($distribution[$index] >= 3) {
                if (isset($strength[$distribution[$index]])) {
                    $strength[$distribution[$index]] += ($result['lh'][$index]+$result['lh_plus'][$index]+$result['lh_10'][$index]+$result['lh_short'][$index]);
                }
                else {
                    $strength[$distribution[$index]] = ($result['lh'][$index]+$result['lh_plus'][$index]+$result['lh_10'][$index]+$result['lh_short'][$index]);
                }
                if (isset($length[$distribution[$index]])) {
                    $length[$distribution[$index]] += $distribution[$index];
                }
                else {
                    $length[$distribution[$index]] = $distribution[$index];
                }
            }
        }
    }
    ksort($strength);
    ksort($length);
    $sumLength = array_sum($length);
    $sumStrength = array_sum($strength);
    $longestDiff = end($strength)-$sumStrength*end($length)/$sumLength;
    $shortestDiff = reset($strength)-$sumStrength*reset($length)/$sumLength;
    if (abs($longestDiff) > 0.5) {
        if (abs($longestDiff) > 1) {
            return 0.25*(abs($longestDiff)/$longestDiff);
        }
        else {
            return 0.125*(abs($longestDiff)/$longestDiff);
        }
    }
    if (abs($shortestDiff) > 0.5) {
        if (abs($shortestDiff) > 1) {
            return -0.125*(abs($shortestDiff)/$shortestDiff);
        }
        else {
            return -0.0625*(abs($shortestDiff)/$shortestDiff);
        }
    }
    return 0;
}


function osika($hand) {
    if (!$hand) {
        return array('error' => 'Brak podanej ręki!');
    }
    $hand = strtolower($hand);
    $hand = str_replace(array('10', 'w', 'd'), array('t', 'j', 'q'), $hand);
    $hand = preg_replace('/\s/', '', $hand);
    $suits = explode('|', $hand);
    if (count($suits) != 4) {
        return array('error' => 'Ręka nie zawiera 4 kolorów!');
    }
    $distribution = array();
    $result = array('lu' => array('total' => 0),
                    'lu_plus' => array('total' => 0),
                    'lh' => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 'total' => 0),
                    'lh_plus' => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 'total' => 0),
                    'lh_10' => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 'total' => 0),
                    'lh_short' => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 'total' => 0),
                    'lsz' => array('total' => 0),
                    'l10n' => array('total' => 0),
                    'short_suit' => array('total' => 0),
                    'major_suit' => array('total' => 0));
    foreach ($suits as $index => $suit) {
        $distribution[] = strlen($suit);
        if (preg_match('/[^akqjtx2-9]/', $suit)) {
            return array('error' => 'Kolor '.$suit.' zawiera nieprawidłowe znaki!');
        }
        foreach (array('a', 'k', 'q', 'j', 't', '9') as $honor) {
            if (substr_count($suit, $honor) > 1) {
                return array('error' => 'Kolor '.$suit.' zawiera zduplikowany honor!');
            }
        }
        sortHand($suit);
        $result['lh_short'][$index] = honorShort($suit);
        $result['lh_short']['total'] += $result['lh_short'][$index];
        $result['lh_10'][$index] = blotPlus($suit);
        $result['lh_10']['total'] += $result['lh_10'][$index];
        $suit = substr($suit, 0, 3);
        $result['lh'][$index] = honor($suit);
        $result['lh']['total'] += $result['lh'][$index];
        $result['lh_plus'][$index] = honorPlus($suit);
        $result['lh_plus']['total'] += $result['lh_plus'][$index];
    }
    if (array_sum($distribution) != 13) {
        return array('error' => 'Ręka nie zawiera 13 kart!');
    }
    foreach ($distribution as $index => $suit) {
        $result['lu'][$index] = blot($suit);
        $result['lu']['total'] += $result['lu'][$index];
    }
    $result['lsz']['total'] = quickTricks(implode('|',$suits));
    $result['lu_plus']['total'] = luBlots($suits);
    $result['short_suit']['total'] = shortSuit($distribution);
    $result['major_suit']['total'] = majorSuit($distribution);
    $result['l10n']['total'] = localization($distribution, $result);
    $subtotal = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 'total' => 0);
    $total = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 'total' => 0);
    foreach ($result as $i => $factor) {
        if (count($factor) == 5) {
            foreach ($factor as $index => $subt) {
                $subtotal[$index] += $subt;
            }
        }
        $total['total'] += $factor['total'];
    }
    $result['subtotal'] = $subtotal;
    $result['total'] = $total;
    return $result;
}

$hand = $_REQUEST['h'];
$res = osika($hand);
if (!isset($_REQUEST['f'])) {
    $_REQUEST['f'] = '';
}
switch ($_REQUEST['f']) {
    case 'json':
        echo json_encode($res);
        exit;
    default:
        if (isset($res['error'])) {
            echo 'Błąd: '.$res['error']."\n";
            exit;
        }
        else {
            print_r($res);
        }
}



?>