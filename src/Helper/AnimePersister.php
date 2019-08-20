<?php


namespace App\Helper;


use App\Client\JikanClient;
use App\Entity\Anime;
use App\Entity\Recommendation;
use App\Entity\Recommendation as RecommendationEntity;
use App\Model\Genre;
use App\Model\Recommendation as RecommendationModel;
use App\Repository\AnimeRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;

class AnimePersister
{
    private $animeRepository;
    private $client;
    private $em;
    private $logger;
    private $genreRepository;
    private $genres;

    public function __construct(AnimeRepository $animeRepository, GenreRepository $genreRepository, JikanClient $client, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->animeRepository = $animeRepository;
        $this->client = $client;
        $this->em = $em;
        $this->logger = $logger;
        $this->genreRepository = $genreRepository;
        $this->genres = $this->genreRepository->findAllCollection();
    }

    protected function createAnimeEntity(int $malId, bool $withRecommendations = true): ?Anime
    {
        try {
            $animeFromApi = \App\Model\Anime::fromApi(
                $this->client->anime($malId)
            );

            $entity = Anime::fromModel($animeFromApi);

            $animeFromApi->getGenres()
                ->map(function (Genre $genreFromApi) {
                    return $this->genreRepository->findOneBy(['malId' => $genreFromApi->getMalId()]);
                })->map(static function (\App\Entity\Genre $genreFromDb) use ($entity) {
                    $entity->addGenre($genreFromDb);
                })
            ;

            if ($withRecommendations) {
                foreach ($this->client->animeRecommendations($malId) as $recommendationFromApi) {
                    $this->addRecommendationForEntity($recommendationFromApi, $entity);
                }
            }

            return $entity;
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }
            if ($exception->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                return $this->createAnimeEntity($malId, $withRecommendations);
            }

            return null;
        }
    }

    protected function updateAnimeEntity(Anime $entity): Anime
    {
        $animeFromApi = \App\Model\Anime::fromApi(
            $this->client->anime($entity->getMalId())
        );

        $this->reflectScalarFieldsDifferences($entity, $animeFromApi);

        $entity->clearGenres();

        $animeFromApi->getGenres()
            ->map(function (Genre $genreFromApi) {
                return $this->genreRepository->findOneBy(['malId' => $genreFromApi->getMalId()]);
            })->map(static function (\App\Entity\Genre $genreFromDb) use ($entity) {
                $entity->addGenre($genreFromDb);
            })
        ;

        return $entity;
    }

    protected function reflectScalarFieldsDifferences(Anime $entity, \App\Model\Anime $model): void
    {
        $reflection = new \ReflectionClass($entity);
        $excludedMethods = [
            'getters' => ['getId', 'getGenres', 'getRecommendations', 'getFromAnimeRecommendations'],
            'setters' => ['setId', 'setGenres', 'setRecommendations', 'setFromAnimeRecommendations']
        ];

        $entityGettersSetters = array_reduce(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            static function (array $acc, \ReflectionMethod $method) use ($excludedMethods) {
                $name = $method->getName();
                if (Str::startsWith($name, 'get') && !in_array($name, $excludedMethods['getters'], true)) {
                    $acc['getters'][] = $method;
                } elseif (Str::startsWith($name, 'set') && !in_array($name, $excludedMethods['setters'], true)) {
                    $acc['setters'][] = $method;
                }
                return $acc;
            },
            []
        );

        foreach ($entityGettersSetters['getters'] as $index => $getter) {
            $entityValue = $entity->{$getter->getName()}();
            $modelValue = $model->{$getter->getName()}();
            if ($entityValue !== $modelValue) {
                $setter = $entityGettersSetters['setters'][$index]->getName();
                $entity->{$setter}($modelValue);
            }
        }
    }

    protected function addRecommendationForEntity(array $recommendationFromApi, Anime $animeInDb): void
    {
        $model = RecommendationModel::fromApi($recommendationFromApi);
        if (!$recommendedAnime = $this->animeRepository->findOneBy(['malId' => $model->getMalId()])) {
            $recommendedAnime = $this->createAnimeEntity($model->getMalId(), false);
        }
        $recommendation = RecommendationEntity::fromModel(
            $model
        );
        $recommendation
            ->setRecommended($recommendedAnime);

        $animeInDb->addRecommendation($recommendation);
    }

    public function addMyAnimeListAnime(int $malId, ?SymfonyStyle $io = null, bool $force = false, bool $update = false, int $retryCount = 0): ?Anime
    {
        try {
            if ($animeInDb = $this->animeRepository->findOneBy(['malId' => $malId])) {
                if ($update) {
                    $this->updateAnimeEntity($animeInDb);
                }
                if ($force || $animeInDb->getRecommendations()->count() === 0) {
                    foreach ($this->client->animeRecommendations($animeInDb->getMalId()) as $recommendationFromApi) {
                        $this->addRecommendationForEntity($recommendationFromApi, $animeInDb);
                    }
                }
                if ($io) {
                    $io->writeln(
                        sprintf(
                            'Anime <info>"%s"</info> (<fg=blue>%s</>) already exists in db. ' . ($update ? 'Updating' : 'Skipping') . ' it.',
                            $animeInDb->getTitle(),
                            $animeInDb->getMalId()
                        )
                    );
                }
                return $animeInDb;
            }

            if ($anime = $this->createAnimeEntity($malId)) {
                if ($io) {
                    $io->block('Adding anime with id ' . $malId);
                }
                $this->em->persist($anime);
                return $anime;
            }

            return null;
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                $this->logger->warning('429 when trying to fetch anime with ID ' . $malId);
                if ($retryCount >= 100) {
                    return null;
                }
                if ($io) {
                    $io->writeln('<info>Too many requests. Retrying request (' . ($retryCount + 1) . '/100)</info>');
                }
                return $this->addMyAnimeListAnime($malId, $io, $force, $update, $retryCount + 1);
            }
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning('404 when trying to fetch anime with ID ' . $malId);
                return null;
            }

            return null;
        }
    }
}
