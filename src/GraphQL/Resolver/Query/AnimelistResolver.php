<?php

namespace App\GraphQL\Resolver\Query;

use App\Client\JikanClient;
use App\Model\Anime;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class AnimelistResolver implements ResolverInterface
{

    private $client;

    public function __construct(JikanClient $client)
     {
         $this->client = $client;
     }

    /**
     * @param Argument $args
     * @return Anime[]
     */
    public function __invoke(Argument $args): array
    {
        $username = $args->offsetGet('username');
        // Add your logic on how to fetch your data (e.g database calls).
        // $args can be an object passed in your Query.types.yaml and contains arguments of your query.
        return array_map(function (array $anime) {
            return Anime::fromApi($anime);
        }, $this->client->userAnimelist($username));
    }

}
