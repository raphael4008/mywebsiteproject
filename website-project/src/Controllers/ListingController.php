<?php
namespace App\Controllers;

use App\Helpers\Request;
use App\Config\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use App\Controllers\BaseController;
use App\Models\Listing;
use App\Models\Image;
use App\Models\Amenity;
use App\Services\AISearchService;
use OpenAI;

class ListingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function search()
    {
        try {
            $params = Request::queryParams();
            $result = Listing::search($params);

            if (empty($result['data'])) {
                $this->jsonErrorResponse('No listings found', 404);
                return;
            }

            $this->jsonResponse(['data' => $result['data'], 'total' => $result['total']]);
        }
        catch (\Exception $e) {
            // Log the real error
            error_log($e->getMessage());
            // Return a generic error to the user
            $this->jsonErrorResponse('An error occurred while searching for listings.', 500);
        }
    }

    public function getAll()
    {
        $listings = Listing::all();
        $this->jsonResponse($listings);
    }

    public function getCities()
    {
        $stmt = $this->pdo->query("SELECT DISTINCT city FROM listings ORDER BY city ASC");
        $cities = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $this->jsonResponse($cities);
    }

    public function getById($id)
    {
        $listing = Listing::findByIdWithDetails($id);

        if (!$listing) {
            $this->jsonErrorResponse('Listing not found', 404);
            return;
        }

        $this->jsonResponse($listing);
    }

    public function create()
    {
        $data = Request::all();
        $user = \App\Helpers\JwtMiddleware::authorize();

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->jsonErrorResponse('Validation Failed', 400, $errors);
            return;
        }

        $data['owner_id'] = $user['id'];
        $data['status'] = 'AVAILABLE';

        $this->convertNamesToIds($data);

        try {
            $listingId = Listing::create($data);
            if (!$listingId) {
                throw new \Exception("Failed to create listing.");
            }

            if (!empty($_FILES)) {
                $uploadedImagePaths = $this->handleImageUploads($listingId, $_FILES);
                foreach ($uploadedImagePaths as $path) {
                    // Use 'path' field name; Image::create adapts to the DB schema (path or image_path)
                    Image::create(['listing_id' => $listingId, 'path' => $path]);
                }
            }

            $this->jsonResponse(['id' => $listingId], 201);
        }
        catch (\Exception $e) {
            $this->jsonErrorResponse('An error occurred while creating the listing: ' . $e->getMessage(), 500);
        }
    }

    public function update($id)
    {
        $data = Request::all();
        $user = \App\Helpers\JwtMiddleware::authorize();

        $listing = Listing::find($id);

        if (!$listing) {
            $this->jsonErrorResponse('Listing not found', 404);
            return;
        }

        if ($listing['owner_id'] != $user['id']) {
            $this->jsonErrorResponse('You are not authorized to update this listing', 403);
            return;
        }

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->jsonErrorResponse('Validation Failed', 400, $errors);
            return;
        }

        $this->convertNamesToIds($data);

        try {
            Listing::update($id, $data);

            if (!empty($_FILES)) {
                $uploadedImagePaths = $this->handleImageUploads($id, $_FILES);
                foreach ($uploadedImagePaths as $path) {
                    Image::create(['listing_id' => $id, 'path' => $path]);
                }
            }

            $this->jsonResponse(['success' => true]);
        }
        catch (\Exception $e) {
            $this->jsonErrorResponse('An error occurred while updating the listing.', 500);
        }
    }

    public function delete($id)
    {
        $user = \App\Helpers\JwtMiddleware::authorize();

        $listing = Listing::find($id);

        if (!$listing) {
            $this->jsonErrorResponse('Listing not found', 404);
            return;
        }

        if ($listing['owner_id'] != $user['id']) {
            $this->jsonErrorResponse('You are not authorized to delete this listing', 403);
            return;
        }

        if (Listing::delete($id)) {
            $this->jsonResponse(['success' => true]);
        }
        else {
            $this->jsonErrorResponse('An error occurred while deleting the listing.', 500);
        }
    }

    public function getFeatured()
    {
        try {
            $listings = Listing::getFeatured(3); // Get 3 featured listings
            $this->jsonResponse(['data' => $listings]);
        }
        catch (\Exception $e) {
            error_log($e->getMessage());
            $this->jsonErrorResponse('An error occurred while fetching featured listings.', 500);
        }
    }

    public function aiSearch()
    {
        try {
            $userQuery = Request::input('query', '');

            if (empty($userQuery)) {
                $this->jsonErrorResponse('Query is empty.', 400);
                return;
            }

            // Get context for the AI prompt
            $cityStmt = $this->pdo->query("SELECT DISTINCT city FROM listings ORDER BY city ASC");
            $neighborhoodStmt = $this->pdo->query("SELECT DISTINCT neighborhood FROM listings ORDER BY neighborhood ASC");
            $htypeStmt = $this->pdo->query("SELECT name FROM house_types ORDER BY name ASC");
            $styleStmt = $this->pdo->query("SELECT name FROM styles ORDER BY name ASC");
            $amenities = Amenity::all();

            $context = [
                'cities' => $cityStmt->fetchAll(\PDO::FETCH_COLUMN),
                'neighborhoods' => $neighborhoodStmt->fetchAll(\PDO::FETCH_COLUMN),
                'htypes' => $htypeStmt->fetchAll(\PDO::FETCH_COLUMN),
                'styles' => $styleStmt->fetchAll(\PDO::FETCH_COLUMN),
                'amenities' => array_column($amenities, 'name'),
            ];

            $aiService = new AISearchService();
            $searchParams = $aiService->getParamsFromQuery($userQuery, $context);

            // Use the extracted params to search for listings
            $result = Listing::search($searchParams);

            if (empty($result['data'])) {
                $this->jsonErrorResponse('No listings found matching your AI search.', 404);
                return;
            }

            $this->jsonResponse(['data' => $result['data'], 'total' => $result['total']]);

        }
        catch (\Exception $e) {
            error_log('AI Search Error: ' . $e->getMessage());
            $this->jsonErrorResponse($e->getMessage(), 500);
        }
    }

    private function validate($data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Description is required';
        }

        if (empty($data['city'])) {
            $errors['city'] = 'City is required';
        }

        $htypeStmt = $this->pdo->query("SELECT name FROM house_types");
        $allowedHtypes = $htypeStmt->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($data['htype'])) {
            $errors['htype'] = 'House type is required';
        }
        elseif (!in_array(strtoupper($data['htype']), array_map('strtoupper', $allowedHtypes))) {
            $errors['htype'] = 'Invalid house type. Allowed types are: ' . implode(', ', $allowedHtypes);
        }

        $styleStmt = $this->pdo->query("SELECT name FROM styles");
        $allowedStyles = $styleStmt->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($data['style'])) {
            $errors['style'] = 'Style is required';
        }
        elseif (!in_array(strtoupper($data['style']), array_map('strtoupper', $allowedStyles))) {
            $errors['style'] = 'Invalid style. Allowed styles are: ' . implode(', ', $allowedStyles);
        }

        if (empty($data['rent_amount'])) {
            $errors['rent_amount'] = 'Rent amount is required';
        }
        elseif (!is_numeric($data['rent_amount']) || $data['rent_amount'] <= 0) {
            $errors['rent_amount'] = 'Rent amount must be a positive number';
        }

        if (empty($data['deposit_amount'])) {
            $errors['deposit_amount'] = 'Deposit amount is required';
        }
        elseif (!is_numeric($data['deposit_amount']) || $data['deposit_amount'] <= 0) {
            $errors['deposit_amount'] = 'Deposit amount must be a positive number';
        }

        return $errors;
    }

    private function handleImageUploads(int $listingId, array $files): array
    {
        $uploadedPaths = [];
        $uploadDir = Config::get('FILESYSTEM_ROOT');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $adapter = new LocalFilesystemAdapter($uploadDir);
        $filesystem = new Filesystem($adapter);

        if (empty($files['images']['name'][0])) {
            return [];
        }

        foreach ($files['images']['name'] as $key => $name) {
            if ($files['images']['error'][$key] === UPLOAD_ERR_OK) {
                $tmp_name = $files['images']['tmp_name'][$key];
                $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array(strtolower($file_extension), $allowedExtensions)) {
                    error_log("Invalid file type uploaded: " . $name);
                    continue;
                }

                $newFileName = uniqid('listing_' . $listingId . '_') . '.' . $file_extension;

                $stream = fopen($tmp_name, 'r+');
                $filesystem->writeStream($newFileName, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $uploadedPaths[] = 'images/' . $newFileName;
            }
            else {
                error_log("File upload error for " . $name . ": " . $files['images']['error'][$key]);
            }
        }
        return $uploadedPaths;
    }

    private function convertNamesToIds(array &$data)
    {
        if (isset($data['htype'])) {
            $htypeStmt = $this->pdo->prepare("SELECT id FROM house_types WHERE name = ?");
            $htypeStmt->execute([strtoupper($data['htype'])]);
            $htypeId = $htypeStmt->fetchColumn();
            if ($htypeId) {
                $data['htype_id'] = $htypeId;
            }
            unset($data['htype']);
        }

        if (isset($data['style'])) {
            $styleStmt = $this->pdo->prepare("SELECT id FROM styles WHERE name = ?");
            $styleStmt->execute([strtoupper($data['style'])]);
            $styleId = $styleStmt->fetchColumn();
            if ($styleId) {
                $data['style_id'] = $styleId;
            }
            unset($data['style']);
        }
    }
}