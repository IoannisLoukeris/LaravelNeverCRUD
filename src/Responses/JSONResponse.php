<?php

namespace LaravelNeverCrud\Responses;

/**
 * JSONResponse, handles the response of server to client
 */
class JSONResponse
{
  public static function success($data = null, int $statusCode = 200)
  {
    return response()->json(
      [
        'status' => 'success',
        'data' => $data,
      ],
      $statusCode
    );
  }

  public static function failure($errorType,  int $statusCode = 500, $errors = null)
  {
    return response()->json(
      [
        'status' => 'failure',
        'error' => $errorType,
        'errors' => $errors ? json_decode($errors, true) : null
      ],
      $statusCode
    );
  }
}
