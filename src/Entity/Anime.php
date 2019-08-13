<?php

namespace App\Entity;

use App\Exception\InvalidModelException;
use App\Model\ApiModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnimeRepository")
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
                ->setDescription($model->getSynopsis())
                ->setUrl($model->getUrl())
                ->setEpisodesCount($model->getEpisodesCount())
                ->setScore($model->getScore())
                ->setAiring($model->getAiring())
                ->setImageUrl($model->getImageUrl())
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
     * @ORM\OneToMany(targetEntity="App\Entity\Recommendation", mappedBy="related", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $recommendations;

    /**
     * @ORM\Column(type="integer")
     */
    private $malId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    public function __construct()
    {
        $this->recommendations = new ArrayCollection();
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
            $recommendation->setRelated($this);
        }

        return $this;
    }

    public function removeRecommendation(Recommendation $recommendation): self
    {
        if ($this->recommendations->contains($recommendation)) {
            $this->recommendations->removeElement($recommendation);
            // set the owning side to null (unless already changed)
            if ($recommendation->getRelated() === $this) {
                $recommendation->setRelated(null);
            }
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
}
