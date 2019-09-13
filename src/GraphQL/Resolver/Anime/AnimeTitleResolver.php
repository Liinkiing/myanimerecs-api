<?php

namespace App\GraphQL\Resolver\Anime;

use App\Entity\Anime;
use App\Model\RelatedAnimeRecommendation;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class AnimeTitleResolver implements ResolverInterface
{

    public function __invoke(Anime $anime)
    {
        return [
            'english' => $anime->getTitle(),
            'japanese' => $anime->getTitleJapanese(),
        ];
    }

}
