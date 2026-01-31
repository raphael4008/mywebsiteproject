<?php
namespace App\Models;

use App\Models\Listing;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Payment;

class Admin {

    public static function getDashboardData() {
        return [
            'totalListings' => Listing::countAll(),
            'totalUsers' => User::countAll(),
            'totalReservations' => Reservation::countAll(),
            'totalPayments' => Payment::countAll(),
        ];
    }

    public static function getListings() {
        return Listing::all();
    }

    public static function approveListing($id) {
        return Listing::update($id, ['status' => 'AVAILABLE', 'verified' => 1]);
    }

    public static function rejectListing($id) {
        return Listing::update($id, ['status' => 'REJECTED']);
    }

    public static function deleteListing($id) {
        return Listing::delete($id);
    }

    public static function getUsers() {
        return User::all();
    }

    public static function deleteUser($id) {
        return User::delete($id);
    }

    public static function getReservations() {
        return Reservation::getAll();
    }

    public static function confirmReservation($id) {
        return Reservation::updateStatus($id, 'CONFIRMED');
    }

    public static function cancelReservation($id) {
        return Reservation::updateStatus($id, 'CANCELLED');
    }

    public static function getPayments() {
        return Payment::all();
    }

    public static function approvePayment($id) {
        return Payment::update($id, ['status' => 'APPROVED']);
    }

    public static function rejectPayment($id) {
        return Payment::update($id, ['status' => 'REJECTED']);
    }
}
