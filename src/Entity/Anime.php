<?php

namespace App\Entity;

use App\Exception\InvalidModelException;
use App\Model\ApiModel;
use App\Model\FromAnimeRecommendation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnimeRepository")
 * @ORM\Table(
 *     indexes={@Index(name="mal_anime_idx", columns={"mal_id"})}
 * )
 */
class Anime implements EntityApiModel
{
    public static function fromModel(ApiModel $model): self
    {
        if ($model instanceof \App\Model\Anime) {
            $instance = new self();
            $instance
                ->setMalId($model->getMalId())
                ->setTitle($model->getTitle())
                ->setUrl($model->getUrl())
                ->setEpisodesCount($model->getEpisodesCount())
                ->setScore($model->getScore())
                ->setStatus($model->getStatus())
                ->setAiredFrom($model->getAiredFrom())
                ->setRating($model->getRating())
                ->setSynonyms($model->getSynonyms())
                ->setType($model->getType())
                ->setRank($model->getRank())
                ->setPopularity($model->getPopularity())
                ->setAiredTo($model->getAiredTo())
                ->setOpeningThemes($model->getOpeningThemes())
                ->setEndingThemes($model->getEndingThemes())
                ->setBroadcast($model->getBroadcast())
                ->setDuration($model->getDuration())
                ->setTrailerUrl($model->getTrailerUrl())
                ->setTitleEnglish($model->getTitleEnglish())
                ->setTitleJapanese($model->getTitleJapanese())
                ->setAiring($model->getAiring())
                ->setImageUrl($model->getImageUrl())
                ->setDescription($model->getDescription())
            ;

            return $instance;
        }

        throw new InvalidModelException();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $imageUrl;

    /**
     * @ORM\Column(type="integer")
     */
    private $episodesCount;

    /**
     * @ORM\Column(type="boolean")
     */
    private $airing;

    /**
     * @ORM\Column(type="float")
     */
    private $score;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Recommendation", mappedBy="anime", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\OrderBy({"recommendationCount" = "DESC"})
     */
    private $recommendations;

    /**
     * @ORM\Column(type="integer")
     */
    private $malId;

    /**
     * @see FromAnimeRecommendation Not used by Doctrine, but to extends the entity from a viewer perspective by showing him
     * where do the recommendation came from
     */
    private $fromAnimeRecommendations;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trailerUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titleEnglish;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titleJapanese;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $airedFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $airedTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rating;

    /**
     * @ORM\Column(type="json_array")
     */
    private $synonyms;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $rank;

    /**
     * @ORM\Column(type="integer")
     */
    private $popularity;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Genre", inversedBy="animes")
     */
    private $genres;

    /**
     * @ORM\Column(type="json_array")
     */
    private $openingThemes;

    /**
     * @ORM\Column(type="json_array")
     */
    private $endingThemes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $broadcast;

    public function __construct()
    {
        $this->recommendations = new ArrayCollection();
        $this->fromAnimeRecommendations = new ArrayCollection();
        $this->genres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getEpisodesCount(): ?int
    {
        return $this->episodesCount;
    }

    public function setEpisodesCount(int $episodesCount): self
    {
        $this->episodesCount = $episodesCount;

        return $this;
    }

    public function getAiring(): ?bool
    {
        return $this->airing;
    }

    public function setAiring(bool $airing): self
    {
        $this->airing = $airing;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return Collection|Recommendation[]
     */
    public function getRecommendations(): Collection
    {
        return $this->recommendations;
    }

    public function addRecommendation(Recommendation $recommendation): self
    {
        if (!$this->recommendations->contains($recommendation)) {
            $this->recommendations[] = $recommendation;
            $recommendation->setAnime($this);
        }

        return $this;
    }

    public function removeRecommendation(Recommendation $recommendation): self
    {
        if ($this->recommendations->contains($recommendation)) {
            $this->recommendations->removeElement($recommendation);
            // set the owning side to null (unless already changed)
            if ($recommendation->getRecommended() === $this) {
                $recommendation->setRecommended(null);
            }
        }

        return $this;
    }

    public function clearRecommendations(): self
    {
        foreach ($this->recommendations as $recommendation) {
            $this->removeRecommendation($recommendation);
        }

        return $this;
    }

    public function clearGenres(): self
    {
        foreach ($this->genres as $genre) {
            $this->removeGenre($genre);
        }

        return $this;
    }

    public function getMalId(): ?int
    {
        return $this->malId;
    }

    public function setMalId(int $malId): self
    {
        $this->malId = $malId;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<int, FromAnimeRecommendation>
     */
    public function getFromAnimeRecommendations(): Collection
    {
        return $this->fromAnimeRecommendations ?? new ArrayCollection([]);
    }

    public function addFromAnimeRecommendation(FromAnimeRecommendation $item): self
    {
        if (!$this->fromAnimeRecommendations) {
            $this->fromAnimeRecommendations = new ArrayCollection([]);
        }
        if (!$this->fromAnimeRecommendations->contains($item)) {
            $this->fromAnimeRecommendations->add($item);
        }

        return $this;
    }

    public function removeFromAnimeRecommendation(FromAnimeRecommendation $item): self
    {
        if (!$this->fromAnimeRecommendations) {
            $this->fromAnimeRecommendations = new ArrayCollection([]);
        }
        if ($this->fromAnimeRecommendations->contains($item)) {
            $this->fromAnimeRecommendations->removeElement($item);
        }

        return $this;
    }

    public function getTrailerUrl(): ?string
    {
        return $this->trailerUrl;
    }

    public function setTrailerUrl(?string $trailerUrl): self
    {
        $this->trailerUrl = $trailerUrl;

        return $this;
    }

    public function getTitleEnglish(): ?string
    {
        return $this->titleEnglish;
    }

    public function setTitleEnglish(?string $titleEnglish): self
    {
        $this->titleEnglish = $titleEnglish;

        return $this;
    }

    public function getTitleJapanese(): ?string
    {
        return $this->titleJapanese;
    }

    public function setTitleJapanese(?string $titleJapanese): self
    {
        $this->titleJapanese = $titleJapanese;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAiredFrom(): ?\DateTimeInterface
    {
        return $this->airedFrom;
    }

    public function setAiredFrom(?\DateTimeInterface $airedFrom): self
    {
        $this->airedFrom = $airedFrom;

        return $this;
    }

    public function getAiredTo(): ?\DateTimeInterface
    {
        return $this->airedTo;
    }

    public function setAiredTo(?\DateTimeInterface $airedTo): self
    {
        $this->airedTo = $airedTo;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getSynonyms()
    {
        return $this->synonyms;
    }

    public function setSynonyms($synonyms): self
    {
        $this->synonyms = $synonyms;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getPopularity(): ?int
    {
        return $this->popularity;
    }

    public function setPopularity(int $popularity): self
    {
        $this->popularity = $popularity;

        return $this;
    }

    /**
     * @return Collection|Genre[]
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genres->contains($genre)) {
            $this->genres[] = $genre;
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        if ($this->genres->contains($genre)) {
            $this->genres->removeElement($genre);
        }

        return $this;
    }

    public function getOpeningThemes()
    {
        return $this->openingThemes;
    }

    public function setOpeningThemes($openingThemes): self
    {
        $this->openingThemes = $openingThemes;

        return $this;
    }

    public function getEndingThemes()
    {
        return $this->endingThemes;
    }

    public function setEndingThemes($endingThemes): self
    {
        $this->endingThemes = $endingThemes;

        return $this;
    }

    public function getBroadcast(): ?string
    {
        return $this->broadcast;
    }

    public function setBroadcast(?string $broadcast): self
    {
        $this->broadcast = $broadcast;

        return $this;
    }
}
