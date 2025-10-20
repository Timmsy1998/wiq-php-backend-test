<?php
namespace Wiq\Transport;

class CurlTransport implements TransportInterface
{
    /** Thin cURL wrapper so I can swap transports without touching callers. */
    public function request(
        string $method,
        string $url,
        array $headers = [],
        $body = null,
    ): array {
        $ch = curl_init();

        // I’m fine passing full URLs here; callers decide base paths.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Flatten headers to "Key: Value"
        if (!empty($headers)) {
            $flat = [];
            foreach ($headers as $k => $v) {
                $flat[] = $k . ": " . $v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $flat);
        }

        // Body handling: arrays become form-encoded by default (matches token endpoint),
        // strings are sent as-is (JSON etc).
        if ($body !== null) {
            if (is_array($body)) {
                $body = http_build_query($body);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        // Sensible timeouts for a small library
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $respBody = curl_exec($ch);
        if ($respBody === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("HTTP request failed: " . $err);
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [
            "status" => $httpCode,
            "headers" => [], // if/when I need headers, I’ll flip to HEADERFUNCTION
            "body" => (string) $respBody,
        ];
    }
}
