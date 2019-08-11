<?php


namespace App\Model;


class Anime implements ApiModel
{

    protected $malId;
    protected $title;
    protected $url;
    protected $imageUrl;

    public static function fromApi(array $response): self
    {
        return new self(
            (int)$response['mal_id'],
            $response['title'],
            $response['url'],
            $response['image_url']
        );
    }

    private function __construct(
        int $malId,
        string $title,
        string $url,
        ?string $imageUrl
    )
    {
        $this->malId = $malId;
        $this->title = $title;
        $this->url = $url;
        $this->imageUrl = $imageUrl;
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


}
