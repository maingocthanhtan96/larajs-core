<?php

namespace LaraJS\Core\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseLaraJSController extends BaseController
{
    public function jsonData($data, string $message = '', int $status = Response::HTTP_OK): JsonResponse
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
    public function jsonMessage(
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

    public function jsonValidate($errors): JsonResponse
    {
        return response()->json(
            [
                'errors' => $errors,
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    public function jsonMetadata($data, $meta, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            [
                'data' => $data,
                'meta' => $meta,
            ],
            $status,
        );
    }
}
