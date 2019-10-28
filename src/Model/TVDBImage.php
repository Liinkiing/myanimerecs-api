<?php


namespace App\Model;


class TVDBImage implements ApiModel
{

    private const BASE_ASSETS_PATH = 'https://www.thetvdb.com/banners';

    private $id;
    private $keyType;
    private $filename;
    private $resolution;
    private $rating;

    public static function fromApi(array $response): self
    {
        return new self(
            $response['id'],
            $response['keyType'],
            $response['fileName'],
            $response['resolution'],
            $response['ratingsInfo']['average']
        );
    }

    private function __construct(
        int $id,
        string $keyType,
        string $filename,
        string $resolution,
        float $rating
    )
    {
        $this->id = $id;
        $this->keyType = $keyType;
        $this->filename = $filename;
        $this->resolution = $resolution;
        $this->rating = $rating;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getKeyType(): string
    {
        return $this->keyType;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getResolution(): string
    {
        return $this->resolution;
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function getPublicPath(): string
    {
        return self::BASE_ASSETS_PATH . "/{$this->filename}";
    }


}
