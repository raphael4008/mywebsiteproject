<?php
namespace App\Models;

use App\Models\BaseModel;
use PDO;

class Listing extends BaseModel {
    protected static $tableName = 'listings';
    protected static $primaryKey = 'id';
    protected static $fillable = ['owner_id', 'title', 'description', 'city', 'htype_id', 'style_id', 'rent_amount', 'deposit_amount', 'status', 'neighborhood', 'furnished', 'verified'];
    const DEFAULT_LIMIT = 10;

    public static function search($params) {
        if (!empty($params['ai_query'])) {
            $aiService = new \App\Services\AISearchService();
            $cacheFile = __DIR__ . '/../../cache/ai_search_context.json';
            $cacheLifetime = 86400; // 24 hours
    
            $context = null;
    
            if (file_exists($cacheFile) && (time() - file_get_contents($cacheFile)) < $cacheLifetime) {
                $context = json_decode(file_get_contents($cacheFile), true);
            }
            
            if (!$context) {
                $pdo = \App\Config\DatabaseConnection::getInstance()->getConnection();
                $cityStmt = $pdo->query("SELECT DISTINCT city FROM listings ORDER BY city ASC");
                $neighborhoodStmt = $pdo->query("SELECT DISTINCT neighborhood FROM listings ORDER BY neighborhood ASC");
                $htypeStmt = $pdo->query("SELECT name FROM house_types ORDER BY name ASC");
                $styleStmt = $pdo->query("SELECT name FROM styles ORDER BY name ASC");
                $amenities = Amenity::all();
    
                $context = [
                    'cities' => $cityStmt->fetchAll(\PDO::FETCH_COLUMN),
                    'neighborhoods' => $neighborhoodStmt->fetchAll(\PDO::FETCH_COLUMN),
                    'htypes' => $htypeStmt->fetchAll(\PDO::FETCH_COLUMN),
                    'styles' => $styleStmt->fetchAll(\PDO::FETCH_COLUMN),
                    'amenities' => array_column($amenities, 'name'),
                ];
                
                file_put_contents($cacheFile, json_encode($context));
            }
    
            $aiParams = $aiService->getParamsFromQuery($params['ai_query'], $context);
            unset($params['ai_query']);
            $params = array_merge($params, $aiParams);
        }
        $joins = [];
$sql = [];
$bindings = [];

if (!empty($params['status'])) { $sql[] = 'l.status = ?'; $bindings[] = $params['status']; }
if (!empty($params['owner_id'])) { $sql[] = 'l.owner_id = ?'; $bindings[] = (int)$params['owner_id']; }
if (!empty($params['city'])) { $sql[] = 'LOWER(l.city) = ?'; $bindings[] = strtolower($params['city']); }
if (!empty($params['neighborhood'])) { $sql[] = 'LOWER(l.neighborhood) = ?'; $bindings[] = strtolower($params['neighborhood']); }
if (!empty($params['minRent'])) { $sql[] = 'l.rent_amount >= ?'; $bindings[] = (int)$params['minRent']; }
if (!empty($params['maxRent'])) { $sql[] = 'l.rent_amount <= ?'; $bindings[] = (int)$params['maxRent']; }
if (!empty($params['htype'])) {
    $joins[] = 'LEFT JOIN house_types ht ON l.htype_id = ht.id';
    $sql[] = 'ht.name = ?'; 
    $bindings[] = $params['htype']; 
}
if (!empty($params['style'])) {
    $joins[] = 'LEFT JOIN styles s ON l.style_id = s.id';
    $sql[] = 's.name = ?'; 
    $bindings[] = strtoupper($params['style']); 
}
if (!empty($params['furnished'])) { $sql[] = 'l.furnished = ?'; $bindings[] = 1; }
if (!empty($params['verified'])) { $sql[] = 'l.verified = ?'; $bindings[] = 1; }
if (!empty($params['exclude_id'])) { $sql[] = 'l.id != ?'; $bindings[] = (int)$params['exclude_id']; }
if (!empty($params['ai_query'])) {
    $sql[] = 'MATCH(l.title, l.description, l.city) AGAINST(? IN NATURAL LANGUAGE MODE)';
    $bindings[] = $params['ai_query'];
}

$joinClause = !empty($joins) ? implode(' ', array_unique($joins)) : '';
$whereClause = !empty($sql) ? 'WHERE ' . implode(' AND ', $sql) : '';

$totalSql = "SELECT COUNT(*) FROM " . static::$tableName . " l " . $joinClause . " " . $whereClause;
$countResult = self::rawQuery($totalSql, $bindings, true);
$total = $countResult[0]['COUNT(*)'] ?? 0;

$sort_sql = ' ORDER BY l.verified DESC, l.status ASC, l.rent_amount ASC';
if (!empty($params['sort'])) {
    switch ($params['sort']) {
        case 'price_asc':
            $sort_sql = ' ORDER BY l.rent_amount ASC';
            break;
        case 'price_desc':
            $sort_sql = ' ORDER BY l.rent_amount DESC';
            break;
        case 'newest':
            $sort_sql = ' ORDER BY l.created_at DESC';
            break;
    }
}
        
$limit = !empty($params['limit']) ? (int)$params['limit'] : self::DEFAULT_LIMIT;
$offset = !empty($params['offset']) ? (int)$params['offset'] : 0;
        
$resultsSql = "
    SELECT
        l.id,
        l.title,
        l.rent_amount as price,
        l.city,
        ht.name as bedrooms,
        img.path as main_image
    FROM " . static::$tableName . " l
    LEFT JOIN house_types ht ON l.htype_id = ht.id
    LEFT JOIN images img ON l.id = img.listing_id AND img.is_main = 1
    " . $joinClause . "
" . $whereClause . $sort_sql . " LIMIT ? OFFSET ?";
        
$bindings[] = $limit;
$bindings[] = $offset;

$results = self::rawQuery($resultsSql, $bindings, true);

if (!empty($results)) {
    foreach ($results as &$listing) {
        $listing['images'] = $listing['main_image'] ? [['path' => $listing['main_image']]] : [];
        unset($listing['main_image']);
    }
}
        
return ['data' => $results, 'total' => $total];
    }

    public static function findByIdWithDetails($id) {
        $sql = "
            SELECT
                l.*,
                n.name as neighborhood_name, n.city as neighborhood_city,
                ht.name as htype_name,
                s.name as style_name
            FROM listings l
            LEFT JOIN neighborhoods n ON l.neighborhood_id = n.id
            LEFT JOIN house_types ht ON l.htype_id = ht.id
            LEFT JOIN styles s ON l.style_id = s.id
            WHERE l.id = ?
        ";

        $listing = self::rawQuery($sql, [$id], false);

        if (empty($listing)) {
            return false;
        }
        
        $listing['htype'] = $listing['htype_name'];
        $listing['style'] = $listing['style_name'];
        $listing['neighborhood'] = [
            'name' => $listing['neighborhood_name'],
            'city' => $listing['neighborhood_city'],
        ];

        unset(
            $listing['htype_name'], $listing['style_name'],
            $listing['neighborhood_name'], $listing['neighborhood_city']
        );

        $listing['images'] = Image::findByListingId($id);
        
        $amenitySql = "
            SELECT a.id, a.name, a.icon
            FROM amenities a
            INNER JOIN listing_amenities la ON a.id = la.amenity_id
            WHERE la.listing_id = ?
        ";
        $listing['amenities'] = self::rawQuery($amenitySql, [$id], true);

        // Attach videos if any (videos table)
        $videoSql = "SELECT id, url, title, created_at FROM videos WHERE listing_id = ? ORDER BY id ASC";
        $listing['videos'] = self::rawQuery($videoSql, [$id], true) ?? [];

        return $listing;
    }

    public static function getFeatured($limit = 6) {
        $results = self::rawQuery("
            SELECT
                l.id,
                l.title,
                l.rent_amount as price,
                l.city,
                ht.name as bedrooms
            FROM " . static::$tableName . " l
            LEFT JOIN house_types ht ON l.htype_id = ht.id
            WHERE l.is_featured = 1
            ORDER BY l.created_at DESC
            LIMIT ?", [$limit], true);

        if (!empty($results)) {
            $listingIds = array_column($results, 'id');
            // Use Image::findByListingIds which supports fetching images for multiple listings
            $images = Image::findByListingIds($listingIds);
            $imagesByListingId = [];
            foreach($images as $image) {
                $imagesByListingId[$image['listing_id']][] = $image;
            }
            foreach ($results as &$listing) {
                $listing['images'] = $imagesByListingId[$listing['id']] ?? [];
            }
        }
        
        return $results;
    }

    public static function countByStatus(string $status): int {
        $result = self::rawQuery("SELECT COUNT(*) FROM " . static::$tableName . " WHERE status = ?", [$status], true);
        return $result[0]['COUNT(*)'];
    }
}