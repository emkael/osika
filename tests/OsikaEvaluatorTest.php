<?php

/**
 * DISCLAIMER!
 *
 * Do NOT. Under ANY circumstances. Use this test set as an evaluation
 * for OSiKa algorithm correctness.
 *
 * It's SOLE purpose was to ensure correct transition from the previous
 * codebase and it ONLY checks if all the calculation and output collection
 * was handled the same way as with the previous script.
 *
 * This test set does NOT ensure that the trick values coming out of the algorithm
 * are in any way correct, in terms of bridge theory.
 **/

class OsikaEvaluatorTest extends PHPUnit_Framework_TestCase {

  private $_evaluator;

  public function setUp() {
    require_once('../bin/lib/OsikaEvaluator.php');
    $this->_evaluator = new OsikaEvaluator();
  }

  public function handProvider() {
    $file = file('OsikaEvaluatorTest.tests');
    $ret = [];
    $switch = false;
    $item;
    foreach ($file as $line) {
      if (trim($line)) {
	if ($switch) {
	  $item[] = json_decode($line, TRUE);
	  $ret[] = $item;
	}
	else {
	  $item = [trim($line)];
	}
	$switch = !$switch;
      }
    }
    return $ret;
  }

  /**
   * @dataProvider handProvider
   **/
  public function testEvaluation($hand, $result) {
    $this->_evaluator->setHand($hand);
    $this->assertEquals($result, $this->_evaluator->evaluate());
  }

}

?>
