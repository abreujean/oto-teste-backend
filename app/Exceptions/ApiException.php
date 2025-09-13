<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    protected $error;

    public function __construct(string $message = "", string $error = "", int $code = 400)
    {
         parent::__construct($message, $code);
         $this->error = $error;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => $this->error,
        ], $this->getCode());
    }
}
