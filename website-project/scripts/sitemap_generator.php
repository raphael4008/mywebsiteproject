<?php
// Sitemap Generator Script

require_once __DIR__ . '/../vendor/autoload.php';

// TODO: Adjust for production
$baseUrl = 'http://localhost/househunting/website-project/public'; 

try {
    $pdo = \App\Config\DatabaseConnection::getInstance()->getConnection();

    $sitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

    // 1. Static Pages
    $staticPages = [
        'index.php' => '1.0',
        'about.html' => '0.8',
        'contact.html' => '0.8',
        'listings.php' => '0.9',
        'features.html' => '0.7',
        'location-and-people.html' => '0.7',
        'agents.html' => '0.8',
        'login.html' => '0.5',
        'register.html' => '0.5',
        'compare.html' => '0.6',
        'privacy-policy.html' => '0.3',
        'terms.html' => '0.3'
    ];

    foreach ($staticPages as $page => $priority) {
        $url = $sitemap->addChild('url');
        $url->addChild('loc', $baseUrl . '/' . $page);
        $url->addChild('lastmod', date('Y-m-d', filemtime(__DIR__ . '/../public/' . $page)));
        $url->addChild('changefreq', 'weekly');
        $url->addChild('priority', $priority);
    }

    // 2. Dynamic Listings
    $stmt = $pdo->query("SELECT id, updated_at FROM listings WHERE status = 'available'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $url = $sitemap->addChild('url');
        $url->addChild('loc', $baseUrl . '/listing-details.php?id=' . $row['id']);
        $url->addChild('lastmod', date('Y-m-d', strtotime($row['updated_at'])));
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '1.0');
    }

    // 3. Dynamic Agents
    $stmt = $pdo->query("SELECT id FROM agents");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $url = $sitemap->addChild('url');
        $url->addChild('loc', $baseUrl . '/agent-profile.html?id=' . $row['id']);
        $url->addChild('changefreq', 'weekly');
        $url->addChild('priority', '0.6');
    }

    // Save to public folder
    $outputPath = __DIR__ . '/../public/sitemap.xml';
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = true;
    $dom->formatOutput = true;
    $dom->loadXML($sitemap->asXML());
    $dom->save($outputPath);

    echo "Sitemap generated successfully at: $outputPath\n";

} catch (Exception $e) {
    echo "Error generating sitemap: " . $e->getMessage() . "\n";
}
?>