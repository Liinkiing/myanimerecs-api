<?php


namespace App\Client;


use App\Model\Anime;
use App\Model\Recommendation;
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
                $responses[] = array_map(function (ResponseInterface $response) {
                    return  $response->toArray();
                }, $requests);
                usleep(2000000);
            }
        }

        return $responses;
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
