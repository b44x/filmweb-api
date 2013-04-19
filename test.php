<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
require_once 'Filmweb.php';

$fimweb = \nSolutions\Filmweb::instance();
var_dump($fimweb->getFilmInfoFull(194037));
