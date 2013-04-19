<?php
/**
* @author Michell Hoduń
* @copyright (c) 2013 nSolutions.pl
* @description Filmweb.pl API
* @version 1.0b
*/
namespace nSolutions;
class API
{
    const KEY = 'qjcGhW2JnvGT9dfCt3uT_jozR3s';
    
    public static $methods =
    [
        'getFilmInfoFull' =>
        [
            'args' => ['id'],
            'method' => 'GET',
            'response' => ['title', 'orginal_title', 'rate', 'ratedby', 'cats', 'year', 'minutes', 'aa', '1111', '1111', '111111', 'cover', 'dasdas', 'premiere_world', 'premiere_pl', 'dsadsad', 'dasdsad',' kimdasasd', 'production', 'short_desc']
        ],
        'getFilmDescription' =>
        [
            'args' => ['id'],
            'method' => 'GET',
            'response' => ['description']
        ]
    ];
    
    public static function __callStatic($name, $arguments)
    {
        if(! isset(\nSolutions\API::$methods[$name]))
            throw new \Exception('Brak zdefiniowanej metody '.$name .' w API');
        
        $method = \nSolutions\API::$methods[$name];
        
        if(sizeof($arguments[0]) < sizeof($method['args']))
            throw new \Exception('Nie podano wszystkich wymaganych argumentów');
        
        $url = 'methods=';
        $m = $name;
        
        if(sizeof($method['args']) > 0)
            $m .= ' ['.implode(',',$arguments[0]).']';
        
        $m .= '\n';       
        
        // Generowanie sygnatury
        $signature = '1.0,'.md5($m.'android'.\nSolutions\API::KEY);
        
        // Wygenerowanie pełnego adresu
        $url = \nSolutions\Filmweb::API_SERVER . $url . urlencode($m).'&signature='.$signature;
        
        // Wysłanire requesta i pobranie odpowiedzi
        $response = \nSolutions\API::parse_json(\nSolutions\Request::execute($url));
        
        // Obrobienie odpowiedzi.
        if(! is_array($response))
            throw new \Exception('Nie można było sparsować danych');
        
        $return = new \stdClass();
        
        foreach($response as $k => $v)
        {   
            if($method['response'][$k] === 'cover')
            {
                $v = \nSolutions\Filmweb::$_config['filmImageUrl'].strtr($v, [
                    '2.jpg' => '3.jpg'
                ]);
            }
            $return->$method['response'][$k] = $v;
        }
        
        return $return;
    }
    
    public static function parse_json($response)
    {
        $data = explode("\n", $response);
        if(! isset($data[0]) OR $data[0] !== "ok")
            throw new \Exception('Nie pobrano żadnych danych...');
        
        $row = preg_replace('/ t:.*/is', '', $data[1]);
        return json_decode($row);
    }
}