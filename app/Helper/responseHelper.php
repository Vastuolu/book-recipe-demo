<?php

namespace App\Helper;

class responseHelper{
    protected static $status = [
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        422 => 'Unprocessable Content',
        500 => 'Internal Server Error'
    ];

    public static function response($statusCode, $message, $total=0, $data=null):array{
        $statusMessage = self::$status[$statusCode] ?? 'Unlisted Status Code';

        $response = [
            'total' => $total,
            'meessage' => $message,
            'statusCode' => $statusCode,
            'status' => $statusMessage
        ];

        if(isset($data)){
            $response['data'] = is_array($data) ? $data : [$data];
        }

        return $response;
    }
}

