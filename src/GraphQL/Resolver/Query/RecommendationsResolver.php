<?php

namespace App\GraphQL\Resolver\Query;

use App\Client\JikanClient;
use App\Entity\Anime as AnimeEntity;
use App\Entity\Recommendation as RecommentationEntity;
use App\Model\Anime as AnimeModel;
use App\Model\Recommendation as RecommentationModel;
use App\Repository\AnimeRepository;
use App\Repository\RecommendationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class RecommendationsResolver implements ResolverInterface
{

    private const SAMPLE_RATE = 0.4;
    private const MAX_ANIME = 30;
    private $client;
    private $animeRepository;
    private $em;
    private $recommendationRepository;

    public function __construct(JikanClient $client, EntityManagerInterface $em, RecommendationRepository $recommendationRepository, AnimeRepository $animeRepository)
    {
        $this->client = $client;
        $this->animeRepository = $animeRepository;
        $this->em = $em;
        $this->recommendationRepository = $recommendationRepository;
    }

    public function __invoke(Argument $args)
    {
        $username = $args->offsetGet('username');

        /** @var AnimeEntity[] $animelist */
        $animelist = array_map(function (array $animeApi) {
            $malId = $animeApi['mal_id'];
            $animeEntity = $this->animeRepository->findOneBy(['malId' => $malId]);
            if (!$animeEntity) {
                $animeEntity = $this->getAnimeEntity(
                    AnimeModel::fromApi(
                        $this->client->anime($malId)
                    )
                );
            }
            return $animeEntity;
        }, $this->client->userAnimelist($username));

        $length = (int)floor(count($animelist) * self::SAMPLE_RATE);
        if ($length > self::MAX_ANIME) {
            $animelist = array_splice($animelist, 0, self::MAX_ANIME);
        }

        $recommendationsByAnime = [];
        foreach ($animelist as $index => $anime) {
            $recommendationsByAnime[$anime->getMalId()] = array_map(function (array $recommendation) {
                $recommendationModel = RecommentationModel::fromApi($recommendation);
                $anime = $this->animeRepository->findOneBy(['malId' => $recommendationModel->getMalId()]);
                if (!$anime) {
                    $anime = $this->getAnimeEntity(
                        AnimeModel::fromApi(
                            $this->client->anime(
                                $recommendationModel->getMalId()
                            )
                        )
                    );
                    usleep(600000);
                }
                return $this->getRecommendationEntity($anime, $recommendationModel);
            }, $this->client->animeRecommendations($anime->getMalId()));
            usleep(600000);
        }

        $recommendedAnimesIds = [];
        foreach($recommendationsByAnime as $animeId => $recommendations) {
            $recommendedAnimesIds[] = array_map(static function (RecommentationEntity $recommendation) {
               return $recommendation->getRecommended()->getMalId();
            }, $recommendations);
        }

        $recommendedAnimesIds = array_flatten($recommendedAnimesIds);
        $recommendedAnimesIds = array_sort_by_occurrences($recommendedAnimesIds, 8);
        $recommendedAnimesIds = array_unique($recommendedAnimesIds);

        return $this->animeRepository->findBy(['malId' => $recommendedAnimesIds]);
    }

    private function getAnimeEntity(AnimeModel $anime): AnimeEntity
    {
        $entity = $this->animeRepository->findOneBy(['malId' => $anime->getMalId()]);
        if (!$entity) {
            $entity = AnimeEntity::fromModel($anime);
            $this->em->persist(
                $entity
            );
            $this->em->flush();
        }
        return $entity;
    }

    private function getRecommendationEntity(AnimeEntity $related, RecommentationModel $recommendation): RecommentationEntity
    {
        $entity = $this->recommendationRepository->findOneBy(
            compact('related')
        );
        if (!$entity) {
            $entity = (RecommentationEntity::fromModel($recommendation))
                ->setRecommended($related);
            $this->em->persist(
                $entity
            );
            $this->em->flush();
        }
        return $entity;
    }

}
