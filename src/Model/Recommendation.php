<?php


namespace App\Model;


class Recommendation implements ApiModel
{

    protected $malId;
    protected $title;
    protected $url;
    protected $imageUrl;
    protected $recommendationUrl;
    protected $recommendationCount;

    public static function fromApi(array $response): self
    {
        return new self(
            (int)$response['mal_id'],
            $response['title'],
            $response['url'],
            $response['image_url'],
            $response['recommendation_url'],
            $response['recommendation_count']
        );
    }

    private function __construct(
        int $malId,
        string $title,
        string $url,
        ?string $imageUrl,
        ?string $recommendationUrl,
        ?string $recommendationCount
    )
    {
        $this->malId = $malId;
        $this->title = $title;
        $this->url = $url;
        $this->imageUrl = $imageUrl;
        $this->recommendationUrl = $recommendationUrl;
        $this->recommendationCount = $recommendationCount;
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

    public function getRecommendationUrl(): ?string
    {
        return $this->recommendationUrl;
    }

    public function getRecommendationCount(): ?string
    {
        return $this->recommendationCount;
    }

}
