<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message,$addtionalData = null)
    {
    	$response = [
            'success'        => true,
            'data'           => $result,
            'addtionalData'  => $addtionalData,
            'message'        => $message,
        ];


        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */

    public function sendError($error, $errorMessages = [], $code = null)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = '';//$errorMessages;
        }

        $code == null ? $code = 422 : $code;

        return response()->json($response, $code);
    }

    /**
     * not authorized response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendNotAuthorized($result, $message)
    {
    	$response = [
            'success' => false,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 403);
    }
}