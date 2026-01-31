<?php
namespace App\Controllers;

use App\Models\Review;
use App\Helpers\Request;
use App\Helpers\JwtMiddleware;

class ReviewController extends BaseController {

    public function __construct() {
        // The BaseController can handle PDO connection if needed,
        // but models handle their own connections now.
    }

    /**
     * Get reviews for a specific listing.
     */
    public function getReviews() {
        $listingId = Request::get('listing_id');

        if (empty($listingId)) {
            $this->jsonErrorResponse('listing_id is required', 400);
            return;
        }

        try {
            // This method needs to be added to the Review model
            $reviews = Review::findByListingId($listingId);
            $this.jsonResponse($reviews);
        } catch (\Exception $e) {
            $this->jsonErrorResponse('Error fetching reviews: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new review for a listing.
     */
    public function create() {
        $data = Request::all();
        $user = JwtMiddleware::authorize();

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->jsonErrorResponse('Validation Failed', 400, $errors);
            return;
        }

        $reviewData = [
            'listing_id' => $data['listing_id'],
            'reviewer_id' => $user['id'],
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ];

        try {
            $reviewId = Review::create($reviewData);
            if ($reviewId) {
                $this->jsonResponse(['message' => 'Review created successfully', 'id' => $reviewId], 201);
            } else {
                $this->jsonErrorResponse('Failed to create review', 500);
            }
        } catch (\Exception $e) {
            $this->jsonErrorResponse('Error creating review: ' . $e->getMessage(), 500);
        }
    }

    private function validate(array $data): array {
        $errors = [];
        if (empty($data['listing_id'])) {
            $errors['listing_id'] = 'Listing ID is required';
        }
        if (empty($data['rating']) || !is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            $errors['rating'] = 'A rating between 1 and 5 is required';
        }
        if (empty($data['comment'])) {
            $errors['comment'] = 'A comment is required';
        }
        return $errors;
    }
}