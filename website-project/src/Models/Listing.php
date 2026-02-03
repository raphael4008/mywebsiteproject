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
        $sql = [];
        $bindings = [];

        if (!empty($params['status'])) { $sql[] = 'status = ?'; $bindings[] = $params['status']; }
        if (!empty($params['owner_id'])) { $sql[] = 'owner_id = ?'; $bindings[] = (int)$params['owner_id']; }
        if (!empty($params['city'])) { $sql[] = 'LOWER(city) = ?'; $bindings[] = strtolower($params['city']); }
        if (!empty($params['neighborhood'])) { $sql[] = 'LOWER(neighborhood) = ?'; $bindings[] = strtolower($params['neighborhood']); }
        if (!empty($params['minRent'])) { $sql[] = 'rent_amount >= ?'; $bindings[] = (int)$params['minRent']; }
        if (!empty($params['maxRent'])) { $sql[] = 'rent_amount <= ?'; $bindings[] = (int)$params['maxRent']; }
        if (!empty($params['htype'])) { $sql[] = 'htype_id = (SELECT id FROM house_types WHERE name = ?)'; $bindings[] = $params['htype']; }
        if (!empty($params['style'])) { $sql[] = 'style_id = (SELECT id FROM styles WHERE name = ?)'; $bindings[] = strtoupper($params['style']); }
        if (!empty($params['furnished'])) { $sql[] = 'furnished = ?'; $bindings[] = 1; }
        if (!empty($params['verified'])) { $sql[] = 'verified = ?'; $bindings[] = 1; }
        if (!empty($params['exclude_id'])) { $sql[] = 'id != ?'; $bindings[] = (int)$params['exclude_id']; }
        if (!empty($params['ai_query'])) {
            $sql[] = 'MATCH(title, description, city) AGAINST(? IN NATURAL LANGUAGE MODE)';
            $bindings[] = $params['ai_query'];
        }

        $whereClause = !empty($sql) ? 'WHERE ' . implode(' AND ', $sql) : '';

        $totalSql = "SELECT COUNT(*) FROM " . static::$tableName . " " . $whereClause;
        $countResult = self::rawQuery($totalSql, $bindings, true);
        $total = $countResult[0]['COUNT(*)'] ?? 0;

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
        
        $limit = !empty($params['limit']) ? (int)$params['limit'] : self::DEFAULT_LIMIT;
        $offset = !empty($params['offset']) ? (int)$params['offset'] : 0;
        
        $resultsSql = "
            SELECT
                l.id,
                l.title,
                l.rent_amount as price,
                l.city,
                ht.name as bedrooms
            FROM " . static::$tableName . " l
            LEFT JOIN house_types ht ON l.htype_id = ht.id
        " . $whereClause . $sort_sql . " LIMIT ? OFFSET ?";
        
        $bindings[] = $limit;
        $bindings[] = $offset;

        $results = self::rawQuery($resultsSql, $bindings, true);

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
            ORDER BY RAND()
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