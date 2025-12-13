<?php
namespace App\Controllers;

use App\Models\Listing as ListingModel;
use App\Models\ListingData;
use App\Models\Image;
use App\Helpers\Request;
use App\Config\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use App\Controllers\BaseController;

use R;
use Dotenv\Dotenv;
use OpenAI;

class ListingController extends BaseController {
    public function search() {
        $params = Request::queryParams();
        
        $conditions = ['status = ?'];
        $bindings = ['AVAILABLE'];

        // Apply filters
        if (!empty($params['city'])) {
            $conditions[] = 'LOWER(city) = ?';
            $bindings[] = mb_strtolower($params['city']);
        }
        if (!empty($params['neighborhood'])) {
            $conditions[] = 'LOWER(neighborhood) = ?';
            $bindings[] = mb_strtolower($params['neighborhood']);
        }
        if (!empty($params['minRent'])) {
            $conditions[] = 'rent_amount >= ?';
            $bindings[] = (int)$params['minRent'];
        }
        if (!empty($params['maxRent'])) {
            $conditions[] = 'rent_amount <= ?';
            $bindings[] = (int)$params['maxRent'];
        }
        if (!empty($params['htype'])) {
            $conditions[] = 'htype = ?';
            $bindings[] = $params['htype'];
        }
        if (!empty($params['style'])) {
            $conditions[] = 'LOWER(style) = ?';
            $bindings[] = mb_strtolower($params['style']);
        }
        if (!empty($params['furnished'])) {
            $conditions[] = 'furnished = ?';
            $bindings[] = 1;
        }
        if (!empty($params['verified'])) {
            $conditions[] = 'verified = ?';
            $bindings[] = 1;
        }
        if (!empty($params['amenities']) && is_array($params['amenities'])) {
            $amenitiesCount = count($params['amenities']);
            $inQuery = implode(',', array_fill(0, $amenitiesCount, '?'));
            $conditions[] = 'id IN (SELECT listing_id FROM listing_amenities WHERE amenity_id IN ('.$inQuery.') GROUP BY listing_id HAVING COUNT(DISTINCT amenity_id) = ?)';
            $bindings = array_merge($bindings, $params['amenities'], [$amenitiesCount]);
        }
        if (!empty($params['ai_query'])) {
            $conditions[] = 'MATCH(title, description, city) AGAINST(? IN NATURAL LANGUAGE MODE)';
            $bindings[] = $params['ai_query'];
        }

        $whereClause = implode(' AND ', $conditions);
        
        // Apply sorting
        $sort_sql = ' ORDER BY verified DESC, status ASC, rent_amount ASC';
        if (!empty($params['sort'])) {
            switch ($params['sort']) {
                case 'price_asc':
                    $sort_sql = ' ORDER BY rent_amount ASC';
                    break;
                case 'price_desc':
                    $sort_sql = ' ORDER BY rent_amount DESC';
                    break;
                case 'newest':
                    $sort_sql = ' ORDER BY created_at DESC';
                    break;
            }
        }
        
$total = R::count('listings', $whereClause, $bindings);

        // Apply limit and offset
        $limit = !empty($params['limit']) ? (int)$params['limit'] : 10;
        $offset = !empty($params['offset']) ? (int)$params['offset'] : 0;
        $beans = R::find('listings', $whereClause . $sort_sql . ' LIMIT ? OFFSET ?', array_merge($bindings, [$limit, $offset]));

        $results = R::exportAll($beans);
        
        if (!empty($results)) {
            $listingIds = array_map(function($listing) { return $listing['id']; }, $results);
            
            $images = R::find('images', 'listing_id IN ('.R::genSlots($listingIds).')', $listingIds);
            
            $imagesByListingId = [];
            foreach ($images as $image) {
                $imagesByListingId[$image->listing_id][] = $image->export();
            }
            
            foreach ($results as &$listing) {
                $listing['images'] = isset($imagesByListingId[$listing['id']]) ? $imagesByListingId[$listing['id']] : [];
            }
        }
        
        $this->jsonResponse(['data' => $results, 'total' => $total]);
    }

    public function getCities() {
        $cities = R::getCol("SELECT DISTINCT city FROM listings ORDER BY city ASC");
        $this->jsonResponse($cities);
    }

    public function getById($id) {
        $listing = R::load('listings', $id);
        if (!$listing->id) {
            $this->jsonResponse(['error' => 'Listing not found'], 404);
            return;
        }
        $images = R::related($listing, 'images');
        $listing = $listing->export();
        $listing['images'] = R::exportAll($images);
        $this->jsonResponse($listing);
    }

    public function create($data, $user) {
        R::begin();
        try {
            $listing = R::dispense('listings');
            $listing->owner_id = $user['data']['id'];
            $listing->title = $data['title'];
            $listing->description = $data['description'];
            $listing->city = $data['city'];
            $listing->htype = $data['htype'];
            $listing->style = $data['style'];
            $listing->rent_amount = $data['rent_amount'];
            $listing->deposit_amount = $data['deposit_amount'];
            $listing->status = 'AVAILABLE';

            $errors = $this->validate($listing);
            if (!empty($errors)) {
                $this->jsonResponse(['errors' => $errors], 400);
                return;
            }

            $id = R::store($listing);

            // Handle image uploads
            if (!empty($_FILES)) {
                $uploadedImagePaths = $this->handleImageUploads($id, $_FILES);
                foreach ($uploadedImagePaths as $path) {
                    $image = R::dispense('images');
                    $image->listing_id = $id;
                    $image->path = $path;
                    R::store($image);
                }
            }

            R::commit();
            $this->jsonResponse(['id' => $id], 201);
        } catch (\Exception $e) {
            R::rollback();
            $this->jsonResponse(['error' => 'An error occurred while creating the listing.'], 500);
        }
    }

    public function update($id, $data, $user) {
        R::begin();
        try {
            $listing = R::load('listings', $id);
            if (!$listing->id) {
                $this->jsonResponse(['error' => 'Listing not found'], 404);
                return;
            }

            if ($listing->owner_id != $user['data']['id']) {
                $this->jsonResponse(['error' => 'You are not authorized to update this listing'], 403);
                return;
            }

            $listing->title = $data['title'];
            $listing->description = $data['description'];
            $listing->city = $data['city'];
            $listing->htype = $data['htype'];
            $listing->style = $data['style'];
            $listing->rent_amount = $data['rent_amount'];
            $listing->deposit_amount = $data['deposit_amount'];

            $errors = $this->validate($listing);
            if (!empty($errors)) {
                $this->jsonResponse(['errors' => $errors], 400);
                return;
            }

            R::store($listing);

            // Handle image uploads
            if (!empty($_FILES)) {
                $uploadedImagePaths = $this->handleImageUploads($id, $_FILES);
                foreach ($uploadedImagePaths as $path) {
                    $image = R::dispense('images');
                    $image->listing_id = $id;
                    $image->path = $path;
                    R::store($image);
                }
            }

            R::commit();
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            R::rollback();
            $this->jsonResponse(['error' => 'An error occurred while updating the listing.'], 500);
        }
    }

    public function delete($id, $user) {
        R::begin();
        try {
            $listing = R::load('listings', $id);
            if (!$listing->id) {
                $this->jsonResponse(['error' => 'Listing not found'], 404);
                return;
            }

            if ($listing->owner_id != $user['data']['id']) {
                $this->jsonResponse(['error' => 'You are not authorized to delete this listing'], 403);
                return;
            }

            $images = R::related($listing, 'images');
            R::trashAll($images);
            R::trash($listing);

            R::commit();
            $this.jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            R::rollback();
            $this->jsonResponse(['error' => 'An error occurred while deleting the listing.'], 500);
        }
    }

    public function aiSearch() {
        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            $apiKey = $_ENV['OPENAI_API_KEY'];
            if (empty($apiKey)) {
                $this->jsonResponse(['error' => 'OpenAI API key is not configured.'], 500);
                return;
            }

            $client = OpenAI::client($apiKey);
            $data = Request::json();
            $userQuery = $data['query'] ?? '';

            if (empty($userQuery)) {
                $this->jsonResponse(['error' => 'Query is empty.'], 400);
                return;
            }

            $cities = R::getCol("SELECT DISTINCT city FROM listings ORDER BY city ASC");
            $neighborhoods = R::getCol("SELECT DISTINCT neighborhood FROM listings ORDER BY neighborhood ASC");

            $systemPrompt = <<<PROMPT
You are a highly intelligent assistant for a real estate website. Your task is to extract search parameters from a user's natural language query.
The user will provide a query, and you must return a JSON object with the extracted parameters.
The possible parameters are:
- "city": string (must be one of: {implode(', ', $cities)})
- "neighborhood": string (must be one of: {implode(', ', $neighborhoods)})
- "minRent": integer
- "maxRent": integer
- "htype": string (must be one of: SINGLE, BEDSITTER, STUDIO, ONE_BEDROOM, TWO_BEDROOM)
- "style": string (must be one of: modern, vintage)
- "furnished": boolean
- "verified": boolean
- "amenities": array of strings

Analyze the user's query and construct a valid JSON object. If a parameter is not mentioned, do not include it in the JSON object.
For example, if the user query is "a furnished 2 bedroom apartment in Kilimani with a gym for less than 50000", the JSON output should be:
{
    "htype": "TWO_BEDROOM",
    "neighborhood": "Kilimani",
    "furnished": true,
    "maxRent": 50000,
    "amenities": ["gym"]
}
Only output the JSON object, with no other text before or after it.
PROMPT;

            $response = $client->chat()->completions()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userQuery],
                ],
            ]);

            $jsonResponse = $response->choices[0]->message->content;
            $decodedJson = json_decode($jsonResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonResponse(['error' => 'Failed to parse AI response.', 'raw_response' => $jsonResponse], 500);
                return;
            }

            $this->jsonResponse($decodedJson);

        } catch (\Exception $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'An error occurred while communicating with the AI service.'], 500);
        }
    }

        private function validate($bean): array {
            $errors = [];
    
            if (empty($bean->title)) {
                $errors['title'] = 'Title is required';
            }
    
            if (empty($bean->description)) {
                $errors['description'] = 'Description is required';
            }
    
            if (empty($bean->city)) {
                $errors['city'] = 'City is required';
            }
    
            if (empty($bean->htype)) {
                $errors['htype'] = 'House type is required';
            }
    
            if (empty($bean->style)) {
                $errors['style'] = 'Style is required';
            }
    
            if (empty($bean->rent_amount)) {
                $errors['rent_amount'] = 'Rent amount is required';
            }
    
            if (empty($bean->deposit_amount)) {
                $errors['deposit_amount'] = 'Deposit amount is required';
            }
    
            return $errors;
        }

    

        /**

         * Handles the upload of image files for a given listing.

         *

         * @param int $listingId The ID of the listing to associate images with.

         * @param array $files The $_FILES array containing uploaded files.

         * @return array An array of uploaded image paths relative to the public directory, or empty array if no files or errors.

         */

        private function handleImageUploads(int $listingId, array $files): array
        {
            $uploadedPaths = [];
            $uploadDir = Config::getInstance()->get('FILESYSTEM_ROOT');
    
            $adapter = new Local($uploadDir);
            $filesystem = new Filesystem($adapter);
    
            if (empty($files['images']['name'][0])) {
                return []; // No files uploaded
            }
    
            foreach ($files['images']['name'] as $key => $name) {
                if ($files['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmp_name = $files['images']['tmp_name'][$key];
                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
                    if (!in_array(strtolower($file_extension), $allowedExtensions)) {
                        error_log("Invalid file type uploaded: " . $name);
                        continue; // Skip invalid file types
                    }
    
                    $newFileName = uniqid('listing_') . '.' . $file_extension;
    
                    $stream = fopen($tmp_name, 'r+');
                    $filesystem->writeStream($newFileName, $stream);
                    fclose($stream);
    
                    $uploadedPaths[] = $uploadDir . '/' . $newFileName;
                } else {
                    error_log("File upload error for " . $name . ": " . $files['images']['error'][$key]);
                }
            }
            return $uploadedPaths;
        }

    }