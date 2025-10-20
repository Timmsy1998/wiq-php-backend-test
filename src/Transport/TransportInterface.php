<?php
namespace Wiq\Transport;

interface TransportInterface
{
    /**
     * Generic HTTP request abstraction.
     *
     * @param string $method  HTTP method (GET, POST, PUT, etc.)
     * @param string $url     Full URL or path
     * @param array<string,string> $headers
     * @param array<string,mixed>|string|null $body
     * @return array{status:int, headers:array<string,string>, body:string}
     */
    public function request(string $method, string $url, array $headers = [], $body = null): array;
}
