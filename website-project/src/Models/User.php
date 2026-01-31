<?php
namespace App\Models;

use App\Models\BaseModel; // Add use statement for BaseModel

class User extends BaseModel {
    protected static $tableName = 'users';
    protected static $primaryKey = 'id';
    protected static $fillable = ['name', 'email', 'password', 'role', 'has_paid', 'created_at', 'updated_at']; // Define fillable fields for mass assignment

    public static function all() {
        return parent::all();
    }

    public static function countAll() {
        return parent::countAll();
    }
}