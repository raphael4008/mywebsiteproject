<?php
// A script to clean up artifacts from a failed migration on the listings table.

require_once __DIR__ . '/../vendor/autoload.php';

try {
    echo "Connecting to the database...\n";
    $db = \App\Config\DatabaseConnection::getInstance()->getConnection();
    
    echo "Dropping columns 'htype_id' and 'style_id' from the 'listings' table if they exist...\n";
    // This is to recover from a partially failed run of the ...add_foreign_keys_to_listings_table migration
    $db->exec("ALTER TABLE `listings` DROP COLUMN IF EXISTS `htype_id`;");
    $db->exec("ALTER TABLE `listings` DROP COLUMN IF EXISTS `style_id`;");
    
    echo "Cleanup successful.\n";
    echo "You can now safely re-run the migration script.\n";

} catch (Exception $e) {
    die("An error occurred during cleanup: " . $e->getMessage() . "\n");
}