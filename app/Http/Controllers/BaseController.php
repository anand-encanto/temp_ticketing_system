<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */

    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {

    	$response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);

    }

    public function sendValidationError($error, $errorMessages = [], $code = 422)
    {

    	$response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);

    }


    public function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $time_diff = time() - $time;
        $seconds = $time_diff;
        
        $intervals = array(
            'year'   => 31536000, // 60 * 60 * 24 * 365
            'month'  => 2592000,  // 60 * 60 * 24 * 30
            'day'    => 86400,    // 60 * 60 * 24
            'hour'   => 3600,     // 60 * 60
            'minute' => 60,
            'second' => 1
        );
        
        foreach ($intervals as $unit => $value) {
            if ($seconds >= $value) {
                $number_of_units = floor($seconds / $value);
                return $number_of_units . ' ' . $unit . ($number_of_units > 1 ? 's' : '') . ' ago';
            }
        }
    }
}