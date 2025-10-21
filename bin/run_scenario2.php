#!/usr/bin/env php
<?php
require __DIR__ . "/../vendor/autoload.php";

use Wiq\ApiClient;
use Wiq\Transport\FixtureTransport;
use Wiq\Model\Product;

/**
 * Scenario 2:
 * Fix the misspelled product name "Chpis" -> "Chips"
 * for product ID 84 in menu 7.
 */

$baseUrl = getenv("API_BASE_URL") ?: "http://great-food.test";
$clientId = getenv("API_CLIENT_ID") ?: "1337";
$clientSecret = getenv("API_CLIENT_SECRET") ?: "4j3g4gj304gj3";
$grantType = getenv("API_GRANT_TYPE") ?: "client_credentials";

$transport = new FixtureTransport(__DIR__ . "/../responses");
$api = new ApiClient(
    $transport,
    $baseUrl,
    $clientId,
    $clientSecret,
    $grantType,
);

$menuId = 7;
$productId = 84;

// Fetch current products for proof-before
$before = $api->getProductsByMenuId($menuId);
$current = null;
foreach ($before as $p) {
    if ($p->id === $productId) {
        $current = $p;
        break;
    }
}

echo "Before: Product #{$productId} name = " .
    ($current?->name ?? "N/A") .
    PHP_EOL;

// Update name
$updated = new Product($productId, "Chips", $current?->price);
$ok = $api->updateProduct($menuId, $updated);

if (!$ok) {
    fwrite(STDERR, "Update failed\n");
    exit(1);
}

// Fetch again to confirm
$after = $api->getProductsByMenuId($menuId);
$fixed = null;
foreach ($after as $p) {
    if ($p->id === $productId) {
        $fixed = $p;
        break;
    }
}

echo "After:  Product #{$productId} name = " .
    ($fixed?->name ?? "N/A") .
    PHP_EOL;
echo "PUT /menu/{$menuId}/product/{$productId} => " .
    ($ok ? "200 OK (simulated)" : "FAILED") .
    PHP_EOL;

