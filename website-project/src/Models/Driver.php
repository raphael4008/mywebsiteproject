<?php
namespace App\Models;

use App\Models\BaseModel;

class Driver extends BaseModel {
    protected static $tableName = 'drivers';
    protected static $primaryKey = 'id';
    protected static $fillable = ['name', 'vehicle', 'rating', 'image'];
}
