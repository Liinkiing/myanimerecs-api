<?php

namespace App\Entity;

use App\Exception\InvalidModelException;
use App\Model\ApiModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GenreRepository")
 * @ORM\Table(
 *     indexes={@Index(name="mal_genre_idx", columns={"mal_id"})}
 * )
 */
class Genre
{
    public static function fromModel(ApiModel $model): self
    {
        if ($model instanceof \App\Model\Genre) {
            $instance = new self();
            $instance
                ->setMalId($model->getMalId())
                ->setUrl($model->getUrl())
                ->setName($model->getName())
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
     * @ORM\Column(type="integer")
     */
    private $malId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Anime", mappedBy="genres")
     */
    private $animes;

    public function __construct()
    {
        $this->animes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
     * @return Collection|Anime[]
     */
    public function getAnimes(): Collection
    {
        return $this->animes;
    }

    public function addAnime(Anime $anime): self
    {
        if (!$this->animes->contains($anime)) {
            $this->animes[] = $anime;
            $anime->addGenre($this);
        }

        return $this;
    }

    public function removeAnime(Anime $anime): self
    {
        if ($this->animes->contains($anime)) {
            $this->animes->removeElement($anime);
            $anime->removeGenre($this);
        }

        return $this;
    }
}
