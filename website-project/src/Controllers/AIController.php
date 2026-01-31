<?php

namespace App\Controllers;

use App\Config\Config;
use App\Helpers\Request;
use OpenAI;

class AIController extends BaseController
{
    /**
     * Handles the AI chat requests.
     */
    public function handleChat()
    {
    $data = Request::all();
        $userMessage = $data['message'] ?? '';

        if (empty($userMessage)) {
            $this->jsonErrorResponse('Please provide a message.', 400);
            return;
        }

        try {
            $client = OpenAI::client(Config::get('OPENAI_API_KEY'));

            $systemPrompt = "You are a helpful assistant for a real estate website called HouseHunting. You can help users find properties, answer questions about the website, and provide general real estate advice. Be friendly and conversational.";

            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

            $reply = $response->choices[0]->message->content ?? '';

            $this->jsonResponse(['reply' => $reply]);
        } catch (\Exception $e) {
            // Log the error
            error_log($e->getMessage());
            $this->jsonErrorResponse('An error occurred while processing your request.', 500);
        }
    }
}
