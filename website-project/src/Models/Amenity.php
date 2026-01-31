<?php
namespace App\Models;

class Amenity extends BaseModel {
    protected static $tableName = 'amenities';
    protected static $primaryKey = 'id';
    protected static $fillable = ['name'];
}
