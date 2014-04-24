<?php
/**
* @author Michell Hoduń
* @copyright (c) 2013 nSolutions.pl
* @description Filmweb.pl API
* @version 1.0b
* @link https://github.com/nSolutionsPL/filmweb-api
* @license http://creativecommons.org/licenses/by/3.0/ Creative Commons 3.0
*/
namespace nSolutions;
class Filmweb
{
    protected static $_instance;
    
    const KEY = 'qjcGhW2JnvGT9dfCt3uT_jozR3s';
    
    // Ustawienia adresów
    public static $_config =
    [
        'channelImageUrl' => 'http://1.fwcdn.pl/channels',
        'filmImageUrl' => 'http://1.fwcdn.pl/po',
        'filmPhotoUrl' => 'http://1.fwcdn.pl/ph',
        'personImageUrl' => 'http://1.fwcdn.pl/p',
        'userImageUrl' => 'http://1.fwcdn.pl/u',
        'captchaImageUrl' => 'https://ssl.filmweb.pl/captcha/',
    ];
    
    // Przypisanie gatunków po ID
    public static $genres =
    [
        2 => "animacja",
        3 => "biograficzny",
        4 => "dla dzieci",
        5 => "dokumentalny",
        6 => "dramat",
        7 => "erotyczny",
        8 => "familijny",
        9 => "fantasy",
        10 => "surrealistyczny",
        11 => "historyczny",
        12 => "horror",
        13 => "komedia",
        14 => "kostiumowy",
        15 => "kryminał",
        16 => "melodramat",
        17 => "musical",
        18 => "nowele filmowe",
        19 => "obyczajowy",
        20 => "przygodowy",
        22 => "sensacyjny",
        24 => "thriller",
        25 => "western",
        26 => "wojenny",
        27 => "film-noir",
        28 => "akcja",
        29 => "komedia obycz.",
        30 => "komedia rom.",
        32 => "romans",
        33 => "sci-fi",
        37 => "dramat obyczajowy",
        38 => "psychologiczny",
        39 => "satyra",
        40 => "katastroficzny",
        41 => "dla młodzieży",
        42 => "baśń",
        43 => "polityczny",
        44 => "muzyczny",
        45 => "etiuda",
        46 => "dreszczowiec",
        47 => "czarna komedia",
        50 => "krótkometrażowy",
        51 => "religijny",
        52 => "prawniczy",
        53 => "gangsterski",
        54 => "karate",
        55 => "biblijny",
        57 => "dokumentalizowany",
        58 => "komedia kryminalna",
        59 => "dramat historyczny",
        60 => "groteska filmowa",
        61 => "sportowy",
        62 => "poetycki",
        63 => "szpiegowski",
        64 => "edukacyjny",
        65 => "dramat sądowy",
        66 => "anime",
        67 => "niemy",
        68 => "płaszcza i szpady",
        69 => "dramat społeczny",
        70 => "fabularyzowany dok.",
        71 => "xxx",
        72 => "sztuki walki",
        73 => "przyrodniczy",
        74 => "komedia dokumentalna",
        75 => "fikcja literacka",
        76 => "propagandowy"
    ];
    
    public static $roles =
    [
        1 => 'scenarzysta',
        2 => 'reżyser',
        3 => 'zdjęcia',
        4 => 'muzyka',
        5 => 'scenografia',
        6 => 'aktor',
        7 => 'producent',
        10 => 'montaż',
        13 => 'kostiumy',
        17 => 'materiały do scenariusza',
        18 => 'dźwięk',
        19 => 'materiały archiwalne',
        20 => 'głos',
        21 => 'we własnej osobie'
        
    ];
    
    const API_SERVER = 'https://ssl.filmweb.pl/api?';
    protected $request;
    
    
    public static function instance($name = 'default')
    {
        if(! isset(\nSolutions\Filmweb::$_instance[$name]))
        {
            \nSolutions\Filmweb::$_instance[$name] = new \nSolutions\Filmweb;
        }
        
        return \nSolutions\Filmweb::$_instance[$name];
    }
    
    public function __construct()
    {
        // Automatyczne ładowanie wymaganych klas
        spl_autoload_register('\nSolutions\Filmweb::loader');
    }
    
  /**
   * Wykonanie requestu do API Filmweba.
   * @param string $name
   * @param array $arguments
   */
    public function __call($method, $arguments)
    {
        $class = '\nSolutions\\API\\Methods\\'.$method;
        return new $class($arguments);
    }

   /**
    * Automatyczne ładowanie klas
    * @param string $class
    * @throws \Exception
    */
    public static function loader($class)
    {
        $class = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Filmweb' . DIRECTORY_SEPARATOR . strtr($class, ['\\' => '/']) . '.php';
        if(file_exists($class))
            include $class;
        else
            throw new \Exception('Could not find class: Filmweb_'.basename($class));
    }
}