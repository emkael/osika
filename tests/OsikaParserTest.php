<?php

class OsikaParserTest extends PHPUnit_Framework_TestCase {

  private $_parser;

  public function setUp() {
    require_once('../bin/lib/OsikaParser.php');
    $this->_parser = new OsikaParser();
  }

  /**
   * @expectedException OsikaParserException
   * @expectedExceptionCode 1
   **/
  public function testEmpty() {
    $this->_parser->parse();
  }

  public function testLowerCase() {
    $this->_parser->setHand('xxx|xxxx|xxx|xxx');
    $this->assertEquals(['xxx','xxxx','xxx','xxx'], $this->_parser->parse());
    $this->_parser->setHand('XXX|XXXX|XXX|XXX');
    $this->assertEquals(['xxx','xxxx','xxx','xxx'], $this->_parser->parse());
    $this->_parser->setHand('XXX|xxxx|XXX|XXX');
    $this->assertEquals(['xxx','xxxx','xxx','xxx'], $this->_parser->parse());
    $this->_parser->setHand('XXX|XXXX|Xxx|XXX');
    $this->assertEquals(['xxx','xxxx','xxx','xxx'], $this->_parser->parse());
    $this->_parser->setHand('XXx|XXXx|xXX|XxX');
    $this->assertEquals(['xxx','xxxx','xxx','xxx'], $this->_parser->parse());
  }

  public function testHonorSubstitution() {
    $this->_parser = new OsikaParser();
    $this->_parser->setHand('AKQJT|xxx|xxx|xx');
    $this->assertEquals(['akqjt','xxx','xxx','xx'], $this->_parser->parse());
    $this->_parser->setHand('AKDW10|xxx|xxx|xx');
    $this->assertEquals(['akqjt','xxx','xxx','xx'], $this->_parser->parse());
    $this->_parser->setHand('AKdJT|xxx|xxx|xx');
    $this->assertEquals(['akqjt','xxx','xxx','xx'], $this->_parser->parse());
    $this->_parser->setHand('AKQwT|xxx|xxx|xx');
    $this->assertEquals(['akqjt','xxx','xxx','xx'], $this->_parser->parse());
  }

  public function testSpacesStrip() {
    $this->_parser->setHand("\x09\x0A\x0C\x0D\x20xxx|xx\x09\x0A\x0C\x0D\x20xx|x\x09\x0A\x0C\x0D\x20xx|xxx\x09\x0A\x0C\x0D\x20\x09\x0A\x0C\x0D\x20");
    $this->assertEquals(['xxx','xxxx','xxx','xxx'], $this->_parser->parse());
  }

  /**
   * @expectedException OsikaParserException
   * @expectedExceptionCode 2
   **/
  public function testNotEnoughSuits() {
    $this->_parser->setHand('xxxxxxx|xxxxxx|');
    $this->_parser->parse();
  }

  /**
   * @expectedException OsikaParserException
   * @expectedExceptionCode 2
   **/
  public function testTooManySuits() {
    $this->_parser->setHand('xxx|xxxx|xxx|xxx|');
    $this->_parser->parse();
  }

  /**
   * @expectedException OsikaParserException
   * @expectedExceptionCode 3
   **/
  public function testInvalidCharacters() {
    $this->_parser->setHand('a||a|akdjz98xxx0');
    $this->_parser->parse();
  }

  /**
   * @expectedException OsikaParserException
   * @expectedExceptionCode 4
   **/
  public function testDuplicateCard() {
    $this->_parser->setHand('akdw|akdw|akd|t10');
    $this->_parser->parse();
  }

  public function testCardSort() {
    $this->_parser->setHand('akdj|akw|a|a9xxx');
    $this->assertEquals(['akqj','akj','a','a9xxx'], $this->_parser->parse());
    $this->_parser->setHand('kajd|twa|a|9axxx');
    $this->assertEquals(['akqj','ajt','a','a9xxx'], $this->_parser->parse());
    $this->_parser->setHand('akdj|akw|a|ax89x');
    $this->assertEquals(['akqj','akj','a','a9x8x'], $this->_parser->parse());
  }

  /**
   * @expectedException OsikaParserException
   * @expectedExceptionCode 5
   **/
  public function testInvalidCardCount() {
    $this->_parser->setHand('xxx|xxx|xxx|xxx');
    $this->_parser->parse();
  }

}

?>