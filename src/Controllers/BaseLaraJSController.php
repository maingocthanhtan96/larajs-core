<?php

namespace LaraJS\Core\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class BaseLaraJSController extends BaseController
{
    public function sendData($data, string $message = '', int $status = Response::HTTP_OK): JsonResponse
    {
        if ($data instanceof LengthAwarePaginator) {
            return response()->json(
                [
                    'data' => [
                        'items' => $data->items(),
                        'total' => $data->total(),
                    ],
                ],
                $status,
            );
        }

        return response()->json(
            [
                'message' => $message,
                'data' => $data,
            ],
            $status,
        );
    }

    /**
     * @author tanmnt
     */
    public function sendMessage(
        $message,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return response()->json(
            [
                'message' => $message,
            ],
            $status,
        );
    }
}
