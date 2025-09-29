<?php

namespace App\Helpers;

class ApiHelper
{
    /**
     * Validate required parameters.
     */
    public static function validateParams(array $params, array $required)
    {
        $missing = [];

        foreach ($required as $key) {
            if (! isset($params[$key]) || $params[$key] === null || $params[$key] === '') {
                $missing[] = $key;
            }
        }

        if (! empty($missing)) {
            return self::response(
                false,
                'Missing required parameter(s): '.implode(', ', $missing),
                null,
            );
        }

        return null; // No issues
    }

    /**
     * Standard API response formatter.
     */
    public static function response(bool $success, string $message, $data = null, $total = null)
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'total' => $total,
        ];
    }

    /**
     * Format API result and wrap if data is empty.
     */
    public static function handleApiResponse(array $apiResult)
    {
        if (empty($apiResult['data'] || $apiResult['total'] === 0)) {
            return self::response(false, 'No data found.', []);
        }

        return self::response(
            true,
            'Data retrieved successfully.',
            $apiResult['data'],
            $apiResult['total'],
        );
    }
}
