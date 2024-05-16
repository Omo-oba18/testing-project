<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponder
{
    // 100-level (Informational) – server acknowledges a request
    // 200-level (Success) – server completed the request as expected
    // 300-level (Redirection) – client needs to perform further actions to complete the request
    // 400-level (Client error) – client sent an invalid request
    // 500-level (Server error) – server failed to fulfill a valid request due to an error with server

    /**
     * Success Response.
     *
     * @param  mixed  $data
     */
    protected function responseSuccess($data, string $message = null, int $httpStatus = Response::HTTP_OK): JsonResponse
    {
        // Error code: Successful responses (200–299)!
        if ($httpStatus < Response::HTTP_OK || $httpStatus > Response::HTTP_MULTIPLE_CHOICES) {
            dd('Successful responses (200–299)!');
        }

        // Pagination response.
        if ($data instanceof ResourceCollection) {
            ResourceCollection::$wrap = 'items';
            $newData = $data->response()->getData();

            // check data response is paginate or not
            // if have paginate then format data response
            if (! empty($newData->meta)) {
                $data = $newData;
            }
        } else {
            /*
             * Remove data wrapping with API Resources.
             *
             * @docs https://laravel.com/docs/8.x/eloquent-resources#data-wrapping
             */
            JsonResource::withoutWrapping();
        }

        // return json.
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }

    /**
     * Error Response.
     */
    protected function responseError(string $message, int $httpStatus, array $errors = []): JsonResponse
    {
        return response()->json(array_merge([
            'message' => $message,
            'data' => null,
        ], $errors), $httpStatus);
    }

    /**
     * Respond with success.
     */
    protected function responseSuccessWithMessage(string $message = null): JsonResponse
    {
        $message = $message ?? trans('response.success');

        return $this->responseSuccess(null, $message);
    }

    /**
     * Respond with no content.
     */
    protected function respondErrorWithNoData(string $message = null): JsonResponse
    {
        $message = $message ?? trans('response.http_status_404');

        return $this->responseError($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Respond with no content.
     */
    protected function respondErrorWithUnAuthorized(string $message = null): JsonResponse
    {
        $message = $message ?? trans('response.http_status_401');

        return $this->responseError($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Respond with forbidden.
     */
    protected function respondErrorWithForbidden(string $message = null): JsonResponse
    {
        $message = $message ?? trans('response.http_status_403');

        return $this->responseError($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Respond with internal server error.
     */
    protected function respondErrorWithInternalServer(string $message = null): JsonResponse
    {
        $message = $message ?? trans('response.http_status_500');

        return $this->responseError($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Respond with Validation error.
     *
     * @description use this method or using `throw ValidationException::withMessages(['field_name' => 'This value is incorrect']);`
     *
     * @param  ValidationException  $exception ($exception = \Illuminate\Validation\ValidationException::withMessages(['field_name_1' => ['Validation Message #1'], 'field_name_2' => ['Validation Message #2']]);)
     */
    protected function respondErrorWithValidation(ValidationException $exception): JsonResponse
    {
        $message = implode(chr(10), Arr::flatten($exception->errors()));

        return $this->responseError($message, $exception->status, ['errors' => $exception->errors()]);
    }
}
