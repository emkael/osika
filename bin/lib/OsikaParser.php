<?php

/**
 * Parser class. Converts hand input string to array of sorted and normalized suits.
 **/
class OsikaParser {

   private $_hand;

   /**
    * Constructor for the parser class
    * @param $hand (optional) hand to parse
    **/
   public function __construct($hand = NULL) {
      $this->setHand($hand);
   }

   /**
    * Sets the hand string to parse
    * @param $hand string of xxxx|xxx|xxx|xxx format
    **/
   public function setHand($hand) {
      $this->_hand = $hand;
   }

   /**
    * Card comparison function
    * @param $cardA, $cardB - characters denoting cards
    * @return 1 or -1, as usort() expects
    **/
   private function _sort($cardA, $cardB) {
      // aces first
      if ($cardA == 'a') {
         return -1;
      }
      // ...than kings...
      if ($cardA == 'k') {
         return ($cardB == 'a') ? 1 : -1;
      }
      // ...queens...
      if ($cardA == 'q') {
         return (in_array($cardB, array('a', 'k'))) ? 1 : -1;
      }
      // ...jacks...
      if ($cardA == 'j') {
         return (in_array($cardB, array('a', 'k', 'q'))) ? 1 : -1;
      }
      // ...tens...
      if ($cardA == 't') {
         return (in_array($cardB, array('a', 'k', 'q', 'j'))) ? 1 : -1;
      }
      // ... and nines
      if ($cardA == '9') {
         return (in_array($cardB, array('a', 'k', 'q', 'j', 't'))) ? 1 : -1;
      }
      // anything else goes last, as it was
      return 1;
   }

   /**
    * Suit sorting function. Uses OsikaParser::_sort as user-defined sort function for the exploded string.
    **/
   private function _sortSuit(&$suit) {
      $temp = str_split($suit);
      usort($temp, array($this, '_sort'));
      $suit = implode('', $temp);
   }

   /**
    * Where the magic happens.
    **/
   public function parse() {
      if (!$this->_hand) {
         throw new OsikaParserException('Brak podanej ręki', OsikaParserException::NO_HAND);
      }
      // input is case-insensitive
      $this->_hand = strtolower($this->_hand);
      // allow (and interpret) Polish figures abbrevs. and "10" as Ten
      $this->_hand = strtr($this->_hand,
                           array(
                                 '10' => 't',
                                 'w' => 'j',
                                 'd' => 'q'));
      // strip whitespace
      $this->_hand = preg_replace('/\s/', '', $this->_hand);
      $suits = explode('|', $this->_hand);
      // check for invalid number of suits suits in the hand
      if (count($suits) !== 4) {
         throw new OsikaParserException('Ręka nie zawiera 4 kolorów', OsikaParserException::INVALID_SUIT_COUNT);
      }
      $cardCount = 0;
      foreach ($suits as &$suit) {
         // check for invalid characters
         if (preg_match('/[^akqjtx2-9]/', $suit)) {
            throw new OsikaParserException('Kolor '.$suit.' zawiera nieprawidłowe znaki', OsikaParserException::INVALID_CHARS);
         }
         // check for duplicate cards
         foreach (array('a', 'k', 'q', 'j', 't', '9') as $honor) {
            if (substr_count($suit, $honor) > 1) {
               throw new OsikaParserException('Kolor '.$suit.' zawiera zduplikowany honor (lub 9)', OsikaParserException::DUPLICATE_CHARS);
            }
         }
         $this->_sortSuit($suit);
         $cardCount += strlen($suit);
      }
      unset($suit);
      // check for wrong card count
      if ($cardCount !== 13) {
         throw new OsikaParserException('Ręka nie zawiera 13 kart', OsikaParserException::INVALID_CARD_COUNT);
      }
      return $suits;
   }

}

/**
 * Exception class for parser errors
 **/
class OsikaParserException extends Exception {

   const NO_HAND = 1; // empty (or equivalent) string provided
   const INVALID_SUIT_COUNT = 2; // the hand does not contain 4 suit (i.e. 3 "|" chars)
   const INVALID_CHARS = 3; // the hand contains characters that make no sense
   const DUPLICATE_CHARS = 4; // the hand contains duplicate honors (or 9)
   const INVALID_CARD_COUNT = 5; // the hand does not contain 13 cards

   /*
     Should we check if the hand contains exactly 13 cards?
     I don't think the algorithm technically relies on the hand being complete.
     But does the evaluation stand for incomplete hands?
     E.g. if we played the first 7 tricks and are left with AK AK AK ==,
     do all the quick tricks, short honor and grouped honor evaluations compute correctly?
     Or even make sense?
   */

};

?>
