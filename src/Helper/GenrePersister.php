<?php


namespace App\Helper;


use App\Client\JikanClient;
use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;

class GenrePersister
{
    private $repository;
    private $client;
    private $em;
    private $logger;

    public function __construct(GenreRepository $repository, JikanClient $client, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->client = $client;
        $this->em = $em;
        $this->logger = $logger;
    }

    public function addAnimeGenre(int $malId, int $retryCount = 0): ?Genre
    {
        try {
            if (!$genre = $this->repository->findOneBy(['malId' => $malId])) {
                $genre = $this->createGenreEntity($malId);
            }

            return $genre;
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                $this->logger->warning('429 when trying to fetch genre with ID ' . $malId);
                if ($retryCount >= 5) {
                    return null;
                }
                return $this->addAnimeGenre($malId, $retryCount + 1);
            }
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning('404 when trying to fetch genre with ID ' . $malId);
                return null;
            }

            return null;
        }
    }

    protected function createGenreEntity(int $malId): Genre
    {
        return Genre::fromModel(
            \App\Model\Genre::fromApi(
                $this->client->genre($malId)
            )
        );
    }

}
