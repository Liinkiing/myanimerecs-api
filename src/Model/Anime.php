<?php


namespace App\Model;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Anime implements ApiModel
{

    private $malId;
    private $title;
    private $url;
    private $imageUrl;
    private $airing;
    private $description;
    private $episodesCount;
    private $score;
    private $status;
    private $airedFrom;
    private $rating;
    private $synonyms;
    private $type;
    private $rank;
    private $popularity;
    private $genres;
    private $airedTo;
    private $duration;
    private $trailerUrl;
    private $titleEnglish;
    private $titleJapanese;
    private $openingThemes;
    private $endingThemes;
    private $broadcast;

    public static function fromApi(array $response): self
    {
        //TODO: Handle case when mal_id is not defined
        return new self(
            (int)$response['mal_id'],
            $response['title'],
            $response['url'],
            (int)$response['episodes'],
            (float)$response['score'],
            $response['status'],
            new \DateTimeImmutable($response['aired']['from']),
            $response['rating'],
            $response['title_synonyms'] ?? [],
            $response['type'],
            (int)$response['rank'],
            (int)$response['popularity'],
            $response['genres'] ? new ArrayCollection(array_map(static function (array $genreFromApi) {
                return Genre::fromApi($genreFromApi);
            }, $response['genres'])) : new ArrayCollection([]),
            $response['opening_themes'] ?? [],
            $response['ending_themes'] ?? [],
            $response['broadcast'],
            $response['aired']['to'] ? new \DateTimeImmutable($response['aired']['to']) : null,
            $response['duration'],
            $response['trailer_url'],
            $response['title_english'],
            $response['title_japanese'],
            $response['airing'],
            $response['image_url'],
            $response['synopsis']
        );
    }

    private function __construct(
        int $malId,
        string $title,
        string $url,
        int $episodeCount,
        float $score,
        string $status,
        \DateTimeInterface $airedFrom,
        string $rating,
        array $synonyms,
        string $type,
        int $rank,
        int $popularity,
        ArrayCollection $genres,
        array $openingThemes,
        array $endingThemes,
        ?string $broadcast = null,
        ?\DateTimeInterface $airedTo = null,
        ?string $duration = null,
        ?string $trailerUrl = null,
        ?string $titleEnglish = null,
        ?string $titleJapanese = null,
        ?bool $airing = true,
        ?string $imageUrl = null,
        ?string $description = null
    )
    {
        $this->malId = $malId;
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->imageUrl = $imageUrl;
        $this->airing = $airing;
        $this->episodesCount = $episodeCount;
        $this->score = $score;
        $this->status = $status;
        $this->airedFrom = $airedFrom;
        $this->rating = $rating;
        $this->synonyms = $synonyms;
        $this->type = $type;
        $this->rank = $rank;
        $this->popularity = $popularity;
        $this->genres = $genres;
        $this->airedTo = $airedTo;
        $this->duration = $duration;
        $this->trailerUrl = $trailerUrl;
        $this->titleEnglish = $titleEnglish;
        $this->titleJapanese = $titleJapanese;
        $this->openingThemes = $openingThemes;
        $this->endingThemes = $endingThemes;
        $this->broadcast = $broadcast;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAiredFrom(): \DateTimeInterface
    {
        return $this->airedFrom;
    }

    public function getRating(): string
    {
        return $this->rating;
    }

    public function getSynonyms(): array
    {
        return $this->synonyms;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getPopularity(): int
    {
        return $this->popularity;
    }

    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function getAiredTo(): ?\DateTimeInterface
    {
        return $this->airedTo;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function getOpeningThemes(): array
    {
        return $this->openingThemes;
    }

    public function getEndingThemes(): array
    {
        return $this->endingThemes;
    }

    public function getBroadcast(): ?string
    {
        return $this->broadcast;
    }

    public function getTrailerUrl(): ?string
    {
        return $this->trailerUrl;
    }

    public function getTitleEnglish(): ?string
    {
        return $this->titleEnglish;
    }

    public function getTitleJapanese(): ?string
    {
        return $this->titleJapanese;
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

    public function getDescription(): ?string
    {
        return $this->description;
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
