<?php

use PHPUnit\Framework\TestCase; // This line is already present
use App\Models\Listing;
use App\Config\DatabaseConnection;

require_once 'bootstrap.php';

final class ListingTest extends TestCase {
    private static $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = DatabaseConnection::getInstance()->getConnection();
    }

    public function testSearchReturnsInsertedListing() {
        Listing::create([
            'title' => 'Test Apt',
            'city' => 'TestCity',
            'neighborhood_id' => 1,
            'htype_id' => 2, // Studio
            'style_id' => 1, // Modern
            'furnished' => 0,
            'rent_amount' => 10000,
            'deposit_amount' => 0,
            'status' => 'available',
            'verified' => 1
        ]);

        $out = Listing::search(['city' => 'TestCity']);
        $this->assertIsArray($out);
        $this->assertArrayHasKey('data', $out);
        $this->assertCount(1, $out['data']);
        $this->assertEquals('Test Apt', $out['data'][0]['title']);
    }

    public function testAiQueryAndStyleFilter() {
        Listing::create([
            'title' => 'Vintage Bungalow',
            'city' => 'Nairobi',
            'neighborhood_id' => 1,
            'htype_id' => 1, // Apartment
            'style_id' => 2, // Vintage
            'furnished' => 0,
            'rent_amount' => 14000,
            'deposit_amount' => 0,
            'status' => 'available',
            'verified' => 1
        ]);

        // ai_query should match 'vintage' and 'Nairobi'
        $out = Listing::search(['ai_query' => 'vintage Nairobi', 'style' => 'vintage']);
        $this->assertIsArray($out);
        $this->assertArrayHasKey('data', $out);
        $this->assertGreaterThanOrEqual(1, count($out['data']));
        $this->assertEquals('Vintage Bungalow', $out['data'][0]['title']);
    }

    // public function testParserExtractsFilters() {
    //     require_once __DIR__ . '/../src/helpers/ai_search.php';
    //     $parsed = parse_ai_query('vintage house in nairobi under 15000');
    //     $this->assertEquals('vintage', $parsed['style']);
    //     $this->assertEquals(15000, $parsed['maxRent']);
    //     $this->assertEquals('Nairobi', $parsed['city']);
    // }
}
