<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class Response
{
    private const MAP = [
        // Error responses
        // 500 Internal Server Error
        'UNIVERSAL_ERROR' => [['success' => false, "code" => 50001, 'message' => 'Internal server error, please try again later.'], 500],
        'SERVER_ERROR' => [['success' => false, "code" => 50002, 'message' => 'Server error, please try again later.'], 500],

        // 400 Bad Request
        'INVALID_REQUEST' => [['success' => false, "code" => 40001, 'message' => 'Invalid request'], 400],

        // 404 Not Found
        'NOT_FOUND' => [['success' => false, "code" => 40401, 'message' => ''], 404],

        // 401 Unauthorized

        //429 Too Many Requests

        // 422 Unprocessable Entity
        'VALIDATION_ERROR' => [['success' => false, "code" => 42201, 'message' => 'The given data was invalid.'], 422],

        // Success responses
        'SUCCESS' => [['success' => true, "code" => 20000, 'message' => 'Success'], 200],
        'SUCCESS_CREATED' => [['success' => true, "code" => 20100, 'message' => 'Success'], 201],
    ];

    private const DEFAULT_RESPONSE = self::MAP['UNIVERSAL_ERROR'];

    /**
     * Get the response format for a given key.
     *
     * @param string $key The key to retrieve the response format.
     * @param string $custom_message Optional custom message to override the default message.
     * @param array<mixed> $data Optional data to include in the response.
     * @param array<mixed> $additional_array Optional additional data to include in the response.
     *
     * @return array<mixed> The response format containing success status and message.
     */
    public static function get(string $key = "SUCCESS", string $message = "", array | JsonResource $data = [], array $additional_array = []): array
    {
        $response = self::MAP[$key] ?? self::DEFAULT_RESPONSE;

        // Set the response code
        // $response[0] = ["success" => $response[0]['success'], "code" => $key] + $response[0];

        // If a custom message is provided, use it
        if ($message) {
            $response[0]['message'] = $message;
        }

        // If data is provided, merge it into the response
        if (!empty($data)) {
            $response[0]['data'] = $data;
        }

        // If additional array is provided, merge it into the response
        if (!empty($additional_array)) {
            $response[0] = array_merge($response[0], $additional_array);
        }

        return $response;
    }

    /**
     * Generate a JSON response based on the provided key and optional custom message and data.
     *
     * @param string $key The key to retrieve the response format.
     * @param string $custom_message Optional custom message to override the default message.
     * @param array<mixed> $data Optional data to include in the response.
     * @param array<mixed> $additional_array Optional additional data to include in the response.
     *
     * @return JsonResponse The JSON response object.
     */
    public static function json(string $key = "SUCCESS", string $message = "", array | JsonResource $data = [], array $additional_array = []): JsonResponse
    {
        $response = self::get($key, $message, $data, $additional_array);
        return response()->json(...$response);
    }
}
