<?php
namespace App\Controllers;

class BaseController {
    protected function jsonResponse($data, $statusCode = 200) {
        // Clear buffer to remove any PHP notices/warnings that might break JSON
        while (ob_get_level()) ob_end_clean();
        
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Sends a standardized JSON error response.
     *
     * @param string $message The error message.
     * @param int $statusCode The HTTP status code for the error.
     * @param array $errors Optional array of detailed error messages.
     */
    protected function jsonErrorResponse(string $message, int $statusCode, array $errors = []) {
        // Clear buffer
        while (ob_get_level()) ob_end_clean();
        
        http_response_code($statusCode);
        header('Content-Type: application/json');
        $response = ['error' => $message];
        if (!empty($errors)) {
            $response['details'] = $errors;
        }
        echo json_encode($response);
    }
}
