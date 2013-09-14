<?php

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
