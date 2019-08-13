<?php

namespace App\GraphQL\Resolver\Query;

use App\Client\JikanClient;
use App\Model\Anime;
use App\Model\Recommendation;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class RecommendationsResolver implements ResolverInterface
{

    private const SAMPLE_RATE = 0.4;
    private const MAX_ANIME = 30;
    private $client;

    public function __construct(JikanClient $client)
    {
        $this->client = $client;
    }

    public function __invoke(Argument $args)
    {
        $username = $args->offsetGet('username');

        /** @var Anime[] $animelist */
        $animelist = array_map(function (array $anime) {
            return Anime::fromApi($anime);
        }, $this->client->userAnimelist($username));

        $length = (int)floor(count($animelist) * self::SAMPLE_RATE);
        if ($length > self::MAX_ANIME) {
            $animelist = array_splice($animelist, 0, self::MAX_ANIME);
        }

        $recommendationsByAnime = [];
        foreach ($animelist as $index => $anime) {
            $recommendationsByAnime[$anime->getMalId()] = array_map(static function (array $recommendation) {
                return Recommendation::fromApi($recommendation);
            }, $this->client->animeRecommendations($anime->getMalId()));
            usleep(1000000);
        }

        $recommendedAnimesIds = [];
        foreach($recommendationsByAnime as $animeId => $recommendations) {
            $recommendedAnimesIds[] = array_map(static function (Recommendation $recommendation) {
               return $recommendation->getMalId();
            }, $recommendations);
        }

        $recommendedAnimesIds = array_flatten($recommendedAnimesIds);
        $recommendedAnimesIds = array_sort_by_occurrences($recommendedAnimesIds, 8);
        $recommendedAnimesIds = array_unique($recommendedAnimesIds);

        return array_map(function (int $animeId) {
            return Anime::fromApi(
                $this->client->anime($animeId)
            );
        }, $recommendedAnimesIds);
    }

}
