<?php
/**
* @author Michell Hoduń
* @copyright (c) 2013 nSolutions.pl
* @description Filmweb.pl API
* @version 1.0b
*/
namespace nSolutions;
class Parser
{
    public static function parse($response)
    {
        $data = explode("\n", $response);
        if(! isset($data[0]) OR $data[0] !== "ok")
            throw new \Exception('Nie pobrano żadnych danych...');
        
        $row = preg_replace('/ t:.*/is', '', $data[1]);
        return json_decode($row);
    }
}