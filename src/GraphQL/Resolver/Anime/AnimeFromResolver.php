<?php

namespace App\GraphQL\Resolver\Anime;

use App\Entity\Anime;
use App\Model\FromAnimeRecommendation;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class AnimeFromResolver implements ResolverInterface
{

    public function __invoke(Anime $anime, Argument $args)
    {
        $first = $args->offsetGet('first');

        $from = $anime->getFromAnimeRecommendations();

        $from = ucollection_sort($from, static function (FromAnimeRecommendation $a, FromAnimeRecommendation $b) {
            return $b->getScore() - $a->getScore();
        });

        return $from
            ->filter(static function (FromAnimeRecommendation $fromAnimeRecommendation) {
                return $fromAnimeRecommendation->getScore() >= FromAnimeRecommendation::MIN_SCORE_TO_SHOW;
            })
            ->slice(0, $first);
    }

}
