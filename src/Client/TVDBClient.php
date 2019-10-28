<?php


namespace App\Client;


use App\Enum\TVDBImageType;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TVDBClient
{

    private const MAX_QUERY_WORDS = 4;
    private $client;

    public function __construct(HttpClientInterface $tvdbClient)
    {
        $this->client = $tvdbClient;
    }

    public function serie(int $id): array
    {
        return $this->client->request('GET', "/series/$id")->toArray()['data'];
    }

    public function search(string $title): array
    {
        $query = [
            'name' => $title
        ];

        return $this->client->request('GET', '/search/series', compact('query'))->toArray()['data'];
    }

    public function seriePosters(int $id): array
    {
        $query = [
            'keyType' => TVDBImageType::POSTER
        ];

        return $this->client->request('GET', "/series/$id/images/query", compact('query'))->toArray()['data'];
    }

    public function serieFanarts(int $id): array
    {
        $query = [
            'keyType' => TVDBImageType::FANART
        ];

        return $this->client->request('GET', "/series/$id/images/query", compact('query'))->toArray()['data'];
    }

    public function fetchSerie(string $title): array
    {
        $title = $this->shortenTitle($title);
        $search = $this->search($title);

        return $this->serie($search[0]['id']);
    }

    public function fetchSeriePoster(string $title): array
    {
        $title = $this->shortenTitle($title);
        $search = $this->search($title);

        return $this->seriePosters($search[0]['id'])[0];
    }

    public function fetchSerieFanart(string $title): array
    {
        $title = $this->shortenTitle($title);
        $search = $this->search($title);

        return $this->serieFanarts($search[0]['id'])[0];
    }

    public function fetchSeriePosters(string $title): array
    {
        $title = $this->shortenTitle($title);
        $search = $this->search($title);

        return $this->seriePosters($search[0]['id']);
    }

    public function fetchSerieFanarts(string $title): array
    {
        $title = $this->shortenTitle($title);
        $search = $this->search($title);

        return $this->serieFanarts($search[0]['id']);
    }

    private function shortenTitle(string $title): string
    {
        $words = str_word_count($title, 1);
        $title = '';
        foreach ($words as $i => $word) {
            if ($i > self::MAX_QUERY_WORDS - 1) {
                break;
            }
            $title .= "$word ";
        }

        return trim($title);
    }

}
