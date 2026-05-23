<?php
namespace App\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * Paginated success response. Keeps the same envelope as sendResponse():
     * records go into `data` as a plain array; pagination meta goes into `addtionalData`.
     */
    public function sendPaginatedResponse(LengthAwarePaginator $paginator, $message)
    {
        return $this->sendResponse(
            $paginator->items(),
            $message,
            [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ]
        );
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