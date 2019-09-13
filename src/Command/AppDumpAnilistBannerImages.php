<?php


namespace App\Command;


use App\Entity\Anime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AppDumpAnilistBannerImages extends Command
{
    protected static $defaultName = 'app:dump:anilist:banner-images';

    private const REQUEST_BATCH = 300;

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $em;
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $client
    )
    {
        $this->em = $em;
        $this->client = $client;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add BannerImages from Anilist to animes entities.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $outputPath = __DIR__ . DIRECTORY_SEPARATOR . 'output.txt';
        $query = $this->em->createQuery('select a from ' . Anime::class . ' a');
        $iterable = $query->iterate();
        $count = 0;
        /** @var ResponseInterface[] $responses */
        $responses = [];
        $notFounds = [];
        foreach ($iterable as $row) {
            /** @var Anime $anime */
            $anime = $row[0];
            echo $anime->getMalId() . "\n";
            echo ++$count . "\n";
            try {
                $responses[$anime->getMalId()] = $this->client->request('GET', $anime->getBannerImageUrl());
                if ($count % self::REQUEST_BATCH === 0) {
                    foreach ($responses as $malId => $response) {
                        try {
                            $response->getContent();
                        } catch (ClientExceptionInterface $exception) {
                            if ($exception->getCode() === 404) {
                                $url = $exception->getResponse()->getInfo('url');
                                echo 'NOT FOUND FOR ' . $url . "\n";
                                $notFounds[] = $malId;
                            }
                        }
                    }
                    $responses = [];
                }
            } catch (ClientExceptionInterface $exception) {
                // fail silently
            }
        }

        file_put_contents($outputPath, array_reduce(
            $notFounds,
            static function (string $acc, int $malId) {
                $acc .= $malId;
                $acc .= "\n";
                return $acc;
            },
            ''
        ));

        $this->io->success("Successfully dumped not found banners images animes IDs in file $outputPath");
        return 0;
    }
}
