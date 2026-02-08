<?php
namespace App\Controllers;

use App\Helpers\JwtMiddleware;

class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get aggregated system-wide statistics for the admin dashboard.
     */
    public function getStats()
    {
        // Note: Middleware for role check is handled at the routing level.

        $totalUsersQuery = "SELECT COUNT(id) FROM users";
        $totalUsers = $this->pdo->query($totalUsersQuery)->fetchColumn();

        $totalListingsQuery = "SELECT COUNT(id) FROM listings";
        $totalListings = $this->pdo->query($totalListingsQuery)->fetchColumn();
        
        $pendingListingsQuery = "SELECT COUNT(id) FROM listings WHERE status = 'pending' OR verified = 0";
        $pendingListings = $this->pdo->query($pendingListingsQuery)->fetchColumn();

        // Assemble the response
        $stats = [
            'totalUsers' => (int)$totalUsers,
            'totalListings' => (int)$totalListings,
            'pendingListings' => (int)$pendingListings
        ];

        $this->jsonResponse($stats);
    }

    public function getListings()
    {
        $query = "
            SELECT l.id, l.title, l.status, l.verified, u.name as owner_name
            FROM listings l
            LEFT JOIN users u ON l.owner_id = u.id
            ORDER BY l.created_at DESC
        ";
        $stmt = $this->pdo->query($query);
        $listings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->jsonResponse($listings);
    }

    public function verifyListing($id)
    {
        $query = "UPDATE listings SET verified = 1, status = 'available' WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);

        $this->jsonResponse(['status' => 'success', 'message' => 'Listing verified successfully.']);
    }

    public function deleteListing($id)
    {
        // You might want to add more checks here, e.g., for related records.
        $query = "DELETE FROM listings WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);

        $this->jsonResponse(['status' => 'success', 'message' => 'Listing deleted successfully.']);
    }

    public function getUsers()
    {
        $query = "SELECT id, name, email, role FROM users ORDER BY created_at DESC";
        $stmt = $this->pdo->query($query);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->jsonResponse($users);
    }

    public function updateUserRole($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newRole = $data['role'] ?? null;

        if (!$newRole) {
            $this->jsonResponse(['status' => 'error', 'message' => 'New role is required.'], 400);
            return;
        }

        // You might want to add validation to ensure the role is a valid one.
        $query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$newRole, $id]);

        $this->jsonResponse(['status' => 'success', 'message' => 'User role updated successfully.']);
    }

    public function deleteUser($id)
    {
        // You might want to add more checks here, e.g., for related records.
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);

        $this->jsonResponse(['status' => 'success', 'message' => 'User deleted successfully.']);
    }

    public function getReservations()
    {
        $query = "SELECT id, listing_id, user_id, status, payment_status FROM reservations ORDER BY created_at DESC";
        $stmt = $this->pdo->query($query);
        $reservations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->jsonResponse($reservations);
    }

    // Other admin methods from previous steps can be added here if they don't exist
}
