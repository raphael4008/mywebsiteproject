<?php
namespace App\Controllers;

use App\Helpers\JwtMiddleware;
use App\Helpers\Request;
use App\Config\DatabaseConnection;
use App\Models\User;
use App\Models\Reservation;
use App\Controllers\BaseController;

class UserController extends BaseController {

    public function getMe() {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $userData = User::find($userId);
        if (!$userData) {
            $this->jsonErrorResponse('User not found', 404);
            return;
        }

        unset($userData['password']);

        // Attach favorites
        $pdo = DatabaseConnection::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT f.listing_id AS id, l.title, COALESCE(i.path, i.image_path) AS path FROM favorites f LEFT JOIN listings l ON f.listing_id = l.id LEFT JOIN images i ON i.listing_id = l.id AND (i.is_main = 1 OR i.is_primary = 1) WHERE f.user_id = ? ORDER BY f.created_at DESC");
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Attach saved searches
        $stmt2 = $pdo->prepare("SELECT id, criteria, created_at FROM saved_searches WHERE user_id = ? ORDER BY created_at DESC");
        $stmt2->execute([$userId]);
        $searches = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

        $userData['favorites'] = $favorites;
        $userData['saved_searches'] = $searches;

        $this->jsonResponse($userData);
    }

    public function updateMe() {
        $data = Request::all();
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        // Prevent updates to protected fields
        unset($data['id'], $data['email'], $data['role'], $data['password']);

        $updated = User::update($userId, $data);
        if ($updated) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonErrorResponse('Failed to update user', 500);
        }
    }

    public function getFavorites() {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $pdo = DatabaseConnection::getInstance()->getConnection();
        $sql = "SELECT l.*, f.created_at AS favorited_at, (SELECT path FROM images WHERE listing_id = l.id AND is_main = 1 LIMIT 1) AS main_image
                FROM favorites f
                JOIN listings l ON f.listing_id = l.id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->jsonResponse($favorites);
    }

    public function addFavorite($listingId) {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $pdo = DatabaseConnection::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, listing_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, (int)$listingId]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            error_log('Error adding favorite: ' . $e->getMessage());
            $this->jsonErrorResponse('Failed to add favorite', 500);
        }
    }

    public function removeFavorite($listingId) {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $pdo = DatabaseConnection::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND listing_id = ?");
            $stmt->execute([$userId, (int)$listingId]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            error_log('Error removing favorite: ' . $e->getMessage());
            $this->jsonErrorResponse('Failed to remove favorite', 500);
        }
    }

    public function getSavedSearches() {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $pdo = DatabaseConnection::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT id, criteria, created_at FROM saved_searches WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $searches = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->jsonResponse($searches);
    }

    public function getReservations() {
        $user = JwtMiddleware::authorize();
        $userId = $user['id'];

        $reservations = Reservation::getAll(['user_id' => $userId]);
        $this->jsonResponse($reservations);
    }
}
