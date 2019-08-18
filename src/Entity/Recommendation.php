<?php

namespace App\Entity;

use App\Exception\InvalidModelException;
use App\Model\ApiModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecommendationRepository")
 */
class Recommendation implements EntityApiModel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Anime", inversedBy="recommendations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $anime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Anime", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $recommended;

    /**
     * @ORM\Column(type="integer")
     */
    private $recommendationCount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecommended(): ?Anime
    {
        return $this->recommended;
    }

    public function setRecommended(?Anime $recommended): self
    {
        $this->recommended = $recommended;

        return $this;
    }

    public function getRecommendationCount(): ?int
    {
        return $this->recommendationCount;
    }

    public function setRecommendationCount(int $recommendationCount): self
    {
        $this->recommendationCount = $recommendationCount;

        return $this;
    }

    public static function fromModel(ApiModel $model): self
    {
        if ($model instanceof \App\Model\Recommendation) {
            $instance = new self();

            $instance
                ->setRecommendationCount($model->getRecommendationCount());

            return $instance;
        }

        throw new InvalidModelException();
    }

    public function getAnime(): ?Anime
    {
        return $this->anime;
    }

    public function setAnime(?Anime $anime): self
    {
        $this->anime = $anime;

        return $this;
    }
}
