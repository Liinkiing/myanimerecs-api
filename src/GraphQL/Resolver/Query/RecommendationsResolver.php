<?php

namespace App\GraphQL\Resolver\Query;

use App\Client\JikanClient;
use App\Entity\Anime;
use App\Entity\Recommendation;
use App\Enum\AnimeListItemWatchingStatus;
use App\Message\AddInexistingAnimes;
use App\Model\FromAnimeRecommendation;
use App\Repository\AnimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RecommendationsResolver implements ResolverInterface
{

    public const CACHE_KEY = 'recommendations_resolver.results';
    public const RECOMMENDATIONS_COUNT_WEIGHT = 1.7;
    public const USER_SCORE_WEIGHT = 6;

    private const SAMPLE_RATE = 0.5;
    private const MAX_ANIME = 500;
    private const MIN_RECOMMENDATIONS_COUNT = 4;

    private $client;
    private $animeRepository;
    private $logger;
    private $cache;
    private $cacheTtl;
    private $bus;

    public function __construct(JikanClient $client, MessageBusInterface $bus, AnimeRepository $animeRepository, LoggerInterface $logger, CacheInterface $cache, int $cacheTtl)
    {
        $this->client = $client;
        $this->animeRepository = $animeRepository;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
        $this->bus = $bus;
    }

    private function mapResponseFromAnimeList(array $responseFromApi): array
    {
        return [
            'mal_id' => (int)$responseFromApi['mal_id'],
            'watching_status' => (int)$responseFromApi['watching_status'],
            'user_score' => $responseFromApi['score']
        ];
    }

    private function mapAnimelistToIds(array $animelist): array
    {
        return array_map(static function (array $animelistItem) {
            return $animelistItem['mal_id'];
        }, $animelist);
    }

    public function __invoke(Argument $args)
    {
        $username = $args->offsetGet('username');

        return $this->cache->get(self::CACHE_KEY. '.' . $username, function (ItemInterface $item) use ($username) {
            $item->expiresAfter($this->cacheTtl);

            $animelist = array_map([$this, 'mapResponseFromAnimeList'], $this->client->userAnimelist($username));

            $length = (int)floor(count($animelist) * self::SAMPLE_RATE);
            if ($length > self::MAX_ANIME) {
                $animelist = array_splice($animelist, 0, self::MAX_ANIME);
            }

            $malIds = $this->mapAnimelistToIds($animelist);

            $animes = $this->animeRepository->findByAnimeList($malIds);

            $idsInDb = $animes->map(static function (Anime $anime) {
                return $anime->getMalId();
            })->toArray();

            $diff = array_diff($malIds, $idsInDb);
            if (count($diff) > 0) {
                $this->bus->dispatch(
                    new AddInexistingAnimes($diff)
                );
            }
            /** @var ArrayCollection<int, Recommendation> $recommendations */
            $recommendations = $this->getAnimeRecommendationsBasedOnAnimelist($animes, $animelist);

            $recommended = $recommendations->map(function (Recommendation $recommendation) use ($animelist, $recommendations) {
                return $this->getRecommendedItem($recommendation, $recommendations, $animelist);
            })->toArray();

            $max = $this->getMaxScoreForRecommended($recommended);

            uasort($recommended, static function (array $a, array $b) {
                return $b['recommendation_score'] - $a['recommendation_score'];
            });

            $recommendedIds = array_unique(
                array_column($recommended, 'recommended_id')
            );

            return $this->mapResultsWithViewerFromRecommendations(
                $this->animeRepository->findByOrderedMalIds($recommendedIds),
                $animes,
                $recommended,
                $max
            );
        });
    }

    protected function getMaxScoreForRecommended(array $recommended): float
    {
        $scores = array_map(static function (array $recommendedItem) {
            return $recommendedItem['recommendation_score'];
        }, $recommended);

        return max($scores);
    }

    protected function mapResultsWithViewerFromRecommendations(Collection $results, Collection $animes, array $recommended, float $maxScore): Collection
    {
        return $results->map(function (Anime $anime) use ($maxScore, $recommended, $animes) {
            $animes
                ->filter(static function (Anime $animeFromMyAnimeList) use ($anime) {
                    return $animeFromMyAnimeList->getRecommendations()->filter(static function (Recommendation $recommendation) use ($anime) {
                        return $recommendation->getAnime()->getMalId() === $anime->getMalId();
                    });
                })
                ->map(function (Anime $animeFromMyAnimeList) use ($maxScore, $recommended, $anime) {
                    $recommendationForScore = $animeFromMyAnimeList->getRecommendations()->filter(static function (Recommendation $r) use ($anime) {
                        return $r->getRecommended()->getMalId() === $anime->getMalId();
                    })->first();
                    if ($recommendationForScore) {
                        $score = array_values(
                            array_filter($recommended, static function (array $recommendedItem) use ($animeFromMyAnimeList, $anime) {
                                return $recommendedItem['recommended_id'] === $anime->getMalId() && $recommendedItem['anime_id'] === $animeFromMyAnimeList->getMalId();
                            })
                        );
                        $score = count($score) > 0 ? $score[0]['recommendation_score'] : 0;
                        $score = (int)map_numbers(sqrt($score), 0, sqrt($maxScore), 0, 100);
                        $fromRecommendation = new FromAnimeRecommendation(
                            $animeFromMyAnimeList,
                            $score
                        );
                        $anime->addFromAnimeRecommendation($fromRecommendation);
                    }
                });
            return $anime;
        });
    }

    /**
     * @param Collection<int, Anime> $animes
     * @param array $animelist
     * @return ArrayCollection<int, Recommendation>
     */
    protected function getAnimeRecommendationsBasedOnAnimelist(Collection $animes, array $animelist): ArrayCollection
    {
        return array_reduce($animes->toArray(), function (ArrayCollection $acc, \App\Entity\Anime $item) use ($animelist) {
            $item->getRecommendations()
                ->filter(function (Recommendation $recommendation) use ($animelist) {
                    $completed = array_values(
                        array_filter($animelist, static function (array $animelistItem) {
                            return $animelistItem['watching_status'] === AnimeListItemWatchingStatus::COMPLETED;
                        })
                    );
                    $dropped = array_values(
                        array_filter($animelist, static function (array $animelistItem) {
                            return $animelistItem['watching_status'] === AnimeListItemWatchingStatus::DROPPED;
                        })
                    );
                    return $recommendation->getRecommendationCount() >= self::MIN_RECOMMENDATIONS_COUNT &&
                        !in_array($recommendation->getRecommended()->getMalId(), array_merge(
                            $this->mapAnimelistToIds($completed),
                            $this->mapAnimelistToIds($dropped)
                        ), true);
                })
                ->map(static function (Recommendation $recommendation) use ($acc) {
                    $acc->add($recommendation);
                });

            return $acc;
        }, new ArrayCollection([]));
    }

    protected function computeRecommendationScore(Recommendation $recommendation, Collection $recommendations, array $animelist): float
    {
        $occurrences = $recommendations
            ->filter(static function (Recommendation $r) use ($recommendation) {
                return $r->getRecommended()->getMalId() === $recommendation->getRecommended()->getMalId();
            })->count();

        //$this->logger->info('Because you watched ' . $recommendation->getAnime()->getTitle() . ', you should like ' .
        //    $recommendation->getRecommended()->getTitle() . ' because it has been recommended ' .
        //    $recommendation->getRecommendationCount() . ' times and appears ' . $occurrences . ' times'
        //);

        //$this->logger->info($recommendation->getRecommended()->getTitle() . ' appears ' .
        //    $occurrences . ' times in all recommendations and has been recommended ' .
        //    $recommendation->getRecommendationCount() . ' times'
        //);

        //$score = $recommendation->getRecommendationCount() * $occurrences;
        $animeUserScore = array_values(
            array_filter($animelist, static function (array $animelistItem) use ($recommendation) {
                return $animelistItem['mal_id'] === $recommendation->getAnime()->getMalId() &&
                    $animelistItem['user_score'] > 0;
            })
        );

        $animeUserScore = count($animeUserScore) > 0 ? $animeUserScore[0]['user_score'] : 0;
        $score = ($animeUserScore ** self::USER_SCORE_WEIGHT + ($recommendation->getRecommendationCount() ** self::RECOMMENDATIONS_COUNT_WEIGHT)) * $occurrences;

        return $score;
    }

    protected function getRecommendedItem(Recommendation $recommendation, ArrayCollection $recommendations, array $animelist): array
    {
        return [
            'anime_id' => $recommendation->getAnime()->getMalId(),
            'anime_title' => $recommendation->getAnime()->getTitle(),
            'recommended_id' => $recommendation->getRecommended()->getMalId(),
            'recommended_title' => $recommendation->getRecommended()->getTitle(),
            'recommended_count' => $recommendation->getRecommendationCount(),
            'recommendation_score' => $this->computeRecommendationScore($recommendation, $recommendations, $animelist),
        ];
    }

}
