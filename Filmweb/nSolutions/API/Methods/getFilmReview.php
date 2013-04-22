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
final class getFilmReview extends \nSolutions\API\Methods
{
    // Nazwa metody
    public $method = 'getFilmReview';
    
   /**
    * Wymagane parametry
    * @var array 
    */
    protected $_args =
    [
        'filmId'
    ];
    
   /**
    * Dane zwrócone przez filmweba
    */
    protected $_response_keys =
    [
        'authorName',
        'authorUserId',
        'authorImagePath',
        'review',
        'title'
    ];
    
    protected function prepare()
    {
        $this->methods = [
            $this->method => $this->filmId
        ];        
    }
}