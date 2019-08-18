<?php


namespace App\Model;


class Genre implements ApiModel
{

    protected $malId;
    protected $name;
    protected $url;

    public static function fromApi(array $response): self
    {
        return new self(
            (int)$response['mal_id'],
            $response['name'],
            $response['url']
        );
    }

    private function __construct(
        int $malId,
        string $name,
        string $url
    )
    {
        $this->malId = $malId;
        $this->name = $name;
        $this->url = $url;
    }

    public function getMalId(): int
    {
        return $this->malId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }



}
