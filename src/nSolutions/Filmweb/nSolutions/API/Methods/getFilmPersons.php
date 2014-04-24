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
final class getFilmPersons extends \nSolutions\API\Methods
{
    // Nazwa metody
    public $method = 'getFilmPersons';
    
   /**
    * Wymagane parametry
    * @var array 
    */
    protected $_args =
    [
        'filmId',
        'type',
        'pageNo',
    ];
    
   /**
    * Dane zwrócone przez filmweba
    */
    protected $_response_keys =
    [
        'personId', 'assocName', 'assocAttributes', 'personName', 'personPhoto'
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
            $this->method => $this->filmId . ',' . $this->type . ',' . (50 * $this->pageNo) . ',' . 50 * ($this->pageNo + 1)
        ];        
    }
    
    protected function getData($response)
    {
        $data = [];
        $key = $this->_response_keys[0];
        $type = \nSolutions\Filmweb::$roles[$this->type];
        
        $i = 0;
        
        foreach($response as $item)
        {
            $i = new \stdClass;
            
            foreach($this->_response_keys as $k => $v)
            {
                if($v === 'personPhoto' AND ! is_null($item[$k]))
                {
                    $item[$k] = \nSolutions\Filmweb::$_config['personImageUrl'] . $item[$k];
                }
                
                $i->$v = $item[$k];
            }
            
            $data[] = $i;
        }
        
        return (object) $data;
    }
}