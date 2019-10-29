<?php

namespace App\GraphQL\Resolver\Anime;

use App\Entity\Anime;
use App\Model\RelatedAnimeRecommendation;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class AnimeMediaResolver implements ResolverInterface
{

    public function __invoke(Anime $anime)
    {
        return [
            'background' => $anime->getBackgroundImageUrl() ?? $anime->getImageUrl(),
            'banner' => $anime->getBannerImageUrl(),
            'poster' => $anime->getPosterImageUrl()
        ];
    }

}
