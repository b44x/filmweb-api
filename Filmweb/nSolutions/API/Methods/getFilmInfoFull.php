<?php
/**
* @author Michell Hoduń
* @copyright (c) 2013 nSolutions.pl
* @description Filmweb.pl API
* @version 1.0b
* @link https://github.com/nSolutionsPL/filmweb-api
* @license http://creativecommons.org/licenses/by/3.0/ Creative Commons 3.0
*/
namespace nSolutions\API\Methods;
final class getFilmInfoFull extends \nSolutions\API\Methods
{
    // Nazwa metody
    public $method = 'getFilmInfoFull';
    
   /**
    * Wymagane parametry
    * @var array 
    */
    protected $_args =
    [
        'video_id'
    ];
    
   /**
    * Dane zwrócone przez filmweba
    */
    protected $_response_keys =
    [
        0 => 'title',
        1 => 'originalTitle',
        2 => 'avgRate',
        3 => 'votesCount',
        4 => 'genres',
        5 => 'year',
        6 => 'duration',
        7 => 'commentsCount',
        8 => 'forumUrl',
        9 => 'hasReview', 
        10 => 'hasDescription',
        11 => 'imagePath',
        12 => 'video',
        13 => 'premiereWorld',
        14 => 'premiereCountry',
        15 => 'filmType',
        16 => 'seasonsCount',
        17 => 'episodesCount',
        18 => 'countriesString',
        19 => 'desciption'
    ];
    
   /**
    * Callbacki
    */
    protected $_functions =
    [
        'cats' => ['explode', ',']
    ];
    
    protected function prepare()
    {
        $this->methods = [
            $this->method => $this->video_id
        ];        
    }
    
   /**
    * Dodatkowe obrobienie danych.
    * @param string $response
    * @return object
    */
    protected function parse($response)
    {
        $response = parent::parse($response);
        
        // Sprawdzenie czy są trailery - jeśli tak to przypisz jakości.
        if(isset($response->video) AND ! is_null($response->video))
        {
            $data = [
                'videoImageUrl' => $response->video[0]
            ];
            
            if(isset($response->video[1]))
                $data['videoUrl'] = $response->video[1];
            
            if(isset($response->video[2]))
                $data['videoHDUrl'] = $response->video[2];
            
            if(isset($response->video[3]))
                $data['video480pUrl'] = $response->video[3];
            
            $response->video = $data;
        }
        
        if(isset($response->imagePath) AND ! is_null($response->imagePath))
        {
            // Dostanie adresu obrazków + zwrócenie największego
            $response->imagePath = \nSolutions\Filmweb::$_config['filmImageUrl'] . strtr($response->imagePath, ['.2.jpg' => '.3.jpg']);
        }
        
        return $response;
    }
}