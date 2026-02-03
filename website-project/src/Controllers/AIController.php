<?php
namespace App\Controllers;

use App\Services\ChatbotService;

class AIController extends BaseController {
    public function handleChat() {
        $data = json_decode(file_get_contents('php://input'), true);
        $message = $data['message'] ?? '';

        if (empty($message)) {
            $this->jsonErrorResponse('Message is empty.', 400);
            return;
        }

        $chatbotService = new ChatbotService();
        $response = $chatbotService->getReply($message);

        $this->jsonResponse(['reply' => $response]);
    }
}