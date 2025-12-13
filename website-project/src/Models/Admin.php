<?php
namespace App\Models;

use Database;
use PDO;

class Admin {
    public static function getDashboardData() {
        $db = Database::getInstance();
        
        $stmt = $db->query("SELECT COUNT(*) as totalListings FROM listings");
        $totalListings = $stmt->fetch(PDO::FETCH_ASSOC)['totalListings'];
        
        $stmt = $db->query("SELECT COUNT(*) as totalUsers FROM users");
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['totalUsers'];
        
        $stmt = $db->query("SELECT COUNT(*) as totalReservations FROM reservations");
        $totalReservations = $stmt->fetch(PDO::FETCH_ASSOC)['totalReservations'];
        
        $stmt = $db->query("SELECT COUNT(*) as totalPayments FROM reservation_fees");
        $totalPayments = $stmt->fetch(PDO::FETCH_ASSOC)['totalPayments'];
        
        return [
            'totalListings' => $totalListings,
            'totalUsers' => $totalUsers,
            'totalReservations' => $totalReservations,
            'totalPayments' => $totalPayments,
        ];
    }

    public static function getListings() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM listings");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function approveListing($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE listings SET status = 'AVAILABLE', verified = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function rejectListing($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE listings SET status = 'REJECTED' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function deleteListing($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM listings WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getUsers() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT id, name, email, role, created_at FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteUser($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getReservations() {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT r.id, r.start_date, r.end_date, r.status, l.title as listing_title, u.name as user_name
            FROM reservations r
            JOIN listings l ON r.listing_id = l.id
            JOIN users u ON r.user_id = u.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function confirmReservation($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE reservations SET status = 'CONFIRMED' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function cancelReservation($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE reservations SET status = 'CANCELLED' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getPayments() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM reservation_fees");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function approvePayment($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE reservation_fees SET status = 'APPROVED' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function rejectPayment($id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE reservation_fees SET status = 'REJECTED' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}