<?php

namespace App\GraphQL\Resolver\Query;

use App\Repository\AnimeRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class AnimeResolver implements ResolverInterface
{

    private const SLUG_DELIMITER = '-';
    private $animeRepository;

    public function __construct(AnimeRepository $animeRepository)
     {
         $this->animeRepository = $animeRepository;
     }

    public function __invoke(Argument $args)
    {
        $slug = $args->offsetGet('slug');

        $parts = explode(self::SLUG_DELIMITER, $slug);

        $id = end($parts);

        return $this->animeRepository->findOneBy(['malId' => $id]);
    }

}
