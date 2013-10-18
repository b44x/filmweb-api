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
    $filmweb = \nSolutions\Filmweb::instance();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"></meta>
  </head>
  <body>
<h2>Pełne informacje o filmie</h2>
<?php
    // Przykładowe zachowanie sesji po zalogowaniu.
    \nSolutions\Request::$default_options += array(
        CURLOPT_COOKIEJAR => dirname(__FILE__).DIRECTORY_SEPARATOR.'cookies.txt',
        CURLOPT_COOKIEFILE => dirname(__FILE__).DIRECTORY_SEPARATOR.'cookies.txt'
    );

    // Logujemy się na konto
    $filmweb->Login('login', 'haslo')
        ->execute();
?>

<pre><?php var_dump($filmweb->getFilmInfoFull(491118)->execute()); ?></pre>
<pre><?php var_dump($filmweb->getUserFilmVotes(2436894, 0)->execute()); ?></pre>

<h2>Obsada filmu</h2>
<?php foreach(nSolutions\Filmweb::$roles as $type => $role):?>
<h3><?php echo $role; ?></h3>
<?php $casts = $filmweb->getFilmPersons(491118, $type, 0)->execute(); ?>
<?php if($casts):?>
    <?php foreach($casts as $cast):?>
        <div style="margin:10px 0px;">
            <div style="float:left;width:150px;height:200px;">
        <?php if($cast->personPhoto):?>
            <img src="<?php echo $cast->personPhoto; ?>" alt="" />
        <?php endif; ?>
            </div>
            <div style="float:left;padding-top:100px">(ID: #<?php echo $cast->personId; ?>) - <?php echo $cast->personName;?> <i><?php echo $cast->assocName;?> <?php echo $cast->assocAttributes; ?></i></div>
        </div>
        <div style="clear:left;"></div>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p>brak</p>
<?php endif; ?>
<?php endforeach; ?>
</body>
</html>