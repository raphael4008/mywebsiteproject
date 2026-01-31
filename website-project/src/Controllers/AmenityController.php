<?php
namespace App\Controllers;

use App\Models\Amenity;
use App\Controllers\BaseController;

class AmenityController extends BaseController {

    public function index() {
        try {
            $amenities = Amenity::all();
            if ($amenities) {
                $this->jsonResponse($amenities);
            } else {
                $this->jsonResponse(["message" => "No amenities found."], 404);
            }
        } catch (\Exception $e) {
            $this->jsonErrorResponse('Error fetching amenities: ' . $e->getMessage(), 500);
        }
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['name'])) {
            $amenityId = Amenity::create(['name' => $data['name']]);
            if ($amenityId) {
                $this->jsonResponse(["message" => "Amenity was created.", "id" => $amenityId], 201);
            } else {
                $this->jsonErrorResponse("Unable to create amenity.", 503);
            }
        } else {
            $this->jsonErrorResponse("Unable to create amenity. Data is incomplete.", 400);
        }
    }

    public function delete($id) {
        if (!empty($id)) {
            if (Amenity::delete($id)) {
                $this->jsonResponse(["message" => "Amenity was deleted."]);
            } else {
                $this->jsonErrorResponse("Unable to delete amenity.", 503);
            }
        } else {
            $this->jsonErrorResponse("Unable to delete amenity. ID not specified.", 400);
        }
    }
}