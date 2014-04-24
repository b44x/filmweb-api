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
final class Login extends \nSolutions\API\Methods
{
    // Nazwa metody
    protected $method = 'login';
    protected $post = TRUE;

   /**
    * Wymagane parametry
    * @var array 
    */
    protected $_args =
    [
        'login',
        'password',
    ];
    
   /**
    * Dane zwrócone przez filmweba
    */
    protected $_response_keys =
    [
        'username',
        'dont_know',
        'name',
        'user_id',
        'gender'
    ];
    
    protected function prepare()
    {
        $this->methods = [
            $this->method => '"'.$this->login.'"' . ', ' . '"'.$this->password.'", 1'
        ];        
    }
}