<?php
namespace App\Controllers;

use App\Helpers\JwtMiddleware;

class AdminController extends BaseController
{
    /**
     * Get aggregated system-wide statistics for the admin dashboard.
     */
    public function getSystemStats()
    {
        // Note: Middleware for role check is handled at the routing level.

        // 1. Financials
        $totalRevenueQuery = "SELECT SUM(amount) as total FROM payments WHERE status IN ('Completed', 'confirmed', 'paid')";
        $totalRevenue = $this->pdo->query($totalRevenueQuery)->fetchColumn() ?: 0;

        $monthlyRevenueQuery = "SELECT SUM(amount) as total FROM payments WHERE status IN ('Completed', 'confirmed', 'paid') AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $monthlyRevenue = $this->pdo->query($monthlyRevenueQuery)->fetchColumn() ?: 0;

        // 2. Growth
        $totalUsersQuery = "SELECT COUNT(id) FROM users";
        $totalUsers = $this->pdo->query($totalUsersQuery)->fetchColumn();

        $newUsersQuery = "SELECT COUNT(id) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $newUsers = $this->pdo->query($newUsersQuery)->fetchColumn();

        // 3. Inventory
        $activeListingsQuery = "SELECT COUNT(id) FROM listings WHERE status = 'available'";
        $activeListings = $this->pdo->query($activeListingsQuery)->fetchColumn();
        
        $pendingListingsQuery = "SELECT COUNT(id) FROM listings WHERE status = 'pending'";
        $pendingListings = $this->pdo->query($pendingListingsQuery)->fetchColumn();

        // 4. Health: Recent Activities (a mix of new listings and new users)
        $recentActivityQuery = "
            (SELECT id, 'New Listing' as type, title as details, created_at FROM listings ORDER BY created_at DESC LIMIT 3)
            UNION ALL
            (SELECT id, 'New User' as type, name as details, created_at FROM users ORDER BY created_at DESC LIMIT 2)
            ORDER BY created_at DESC
        ";
        $recentActivity = $this->pdo->query($recentActivityQuery)->fetchAll(\PDO::FETCH_ASSOC);

        // Assemble the response
        $stats = [
            'financials' => [
                'total_revenue' => (float)$totalRevenue,
                'revenue_this_month' => (float)$monthlyRevenue
            ],
            'growth' => [
                'total_users' => (int)$totalUsers,
                'new_users_last_30_days' => (int)$newUsers
            ],
            'inventory' => [
                'active_listings' => (int)$activeListings,
                'pending_approval' => (int)$pendingListings
            ],
            'health' => [
                'recent_activity' => $recentActivity
            ]
        ];

        $this->jsonResponse(['status' => 'success', 'data' => $stats]);
    }

    // Other admin methods from previous steps can be added here if they don't exist
}
