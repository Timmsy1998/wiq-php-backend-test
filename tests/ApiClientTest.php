<?php
namespace Wiq\Tests;

use PHPUnit\Framework\TestCase;
use Wiq\ApiClient;
use Wiq\Transport\FixtureTransport;
use Wiq\Model\Product;

/**
 * Basic smoke tests against the current fixtures.
 */
class ApiClientTest extends TestCase
{
    private ApiClient $api;

    protected function setUp(): void
    {
        $this->api = new ApiClient(
            new FixtureTransport(__DIR__ . "/../responses"),
            "http://great-food.test",
            "1337",
            "4j3g4gj304gj3",
        );
    }

    public function testListTakeawayProducts(): void
    {
        $menus = $this->api->getMenus();
        $takeaway = null;
        foreach ($menus as $m) {
            if (strtolower($m->name) === "takeaway") {
                $takeaway = $m;
                break;
            }
        }
        $this->assertNotNull($takeaway, "Takeaway menu must exist");

        $products = $this->api->getProductsByMenuId($takeaway->id);
        $names = array_map(fn($p) => $p->name, $products);

        // Assert against your new fixture set
        foreach (
            ["Large Pizza", "Medium Pizza", "Burger", "Chips", "Soup", "Salad"]
            as $expected
        ) {
            $this->assertContains(
                $expected,
                $names,
                "Missing expected product: {$expected}",
            );
        }
    }

    public function testFixMisspelledProduct(): void
    {
        $menuId = 7;
        $productId = 84;

        // Before: may not exist in the base fixture, that's fine.
        $before = $this->api->getProductsByMenuId($menuId);
        $current = null;
        foreach ($before as $p) {
            if ($p->id === $productId) {
                $current = $p;
                break;
            }
        }

        // Perform update to ensure the record exists and has the correct name.
        $ok = $this->api->updateProduct(
            $menuId,
            new Product($productId, "Chips", $current?->price),
        );
        $this->assertTrue($ok);

        // After: must exist and be 'Chips'
        $after = $this->api->getProductsByMenuId($menuId);
        $fixed = null;
        foreach ($after as $p) {
            if ($p->id === $productId) {
                $fixed = $p;
                break;
            }
        }
        $this->assertNotNull(
            $fixed,
            "Updated product should be present after PUT",
        );
        $this->assertEquals("Chips", $fixed->name);
    }
}
