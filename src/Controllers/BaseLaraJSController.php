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
        return response()->json(
            [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
            $status,
        );
    }

    /**
     * @author tanmnt
     */
    public function jsonTable(LengthAwarePaginator $paginator, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            [
                'success' => true,
                'data' => [
                    'items' => $paginator->items(),
                    'total' => $paginator->total(),
                ],
            ],
            $status,
        );
    }

    /**
     * @author tanmnt
     */
    public function jsonError($error, int $status = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        if ($error instanceof HttpException) {
            $status = $error->getStatusCode();
        }
        if ($error instanceof \Exception) {
            write_log_exception($error);
        }
        if (app()->isProduction()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => trans('errors.unexpected_error'),
                ],
                $status,
            );
        }

        return response()->json(
            [
                'success' => false,
                'message' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ],
            $status,
        );
    }

    /**
     * @author tanmnt
     */
    public function jsonMessage(
        $message,
        bool $success = true,
        bool $showMessage = true,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return response()->json(
            [
                'success' => $success,
                'message' => $message,
                'show_message' => $showMessage,
            ],
            $status,
        );
    }

    public function jsonValidate($errors): JsonResponse
    {
        return response()->json(
            [
                'success' => false,
                'errors' => $errors,
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    public function jsonMetadata($data, $meta, bool $success = true, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            [
                'success' => $success,
                'data' => $data,
                'meta' => $meta,
            ],
            $status,
        );
    }
}
