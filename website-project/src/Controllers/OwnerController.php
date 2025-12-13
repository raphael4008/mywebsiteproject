<?php
namespace App\Controllers;

use App\Helpers\JwtMiddleware;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\OwnerPayment as Payment; // Ensure this is correctly imported
use App\Models\User; // Ensure this is correctly imported

class OwnerController extends BaseController {

    public function getDashboardStats() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        // $ownerId = 1;

        $listings = Listing::search(['owner_id' => $ownerId]);
        $reservations = Reservation::getAll(['owner_id' => $ownerId, 'status' => 'CONFIRMED']);
        $payments = Payment::findByOwnerId($ownerId);

        $totalListings = $listings['total'];
        $activeReservations = count($reservations);
        $totalEarnings = array_reduce($payments, function($carry, $payment) {
            return $carry + $payment['amount'];
        }, 0);

        $this->jsonResponse([
            'totalListings' => $totalListings,
            'activeReservations' => $activeReservations,
            'totalEarnings' => $totalEarnings,
        ]);
    }

    public function getListings() {
        // $user = JwtMiddleware::authorize();
        // $ownerId = $user['id'];
        $ownerId = 1;
        $listings = Listing::search(['owner_id' => $ownerId]);
        $this->jsonResponse($listings['data']);
    }

    public function deleteListing($id) {
        $listing = Listing::findById($id);
        $ownerId = JwtMiddleware::authorize()['id'];

        if (!$listing) {
            $this->jsonResponse(['error' => 'Listing not found'], 404);
            return;
        }

        if ($listing['owner_id'] != $ownerId) {
            $this->jsonResponse(['error' => 'Forbidden'], 403);
            return;
        }

        Listing::delete($id);
        $this->jsonResponse(['success' => true]);
    }

    public function getProfile() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $userData = User::findById($ownerId);
        unset($userData['password']); // Never return the password hash
        $this->jsonResponse($userData);
    }

    public function updateProfile($data) {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];

        // For security, prevent certain fields from being updated directly
        unset($data['id'], $data['email'], $data['role']);

        User::update($ownerId, $data);
        $this->jsonResponse(['success' => true]);
    }

    public function getReservations() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $reservations = Reservation::getAll(['owner_id' => $ownerId]);
        $this->jsonResponse($reservations);
    }

    public function getPayments() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $payments = Payment::findByOwnerId($ownerId);
        $this->jsonResponse($payments);
    }

    public function cancelReservation($id) {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];

        $reservation = Reservation::findById($id);
        if (!$reservation) {
            $this->jsonResponse(['error' => 'Reservation not found'], 404);
            return;
        }

        $listing = Listing::findById($reservation['listing_id']);
        if (!$listing) {
            $this->jsonResponse(['error' => 'Listing not found'], 404);
            return;
        }
        if ($listing['owner_id'] != $ownerId) {
            $this->jsonResponse(['error' => 'Forbidden'], 403);
            return;
        }

        Reservation::updateStatus($id, 'CANCELLED');
        $this->jsonResponse(['success' => true]);
    }
}


//go through my entire project the backend has so many bugs fix the , improve the styling in features and location and people pages , modernize the project all pages , improve the general functionality of the project , generally reduce redandance in the project , asl db , i want a lively project where data is directly from the db , places where images are supposed to be in the frontend to have the specific images