<?php
namespace App\Controllers;

use App\Helpers\JwtMiddleware;
use App\Models\Reservation as ReservationModel;
use App\Controllers\BaseController;

class ReservationController extends BaseController {
    public function getAll() {
        $user = JwtMiddleware::authorize();
        $reservations = ReservationModel::getAll(['user_id' => $user['id']]);
        $this->jsonResponse($reservations);
    }

    public function getById($id) {
        $user = JwtMiddleware::authorize();
        $reservation = ReservationModel::findById($id);

        if (!$reservation || $reservation['user_id'] != $user['id']) {
            $this->jsonResponse(['error' => 'Reservation not found'], 404);
            return;
        }

        $this->jsonResponse($reservation);
    }

    public function create($data) {
        $user = JwtMiddleware::authorize();
        $data['user_id'] = $user['id'];

        try {
            $reservationId = ReservationModel::create($data);
            $this->jsonResponse(['success' => true, 'id' => $reservationId]);
        } catch (\Exception $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus($id, $data) {
        $user = JwtMiddleware::authorize();
        $reservation = ReservationModel::findById($id);

        if (!$reservation || $reservation['user_id'] != $user['id']) {
            $this->jsonResponse(['error' => 'Reservation not found'], 404);
            return;
        }

        ReservationModel::updateStatus($id, $data['status']);
        $this->jsonResponse(['success' => true]);
    }

    public function updateFeePaid($id, $data) {
        $user = JwtMiddleware::authorize();
        $reservation = ReservationModel::findById($id);

        if (!$reservation || $reservation['user_id'] != $user['id']) {
            $this->jsonResponse(['error' => 'Reservation not found'], 404);
            return;
        }

        ReservationModel::updateFeePaid($id, $data['fee_paid']);
        $this->jsonResponse(['success' => true]);
    }

    public function delete($id) {
        $user = JwtMiddleware::authorize();
        $reservation = ReservationModel::findById($id);

        if (!$reservation || $reservation['user_id'] != $user['id']) {
            $this->jsonResponse(['error' => 'Reservation not found'], 404);
            return;
        }

        ReservationModel::delete($id);
        $this->jsonResponse(['success' => true]);
    }
}
