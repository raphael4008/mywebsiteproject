<?php
namespace App\Controllers;

use App\Helpers\JwtMiddleware;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\OwnerPayment as Payment;
use App\Models\User;

class OwnerController extends BaseController {

    public function getDashboardStats() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];

        $totalProperties = Listing::search(['owner_id' => $ownerId])['total'];
        $activeListings = Listing::search(['owner_id' => $ownerId, 'status' => 'AVAILABLE'])['total'];
        $payments = Payment::findByOwnerId($ownerId);

        $totalRevenue = array_reduce($payments, function($carry, $payment) {
            return $carry + $payment['amount'];
        }, 0);
        
        // totalViews is not directly implemented in the backend yet, setting to 0
        $totalViews = 0; 

        $this->jsonResponse([
            'totalProperties' => $totalProperties,
            'activeListings' => $activeListings,
            'totalViews' => $totalViews,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    /**
     * New method to fetch owner's listings with detailed stats.
     */
    public function getMyListings() {
        // 1. Security: Validate JWT and ensure 'owner' role.
        $user = JwtMiddleware::authorizeWithRole('owner');
        $ownerId = $user['id'];

        // 2. Query: Fetch all listings for the owner
        $ownerListings = Listing::search(['owner_id' => $ownerId, 'limit' => -1])['data'];
        
        if (!$ownerListings) {
            $this->jsonResponse(['status' => 'success', 'data' => [
                'listings' => [],
                'stats' => [
                    'total_listings' => 0,
                    'total_views' => 0,
                    'occupancy_rate' => 0
                ]
            ]]);
            return;
        }

        // 3. Stats Calculation
        $total_listings = count($ownerListings);
        
        // total_views: This requires a schema change (e.g., an integer 'views' column on the listings table).
        // For now, we will return a placeholder value. We can sum the non-existent column for future-proofing.
        $total_views = array_reduce($ownerListings, function($sum, $item) {
            return $sum + ($item['views'] ?? 0);
        }, 0);

        $occupied_count = 0;
        foreach ($ownerListings as $listing) {
            if (in_array(strtolower($listing['status']), ['rented', 'reserved'])) {
                $occupied_count++;
            }
        }
        $occupancy_rate = ($total_listings > 0) ? round(($occupied_count / $total_listings) * 100, 2) : 0;

        // 4. Return JSON Response
        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'listings' => $ownerListings,
                'stats' => [
                    'total_listings' => $total_listings,
                    'total_views' => $total_views, // Placeholder, see comment above
                    'occupancy_rate' => $occupancy_rate
                ]
            ]
        ]);
    }

    public function getListings() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $listings = Listing::search(['owner_id' => $ownerId]);
        $this->jsonResponse($listings['data']);
    }

    public function deleteListing($id) {
        $listing = Listing::find($id);
        $ownerId = JwtMiddleware::authorize()['id'];

        if (!$listing) {
            $this->jsonErrorResponse('Listing not found', 404);
            return;
        }

        if ($listing['owner_id'] != $ownerId) {
            $this->jsonErrorResponse('Forbidden', 403);
            return;
        }

        Listing::delete($id);
        $this->jsonResponse(['success' => true]);
    }

    public function getProfile() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $userData = User::find($ownerId);
        unset($userData['password']);
        $this->jsonResponse($userData);
    }
    
    public function updateProfile() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];

        unset($data['id'], $data['email'], $data['role']);

        User::update($ownerId, $data);
        $this->jsonResponse(['success' => true]);
    }

    public function getReservations() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $reservations = Reservation::getAll(['owner_id' => $ownerId]);
        
        $formattedReservations = array_map(function($reservation) {
            $reservation['tenant_name'] = $reservation['user_name'] ?? 'N/A';
            unset($reservation['user_name']);
            return $reservation;
        }, $reservations);

        $this->jsonResponse($formattedReservations);
    }

    public function getActivities() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $activities = [];

        $recentListings = Listing::search(['owner_id' => $ownerId, 'limit' => 10, 'sort' => 'newest'])['data'];
        foreach ($recentListings as $listing) {
            $activities[] = [
                'property' => $listing['title'],
                'date' => (new \DateTime($listing['created_at']))->format('Y-m-d H:i:s'),
                'action' => 'Property Added',
                'status' => $listing['status']
            ];
        }

        $recentReservations = Reservation::getAll(['owner_id' => $ownerId, 'limit' => 10]);
        foreach ($recentReservations as $reservation) {
            $activities[] = [
                'property' => $reservation['listing_title'],
                'date' => (new \DateTime($reservation['created_at']))->format('Y-m-d H:i:s'),
                'action' => 'Reservation (' . ($reservation['user_name'] ?? 'N/A') . ')',
                'status' => $reservation['status']
            ];
        }

        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        $activities = array_slice($activities, 0, 10);
        $this->jsonResponse($activities);
    }

    public function getPayments() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $payments = Payment::findByOwnerId($ownerId);
        $this->jsonResponse($payments);
    }

    public function getFinancials() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $this->jsonResponse([
            'totalRevenue' => 120000,
            'monthlyEarnings' => 15000,
            'pendingPayouts' => 5000,
        ]);
    }

    public function getTransactions() {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $transactions = [
            ['date' => '2024-01-10', 'type' => 'Payout', 'property' => 'Cozy Apartment in Nairobi', 'amount' => 5000, 'status' => 'Completed'],
            ['date' => '2024-01-05', 'type' => 'Booking', 'property' => 'Spacious Villa in Mombasa', 'amount' => 10000, 'status' => 'Completed'],
            ['date' => '2024-01-02', 'type' => 'Booking', 'property' => 'Cozy Apartment in Nairobi', 'amount' => 5000, 'status' => 'Completed']
        ];
        $this->jsonResponse($transactions);
    }

    public function cancelReservation($id) {
        $user = JwtMiddleware::authorize();
        $ownerId = $user['id'];
        $reservation = Reservation::findById($id);

        if (!$reservation) {
            $this->jsonErrorResponse('Reservation not found', 404);
            return;
        }

        $listing = Listing::find($reservation['listing_id']);
        if (!$listing || $listing['owner_id'] != $ownerId) {
            $this->jsonErrorResponse('Forbidden', 403);
            return;
        }

        Reservation::updateStatus($id, 'CANCELLED');
        $this->jsonResponse(['success' => true]);
    }

    public function getUnavailability($listingId) {
        $ownerId = JwtMiddleware::authorize()['id'];
        $listing = Listing::find($listingId);

        if (!$listing || $listing['owner_id'] != $ownerId) {
            $this->jsonErrorResponse('Forbidden', 403);
            return;
        }

        $unavailability = \App\Models\PropertyUnavailability::findByListing($listingId);
        $this->jsonResponse($unavailability);
    }

    public function addUnavailability($listingId) {
        $ownerId = JwtMiddleware::authorize()['id'];
        $listing = Listing::find($listingId);

        if (!$listing || $listing['owner_id'] != $ownerId) {
            $this->jsonErrorResponse('Forbidden', 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['start_date']) || empty($data['end_date'])) {
            $this->jsonErrorResponse('Invalid start or end date.', 400);
            return;
        }

        $id = \App\Models\PropertyUnavailability::create([
            'listing_id' => $listingId,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        $this->jsonResponse(['success' => true, 'id' => $id]);
    }

    public function deleteUnavailability($unavailabilityId) {
        $ownerId = JwtMiddleware::authorize()['id'];
        $unavailability = \App\Models\PropertyUnavailability::find($unavailabilityId);

        if (!$unavailability) {
            $this->jsonErrorResponse('Unavailability record not found.', 404);
            return;
        }

        $listing = Listing::find($unavailability['listing_id']);
        if (!$listing || $listing['owner_id'] != $ownerId) {
            $this->jsonErrorResponse('Forbidden', 403);
            return;
        }

        \App\Models\PropertyUnavailability::delete($unavailabilityId);
        $this->jsonResponse(['success' => true]);
    }
}
