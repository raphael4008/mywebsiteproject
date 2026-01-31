<?php
namespace App\Controllers;

use App\Models\User;
// use App\Models\Message as MessageModel; // This model is not used in this file
use App\Models\Conversation as ConversationModel;
use App\Helpers\JwtMiddleware;

class ConversationController extends BaseController {

    public function getConversations() {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $conversations = ConversationModel::getConversationsForUser($userId); // This method should return an array of conversation data

        // Enrich conversations with partner details
        foreach ($conversations as &$conversation) {
            $partnerId = ($conversation['user1_id'] == $userId) ? $conversation['user2_id'] : $conversation['user1_id'];
            $partner = User::find($partnerId); // Assuming User model has a find method
            if ($partner) {
                $conversation['partner_name'] = $partner['name'];
                $conversation['partner_email'] = $partner['email'];
            }
        }
        $this->jsonResponse($conversations);
    }

    public function getMessages($conversationPartnerId) {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $messages = ConversationModel::getMessagesBetween($userId, $conversationPartnerId);
        $this->jsonResponse($messages);
    }

    public function sendMessage($receiverId) {
        $user = JwtMiddleware::authorize();
        $senderId = $user['id'];

        $data = json_decode(file_get_contents('php://input'), true);
        $messageText = $data['message'] ?? null;
        $listingId = $data['listing_id'] ?? null;

        if (!$messageText) {
            $this->jsonErrorResponse('Message text is required.', 400);
            return;
        }

        $messageId = ConversationModel::createMessage([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'listing_id' => $listingId,
            'message' => $messageText,
        ]);

        if ($messageId) {
            $this->jsonResponse(['success' => true, 'message_id' => $messageId]);
        } else {
            $this->jsonErrorResponse('Failed to send message.', 500);
        }
    }
}
