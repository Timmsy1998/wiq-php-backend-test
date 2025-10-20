<?php
namespace Wiq\Transport;

/**
 * FixtureTransport simulates API calls using local JSON files.
 * It lets me run the library completely offline.
 */
class FixtureTransport implements TransportInterface
{
    private string $responsesDir;
    private string $stateFile;

    public function __construct(string $responsesDir)
    {
        $this->responsesDir = rtrim($responsesDir, DIRECTORY_SEPARATOR);
        $this->stateFile =
            $this->responsesDir . DIRECTORY_SEPARATOR . "state.json";
    }

    public function request(
        string $method,
        string $url,
        array $headers = [],
        $body = null,
    ): array {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        // Simulate POST /auth_token
        if ($method === "POST" && $path === "/auth_token") {
            $body = file_get_contents($this->responsesDir . "/token.json");
            return ["status" => 200, "headers" => [], "body" => $body ?: "{}"];
        }

        // Simulate GET /menus
        if ($method === "GET" && $path === "/menus") {
            $body = file_get_contents($this->responsesDir . "/menus.json");
            return [
                "status" => 200,
                "headers" => [],
                "body" => $body ?: '{"data":[]}',
            ];
        }

        // Simulate GET /menu/{id}/products
        if (
            $method === "GET" &&
            preg_match('#^/menu/(\\d+)/products$#', $path)
        ) {
            $base = json_decode(
                (string) file_get_contents(
                    $this->responsesDir . "/menu-products.json",
                ),
                true,
            );
            $state = file_exists($this->stateFile)
                ? json_decode(
                    (string) file_get_contents($this->stateFile),
                    true,
                )
                : [];

            if (isset($state["products"])) {
                $byId = [];
                foreach ($base["data"] as $p) {
                    $byId[$p["id"]] = $p;
                }
                foreach ($state["products"] as $p) {
                    $byId[$p["id"]] = array_merge($byId[$p["id"]] ?? [], $p);
                }
                $base["data"] = array_values($byId);
            }
            return [
                "status" => 200,
                "headers" => [],
                "body" => json_encode($base),
            ];
        }

        // Simulate PUT /menu/{menu_id}/product/{product_id}
        if (
            $method === "PUT" &&
            preg_match('#^/menu/(\\d+)/product/(\\d+)$#', $path, $m)
        ) {
            $menuId = (int) $m[1];
            $productId = (int) $m[2];
            $payload = is_string($body)
                ? json_decode($body, true)
                : (array) $body;

            $state = file_exists($this->stateFile)
                ? json_decode(
                    (string) file_get_contents($this->stateFile),
                    true,
                )
                : [];

            $state["products"] = $state["products"] ?? [];
            $found = false;
            foreach ($state["products"] as &$p) {
                if ((int) $p["id"] === $productId) {
                    $p = array_merge($p, $payload);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $state["products"][] = array_merge(
                    ["id" => $productId],
                    $payload,
                );
            }

            file_put_contents(
                $this->stateFile,
                json_encode($state, JSON_PRETTY_PRINT),
            );

            return [
                "status" => 200,
                "headers" => [],
                "body" => json_encode([
                    "success" => true,
                    "menu_id" => $menuId,
                    "product" => $payload,
                ]),
            ];
        }

        // Default 404
        return [
            "status" => 404,
            "headers" => [],
            "body" => json_encode(["error" => "Not Found"]),
        ];
    }
}
