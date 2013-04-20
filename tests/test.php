<?php
/**
* @author Michell Hoduń
* @copyright (c) 2013 nSolutions.pl
* @description Filmweb.pl API
* @version 1.0b
* @link https://github.com/nSolutionsPL/filmweb-api
* @license http://creativecommons.org/licenses/by/3.0/ Creative Commons 3.0
*/
    /* DEBUG */
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');

    // Załadowanie klasy Filmweb.
    require_once '../Filmweb.php';

    // Utworzenie nowej instancji.
    $fimweb = \nSolutions\Filmweb::instance();
?>

<h2>Pełne informacje o filmie</h2>
<pre><?php var_dump($fimweb->getFilmInfoFull(491118)->execute()); ?></pre>
<pre><?php var_dump($fimweb->getFilmInfoFull(1201)->execute()); ?></pre>