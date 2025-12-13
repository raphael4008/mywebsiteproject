<?php
namespace App\Controllers;

use App\Models\Message;

use App\Controllers\BaseController;

class MessageController extends BaseController {
    public function create($data) {
        if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
            $this->jsonResponse(['message' => 'Name, email, and message are required.'], 400);
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['message' => 'Invalid email format.'], 400);
            return;
        }

        $result = Message::create($data['name'], $data['email'], $data['message']);

        if ($result) {
            $this->jsonResponse(['message' => 'Message sent successfully!'], 201);
        } else {
            $this->jsonResponse(['message' => 'Failed to send message.'], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}