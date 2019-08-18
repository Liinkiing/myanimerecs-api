<?php


namespace App\Client;


use App\Enum\AnimeListStatus;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class JikanClient
{

    public const BASE_URL = 'https://api.jikan.moe/v3/';

    private $client;
    private $requestWaitTime;

    public function __construct(HttpClientInterface $client, int $requestWaitTime = 0)
    {
        $this->client = $client;
        $this->requestWaitTime = $requestWaitTime;
    }

    public function genre(int $malId): array
    {
        $response = $this->makeRequest("genre/anime/$malId");
        return $response->toArray()['mal_url'];
    }

    public function userAnimelist(string $username, string $status = ""): array
    {
        $query = [
            'order_by' => 'score',
            'sort' => 'desc'
        ];

        $response = $this->makeRequest("user/$username/animelist/$status", $query);
        return $response->toArray()['anime'];
    }


    public function anime(int $malId): array
    {
        $response = $this->makeRequest("anime/$malId");

        return $response->toArray();
    }

    public function animeRecommendations(int $malId): array {
        $response = $this->makeRequest("anime/$malId/recommendations");

        return $response->toArray()['recommendations'];
    }

    public function batchAnimeRecommendations(array $ids, int $tolerance): array
    {
        $requests = [];
        $responses = [];
        foreach ($ids as $index => $id) {
            $requests[] = $this->makeRequest("anime/$id/recommendations");
            if ($index % $tolerance === 0) {
                $responses[] = array_map(static function (ResponseInterface $response) {
                    return  $response->toArray();
                }, $requests);
                usleep(2000000);
            }
        }

        return $responses;
    }

    private function makeRequest(string $path, array $query = [], ?string $method = 'GET', ?array $headers = []): ResponseInterface
    {
        $headers = array_merge(
            $headers,
            [
                'User-Agent' => random_user_agent()
            ]
        );
        $options = array_merge(
            compact('headers'),
            compact('query'),
            [
                'buffer' => false,
                'extra' => [
                    'no_cache' => true
                ]
            ]
        );
        if ($this->requestWaitTime > 0) {
            usleep($this->requestWaitTime * 1000);
        }
        return $this->client->request($method, $this->endpoint($path), $options);
    }

    private function endpoint(string $path): string
    {
        return self::BASE_URL . $path;
    }

}
