<?php

// path to Evaluator goes here.
require_once('../bin/lib/OsikaEvaluator.php');

$hand = $_REQUEST['h'];
try {
  $eval = new OsikaEvaluator($hand);
  $res = $eval->evaluate();
}
catch (OsikaParserException $e) {
  $res = array('error' => $e->getMessage());
}

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