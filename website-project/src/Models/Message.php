<?php
namespace App\Models;

class Message extends BaseModel {
    protected static $tableName = 'messages';
    protected static $fillable = ['name', 'email', 'message'];

    public static function create(array $data) {
        $data['name'] = htmlspecialchars(strip_tags((string)$data['name']));
        $data['email'] = htmlspecialchars(strip_tags((string)$data['email']));
        $data['message'] = htmlspecialchars(strip_tags((string)$data['message']));
        
        return parent::create($data);
    }
}