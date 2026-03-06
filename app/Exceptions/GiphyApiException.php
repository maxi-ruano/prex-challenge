<?php

namespace App\Exceptions;

use Exception;

class GiphyApiException extends Exception
{
    protected $code = 502;
    
    public function __construct($message = 'Error al comunicarse con GIPHY', $code = 502)
    {
        parent::__construct($message, $code);
    }
}