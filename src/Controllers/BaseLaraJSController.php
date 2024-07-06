<?php

namespace LaraJS\Core\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class BaseLaraJSController
{
    /**
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $status
     * @return JsonResponse
     */
    public function responseData(mixed $data, string $message = '', int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'data' => $data,
            ],
            $status,
        );
    }

    /**
     * @param  $message
     * @param  int  $status
     * @return JsonResponse
     */
    public function responseMessage(
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

    /**
     * @param  JsonResource  $resource
     * @param  string  $message
     * @param  int  $status
     * @return JsonResponse
     */
    public function responseResource(JsonResource $resource, string $message = '', int $status = Response::HTTP_OK): JsonResponse
    {
        if ($message) {
            $resource->additional(['message' => $message]);
        }

        return $resource->response()->setStatusCode($status);
    }
}
