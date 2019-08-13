<?php


namespace App\Model;


class Anime implements ApiModel
{

    protected $malId;
    protected $title;
    protected $url;
    protected $imageUrl;
    protected $airing;
    protected $synopsis;
    protected $episodesCount;
    protected $score;

    public static function fromApi(array $response): self
    {
        return new self(
            (int)$response['mal_id'],
            $response['title'],
            $response['synopsis'],
            $response['url'],
            (int)$response['episodes'],
            (float)$response['score'],
            $response['airing'],
            $response['image_url']
        );
    }

    private function __construct(
        int $malId,
        string $title,
        string $synopsis,
        string $url,
        int $episodeCount,
        float $score,
        ?bool $airing = true,
        ?string $imageUrl = null
    )
    {
        $this->malId = $malId;
        $this->title = $title;
        $this->synopsis = $synopsis;
        $this->url = $url;
        $this->imageUrl = $imageUrl;
        $this->airing = $airing;
        $this->episodesCount = $episodeCount;
        $this->score = $score;
    }

    public function getMalId(): int
    {
        return $this->malId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getAiring(): ?bool
    {
        return $this->airing;
    }

    public function getSynopsis(): string
    {
        return $this->synopsis;
    }

    public function getEpisodesCount(): int
    {
        return $this->episodesCount;
    }

    public function getScore(): float
    {
        return $this->score;
    }

}
