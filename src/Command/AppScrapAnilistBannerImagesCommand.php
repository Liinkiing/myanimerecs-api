<?php


namespace App\Command;


use App\Entity\Anime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppScrapAnilistBannerImagesCommand extends Command
{
    protected static $defaultName = 'app:scrap:anilist:banner-images';

    public const ANILIST_GRAPHQL_ENDPOINT = 'https://graphql.anilist.co/';

    private const BATCH_SIZE = 30;

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add BannerImages from Anilist to animes entities.');
    }

    protected function execute(InputInterface $input, OutputInterface $output, ?int $lastMalId = null)
    {
        try {
            $this->io = new SymfonyStyle($input, $output);
            $query = $this->em->createQuery('select a from ' . Anime::class . ' a where a.malId IN (:ids) ORDER BY FIELD(a.malId, :ids)');
            $ids = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'output.txt');
            $ids = explode("\n", $ids);
            $ids = array_filter($ids, static function(string $id) {
                return $id !== '';
            });
            $ids = array_map(static function(string $id) {
                return (int) $id;
            }, $ids);
            if ($lastMalId) {
                echo 'STARTING FROM LAST MAL ID '. $lastMalId;
                $offset = array_search($lastMalId, $ids, true);
                $ids = array_splice($ids, $offset);
            }
            //shuffle($ids);
            $query->setParameter('ids', $ids);
            $iterable = $query->iterate();
            $count = 0;
            foreach ($iterable as $row) {
                /** @var Anime $anime */
                $anime = $row[0];
                $lastMalId = $anime->getMalId();
                echo $anime->getMalId() . "\n";
                $media = $this->graphql_query(
                    self::ANILIST_GRAPHQL_ENDPOINT,
                    $this->getMediaGraphQLQuery(),
                    [
                        'malId' => $anime->getMalId()
                    ]
                );
                if ($media['data'] && $media['data']['Media'] && $media['data']['Media']['bannerImage'] && $anime->getBannerImageUrl() !== $media['data']['Media']['bannerImage']) {
                    $anime->setBannerImageUrl($media['data']['Media']['bannerImage']);
                    echo 'NEW URL: ' .$anime->getBannerImageUrl() ;
                }
                echo ++$count . "\n";
                if ($count % self::BATCH_SIZE === 0) {
                    echo "Flushing \n";
                    $this->em->flush();
                    $this->em->clear();
                }
            }

            $this->em->flush();
            $this->em->clear();

            $this->io->success('Successfully added banner images from Anilist!');
            return 0;
        } catch (\Exception $ex) {
            $this->em->flush();
            $this->em->clear();
            dump($ex->getMessage());
            dump($ex->getCode());
            echo 'LAST MAL ID: ' . $lastMalId;
            echo 'EXCEPTION OCCURED, REFORCING';
            if ($ex->getCode() !== 2) {
                return $this->execute($input, $output, $lastMalId);
            }
            if ($ex->getCode() === 2) {
                if ($lastMalId) {
                    $offset = array_search($lastMalId, $ids, true);
                    $afterLastMalIds = array_splice($ids, $offset + 1);
                    $afterLastMalId = count($afterLastMalIds) > 0 ? $afterLastMalIds[0] : null;
                    return $this->execute($input, $output, $afterLastMalId);
                }
            }
        }

    }

    private function graphql_query(string $endpoint, string $query, array $variables = [], ?string $token = null): array
    {
        $headers = ['Content-Type: application/json', 'User-Agent: Dunglas\'s minimal GraphQL client'];
        if (null !== $token) {
            $headers[] = "Authorization: bearer $token";
        }
        if (false === $data = @file_get_contents($endpoint, false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => $headers,
                    'content' => json_encode(['query' => $query, 'variables' => $variables]),
                ]
            ]))) {
            $error = error_get_last();
            throw new \ErrorException($error['message'], $error['type']);
        }
        return json_decode($data, true);
    }

    private function getMediaGraphQLQuery(): string
    {
        return <<<'GRAPHQL'
query GetMedia($malId: Int!) {
    Media(idMal: $malId) {
        id
        bannerImage
      }
}   
GRAPHQL;

    }
}
