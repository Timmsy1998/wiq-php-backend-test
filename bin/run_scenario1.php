#!/usr/bin/env php
<?php
require __DIR__ . "/../vendor/autoload.php";

use Wiq\ApiClient;
use Wiq\Transport\FixtureTransport;

/**
 * Scenario 1:
 * Authenticate, grab the Takeaway menu,
 * fetch its product list, and print a simple table.
 */

$baseUrl = getenv("API_BASE_URL") ?: "http://great-food.test";
$clientId = getenv("API_CLIENT_ID") ?: "1337";
$clientSecret = getenv("API_CLIENT_SECRET") ?: "4j3g4gj304gj3";
$grantType = getenv("API_GRANT_TYPE") ?: "client_credentials";

// Using the fixture transport so it runs offline.
$transport = new FixtureTransport(__DIR__ . "/../responses");
$api = new ApiClient(
    $transport,
    $baseUrl,
    $clientId,
    $clientSecret,
    $grantType,
);

// Find the Takeaway menu
$menus = $api->getMenus();
$takeaway = null;
foreach ($menus as $menu) {
    if (strtolower($menu->name) === "takeaway") {
        $takeaway = $menu;
        break;
    }
}

if (!$takeaway) {
    fwrite(STDERR, "Takeaway menu not found\n");
    exit(1);
}

$products = $api->getProductsByMenuId($takeaway->id);

// Print the table
echo "| ID | Name    |\n";
echo "| -- | ------- |\n";
foreach ($products as $p) {
    printf("| %-2d | %-7s |\n", $p->id, $p->name);
}

