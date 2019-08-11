<?php


namespace App\Client;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class JikanClient
{

    public const BASE_URL = 'https://api.jikan.moe/v3/';

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function userAnimelist(string $username): array
    {
        $query = [
            'order_by' => 'score',
            'sort' => 'desc'
        ];

        $response = $this->makeRequest("user/$username/animelist", $query);
        return $response->toArray()['anime'];
    }

    private function makeRequest(string $path, array $query = [], ?string $method = 'GET', ?array $headers = []): ResponseInterface
    {
        $options = array_merge(
            $headers,
            compact('query')
        );

        return $this->client->request($method, $this->endpoint($path), $options);
    }

    private function endpoint(string $path): string
    {
        return self::BASE_URL . $path;
    }

}
