<?php

namespace QuantumTecnology\HandlerBasicsExtension\Exceptions;

class ApiResponseException extends \Exception
{
    private array $apiResponse = [];

    public function __construct($code = 0, array $apiResponse = [], $previous = null)
    {
        $this->apiResponse = $apiResponse;
        parent::__construct($apiResponse['message'], $code, $previous);
    }

    /**
     * Get the value of apiResponse.
     */
    public function getApiResponse()
    {
        return $this->apiResponse;
    }
}
