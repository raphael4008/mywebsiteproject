<?php
use PHPUnit\Framework\TestCase;

final class ListingTest extends TestCase {
    public function testSearchReturnsInsertedListing() {
        $pdo = Database::getInstance();
        // Insert a test listing
        $pdo->exec("INSERT INTO listings (id, title, city, neighborhood, htype, furnished, rent_amount, deposit_amount, amenities, images, verified, status, style) VALUES ('1','Test Apt','TestCity','TestNbh','STUDIO',0,10000,0,'[]','[]',1,'AVAILABLE','modern')");

        $out = \App\Models\Listing::search(['city' => 'TestCity']);
        $this->assertIsArray($out);
        $this->assertArrayHasKey('data', $out);
        $this->assertCount(1, $out['data']);
        $this->assertEquals('Test Apt', $out['data'][0]['title']);
    }

    public function testAiQueryAndStyleFilter() {
        $pdo = Database::getInstance();
        $pdo->exec("INSERT INTO listings (id, title, city, neighborhood, htype, furnished, rent_amount, deposit_amount, amenities, images, verified, status, style) VALUES ('2','Vintage Bungalow','Nairobi','Langata','ONE_BEDROOM',0,14000,0,'[]','[]',1,'AVAILABLE','vintage')");

        // ai_query should match 'vintage' and 'Langata'
        $out = \App\Models\Listing::search(['ai_query' => 'vintage Langata', 'style' => 'vintage']);
        $this->assertIsArray($out);
        $this->assertArrayHasKey('data', $out);
        $this->assertGreaterThanOrEqual(1, count($out['data']));
        $this->assertEquals('Vintage Bungalow', $out['data'][0]['title']);
    }

    public function testParserExtractsFilters() {
        require_once __DIR__ . '/../src/helpers/ai_search.php';
        $parsed = parse_ai_query('vintage house in nairobi under 15000');
        $this->assertEquals('vintage', $parsed['style']);
        $this->assertEquals(15000, $parsed['maxRent']);
        $this->assertEquals('Nairobi', $parsed['city']);
    }
}
