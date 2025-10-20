<?php
namespace Wiq;

use Wiq\Transport\TransportInterface;
use Wiq\Model\Menu;
use Wiq\Model\Product;

/**
 * Core API client for the fictitious Great Food Ltd REST API.
 * Handles auth, listing menus, products, and updating items.
 */
class ApiClient
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $grantType;
    private TransportInterface $http;
    private ?string $token = null;

    public function __construct(
        TransportInterface $http,
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $grantType = "client_credentials",
    ) {
        $this->http = $http;
        $this->baseUrl = rtrim($baseUrl, "/");
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->grantType = $grantType;
    }

    private function auth(): void
    {
        if ($this->token) {
            return;
        }

        $resp = $this->http->request(
            "POST",
            $this->baseUrl . "/auth_token",
            ["Content-Type" => "application/x-www-form-urlencoded"],
            [
                "client_secret" => $this->clientSecret,
                "client_id" => $this->clientId,
                "grant_type" => $this->grantType,
            ],
        );

        $data = json_decode($resp["body"], true);
        if (!isset($data["access_token"])) {
            throw new \RuntimeException("Auth failed or token missing");
        }
        $this->token = $data["access_token"];
    }

    /** @return Menu[] */
    public function getMenus(): array
    {
        $this->auth();

        $resp = $this->http->request("GET", $this->baseUrl . "/menus", [
            "Authorization" => "Bearer " . $this->token,
        ]);

        $data = json_decode($resp["body"], true);
        $out = [];
        foreach ($data["data"] ?? [] as $raw) {
            $out[] = new Menu((int) $raw["id"], (string) $raw["name"]);
        }
        return $out;
    }

    /** @return Product[] */
    public function getProductsByMenuId(int $menuId): array
    {
        $this->auth();

        $resp = $this->http->request(
            "GET",
            $this->baseUrl . "/menu/" . $menuId . "/products",
            ["Authorization" => "Bearer " . $this->token],
        );

        $data = json_decode($resp["body"], true);
        $out = [];
        foreach ($data["data"] ?? [] as $raw) {
            $out[] = new Product(
                (int) $raw["id"],
                (string) $raw["name"],
                isset($raw["price"]) ? (float) $raw["price"] : null,
            );
        }
        return $out;
    }

    public function updateProduct(int $menuId, Product $product): bool
    {
        $this->auth();

        $resp = $this->http->request(
            "PUT",
            $this->baseUrl . "/menu/" . $menuId . "/product/" . $product->id,
            [
                "Authorization" => "Bearer " . $this->token,
                "Content-Type" => "application/json",
            ],
            json_encode($product->toArray()),
        );

        return $resp["status"] >= 200 && $resp["status"] < 300;
    }
}
