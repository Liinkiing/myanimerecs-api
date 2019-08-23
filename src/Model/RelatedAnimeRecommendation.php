<?php


namespace App\Model;


/**
 * @see \App\Entity\Anime::$from
 * This class is used to explain why an anime has been recommended by showing the anime that led to the recommendation and a score
 */
class RelatedAnimeRecommendation
{
    public const MIN_SCORE_TO_SHOW = 10;

    protected $anime;
    protected $score;

    public function __construct(\App\Entity\Anime $anime, float $score)
    {
        $this->anime = $anime;
        $this->score = $score;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getAnime(): \App\Entity\Anime
    {
        return $this->anime;
    }

    public function setAnime(\App\Entity\Anime $anime): self
    {
        $this->anime = $anime;

        return $this;
    }
}
