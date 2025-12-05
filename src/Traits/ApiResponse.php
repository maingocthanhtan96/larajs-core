<?php

namespace LaraJS\Core\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    public function responseData(mixed $data, string $message = '', int $status = Response::HTTP_OK): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $message && $data->additional(['message' => $message]);
            return $data->response()->setStatusCode($status);
        }

        $response = $message
            ? ['data' => $data, 'message' => $message]
            : ['data' => $data];

        return new JsonResponse($response, $status);
    }

    public function responseMessage(string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }
}
