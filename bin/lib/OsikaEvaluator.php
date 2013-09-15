<?php

require_once('OsikaParser.php');

class OsikaEvaluator {

  private $_hand;

  public function __construct($hand = NULL) {
    if ($hand) {
      $this->setHand($hand);
    }
  }

  public function setHand($hand) {
    $parser = new OsikaParser($hand);
    $this->_hand = $parser->parse();
  }

  private static $_honorCounts = array();
  private function _countHonors($suit, $major = FALSE) {
    if (!isset(self::$_honorCounts[$suit])) {
      self::$_honorCounts[$suit] = array(NULL, NULL);
    }
    if (self::$_honorCounts[$suit][$major] === NULL) {
      self::$_honorCounts[$suit][$major] = preg_match_all($major ? '/a|k|q/' : '/a|k|q|j/', $suit);
    }
    return self::$_honorCounts[$suit][$major];
  }

  private static $_cardHonorTricks = array(
				    'a' => 1.125,
				    'k' => 0.8125,
				    'q' => 0.4375,
				    'j' => 0.125
				    );
  private function _honorTricks($suit) {
    // only 3 highest cards in the suit holding count towards honor tricks
    $suit = substr($suit, 0, 3);

    $cards = self::$_cardHonorTricks;
    // only count Jack if it's the highest card in suit
    if (strpos($suit, 'j') !== 0) {
      $cards = array_slice($cards, 0, 3);
    }

    $ret = 0;
    foreach ($cards as $card => $value) {
      $ret += substr_count($suit, $card) * $value;
    }
    return $ret;
  }

  private function _honorTrickCorrections($suit) {
    // only 3 highest cards in the suit holding count towards honor tricks
    $suit = substr($suit, 0, 3);
    $count = $this->_countHonors($suit);
    // HH = 1/4 trick; HHH = 1/2 trick
    return ($count < 2) ? 0 : $count * 0.25;
  }

  private function _honorTrickSupportCorrections($suit) {
    $ret = 0;
    // every 10 within 4+ suit with any of the AKQ = 1/8 of a trick
    if (strlen($suit) > 3 && strpos($suit, 't') !== FALSE && $this->_countHonors($suit, TRUE)) {
      $ret += 0.125;
    }
    // every 109 = 1/16 of a trick
    if (strpos($suit, 't9') !== FALSE) {
      $ret += 0.0625;
    }
    return $ret;
  }

  private function _honorTrickShortCorrections($suit) {
    $length = strlen($suit);
    // either nothing to subtract from or no need to
    if (!$length || $length > 2) {
      return 0;
    }
    $count = $this->_countHonors($suit);
    // H sec = -1/8 of a trick; Hx = -1/16 of a trick; HH sec = -1/8 of a trick
    return $count * (($length === 1) ? -0.125 : -0.0625);
  }

  private static $_lengthDistributionTricks = array(
						    4 => 0.4375,
						    5 => 1.5,
						    6 => 2.75,
						    7 => 3.9375
						    );
  private function _distributionTricks($suit) {
    $length = strlen($suit);
    if ($length < 4) {
      return 0;
    }
    if ($length >= 8) {
      return $length - 3;
    }
    return self::$_lengthDistributionTricks[$length];
  }

  private function _quickTricks($hand) {
    $hand = '|'.implode('|', $hand);
    // aces and kings contribute towards quick tricks
    $highCards = substr_count($hand, 'a') + substr_count($hand, 'k');
    // queens and jacks contribute against, but we don't count unsupported jacks, because of how they count towards honor tricks in the first place
    $lowCards = substr_count($hand, 'q') + substr_count($hand, 'j') - substr_count($hand, '|j');
    $difference = $highCards - $lowCards;
    // difference of:
    // +3 or more = 1/8
    // +2 = 1/16
    // +1 to -1 = 0
    // -2 = -1/16
    // -3 or less = -1/8
    if (abs($difference) <= 1) {
      return 0;
    }
    if ($difference > 2) {
      return 0.125;
    }
    if ($difference > 1) {
      return 0.0625;
    }
    if ($difference < -2) {
      return -0.125;
    }
    if ($difference < -1) {
      return -0.0625;
    }
  }

  private function _middleCardCorrections($hand) {
    // count only 10's and 9's in 3+ card suits
    $nonshort = '';
    foreach ($hand as $suit) {
      if (strlen($suit) >= 3) {
	$nonshort .= $suit;
      }
    }
    // statistically, we should have 2 of those
    // but we're not counting short suits and some long suit 10 configurations
    // so the par for the course is 1 middle card
    $count = preg_match_all('/t|9/', $nonshort) - 1;
    // if we're better than that single middle card -> 1/16 of a trick
    // if we're worse -> -1/16
    return max(-0.0625, min(0.0625, $count * 0.0625));
  }

  private function _shortSuitCorrections($distribution) {
    $shortSuits = array();
    foreach ($distribution as $suit) {
      // short suit is a 3- card suit here
      if ($suit <= 3) {
	$shortSuits[] = $suit;
      }
    }
    // the correction only applies if we're having 2 or more short suits
    if (count($shortSuits) < 2) {
      return 0;
    }
    sort($shortSuits);
    // if the shortest short suits are 3-0, 3-1 or 2-0, we add 1/16 of a trick
    $diff = $shortSuits[1] - $shortSuits[0];
    return ($diff > 1) ? 0.0625 : 0;
  }

  private function _majorSuitCorrections($distribution) {
    // at least 8 cards in majors...
    if ($distribution['s'] + $distribution['h'] >= 8) {
      // ...and at least 3 cards in each major...
      if ($distribution['h'] > 2 && $distribution['h'] > 2) {
	// ...constitute a 1/16 of a trick correction
	return 0.0625;
      }
    }
    return 0;
  }

  // I honestly have no idea what the hell's going on below.
  private function _localizationCorrections($result, $distribution) {
    $strength = array();
    $length = array();
    foreach ($result['lh'] as $index => $value) {
      if (strlen($index) === 1) {
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

  private static $_suits = ['s','h','d','c'];
  public function evaluate() {
    $result = array();
    $result['lh'] = array();
    $result['lh_plus'] = array();
    $result['lh_10'] = array();
    $result['lh_short'] = array();
    $result['lu'] = array();
    foreach ($this->_hand as $ind => $suit) {
      $suitChar = self::$_suits[$ind];
      $result['lh'][$suitChar] = $this->_honorTricks($suit);
      $result['lh_plus'][$suitChar] = $this->_honorTrickCorrections($suit);
      $result['lh_10'][$suitChar] = $this->_honorTrickSupportCorrections($suit);
      $result['lh_short'][$suitChar] = $this->_honorTrickShortCorrections($suit);
      $result['lu'][$suitChar] = $this->_distributionTricks($suit);
    }
    $result['lh']['total'] = array_sum($result['lh']);
    $result['lh_plus']['total'] = array_sum($result['lh_plus']);
    $result['lh_10']['total'] = array_sum($result['lh_10']);
    $result['lh_short']['total'] = array_sum($result['lh_short']);
    $result['lu']['total'] = array_sum($result['lu']);

    $result['lsz'] = array('total' => $this->_quickTricks($this->_hand));
    $result['lu_plus'] = array('total' => $this->_middleCardCorrections($this->_hand));

    $distribution = array_combine(self::$_suits, array_map('strlen', $this->_hand));
    $result['short_suit'] = array('total' => $this->_shortSuitCorrections($distribution));
    $result['major_suit'] = array('total' => $this->_majorSuitCorrections($distribution));

    $result['l10n'] = array('total' => $this->_localizationCorrections($result, $distribution));

    $subtotal = array();
    $lhSubtotal = array();
    $total = 0;
    foreach ($result as $category => $factor) {
      if (count($factor) === 5) {
	foreach ($factor as $index => $subt) {
	  if (!isset($subtotal[$index])) {
	    $subtotal[$index] = 0;
	    $lhSubtotal[$index] = 0;
	  }
	  $subtotal[$index] += $subt;
	  if ($category !== 'lu') {
	    $lhSubtotal[$index] += $subt;
	  }
	}
      }
      $total += $factor['total'];
    }
    $result['lh_subtotal'] = $lhSubtotal;
    $result['subtotal'] = $subtotal;
    $result['total'] = array('total' => $total);

    return $result;
  }

}

?>
