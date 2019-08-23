<?php

namespace App\GraphQL\Resolver\Anime;

use App\Entity\Anime;
use App\Model\RelatedAnimeRecommendation;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class RelatedAnimeRecommendationsResolver implements ResolverInterface
{

    public function __invoke(Anime $anime, Argument $args)
    {
        $first = $args->offsetGet('first');

        $related = $anime->getRelatedAnimeRecommendations();

        $related = ucollection_sort($related, static function (RelatedAnimeRecommendation $a, RelatedAnimeRecommendation $b) {
            return $b->getScore() - $a->getScore();
        });

        return $related
            ->filter(static function (RelatedAnimeRecommendation $relatedAnimeRecommendation) {
                return $relatedAnimeRecommendation->getScore() >= RelatedAnimeRecommendation::MIN_SCORE_TO_SHOW;
            })
            ->slice(0, $first);
    }

}
