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
final class getUserFilmVotes extends \nSolutions\API\Methods
{
    // Nazwa metody
    public $method = 'getUserFilmVotes';

   /**
    * Wymagane parametry
    * @var array 
    */
    protected $_args =
    [
        'userId',
        'pageNo'
    ];
    
   /**
    * Dane zwrócone przez filmweba
    */
    protected $_response_keys =
    [
        'filmId',
        'date',
        'vote'
    ];
    
    protected function prepare()
    {
        $this->methods = [
            $this->method => $this->userId . ','  . (100 * $this->pageNo) . ',' . 100 * ($this->pageNo + 1)
        ];        
    }
    
   /**
    * Sparsowanie odpowiedzi z API filmweba.
    * @param object $response
    * @return \stdClass
    */
    protected function parse($response)
    {
        $response = explode("\n", $response);

        // Nie ma żadnych danych.
        if($response[1] == 'exc NullPointerException')
            return FALSE;
        
        $response = json_decode(preg_replace('/ s/i', '', $response[1]));
        if(isset($response[0]))
            unset($response[0]);

        return $this->getData($response);
    }

    protected function getData($response)
    {
        $data = [];
        $key = $this->_response_keys[0];        
        $i = 0;

        foreach($response as $item)
        {
            $i = new \stdClass;
            
            foreach($this->_response_keys as $k => $v)
            {
                $i->$v = $item[$k];
            }
            
            $data[] = $i;
        }
        
        return (object) $data;
    }
}