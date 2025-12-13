<?php
namespace App\Controllers;

use App\Models\Admin;

class AdminController extends BaseController {

    public function dashboard() {
        $data = Admin::getDashboardData();
        $this->jsonResponse($data);
    }

    public function getListings() {
        $listings = Admin::getListings();
        $this->jsonResponse(['listings' => $listings]);
    }

    public function approveListing($id) {
        $success = Admin::approveListing($id);
        $this->jsonResponse(['success' => $success]);
    }

    public function rejectListing($id) {
        $success = Admin::rejectListing($id);
        $this->jsonResponse(['success' => $success]);
    }

    public function deleteListing($id) {
        $success = Admin::deleteListing($id);
        $this->jsonResponse(['success' => $success]);
    }

    public function getUsers() {
        $users = Admin::getUsers();
        $this->jsonResponse(['users' => $users]);
    }

    public function deleteUser($id) {
        $success = Admin::deleteUser($id);
        $this->jsonResponse(['success' => $success]);
    }

    public function getReservations() {
        $reservations = Admin::getReservations();
        $this->jsonResponse(['reservations' => $reservations]);
    }

    public function confirmReservation($id) {
        $success = Admin::confirmReservation($id);
        $this->jsonResponse(['success' => $success]);
    }

    public function cancelReservation($id) {
        $success = Admin::cancelReservation($id);
        $this->jsonResponse(['success' => $success]);
    }

    public function getPayments() {
        $payments = Admin::getPayments();
        $this->jsonResponse(['payments' => $payments]);
    }

    public function approvePayment($id) {
        $success = Admin::approvePayment($id);
        $this->jsonResponse(['success' => $success]);
    }

    public function rejectPayment($id) {
        $success = Admin::rejectPayment($id);
        $this->jsonResponse(['success' => $success]);
    }


}