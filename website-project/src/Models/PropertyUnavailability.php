<?php
namespace App\Models;

use App\Models\BaseModel;
use PDO;

class PropertyUnavailability extends BaseModel {
    protected static $tableName = 'property_unavailability';
    protected static $primaryKey = 'id';
    protected static $fillable = ['listing_id', 'start_date', 'end_date'];

    public static function findByListing($listingId) {
        return self::where(['listing_id' => $listingId]);
    }
}