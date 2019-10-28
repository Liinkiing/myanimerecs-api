<?php

namespace App\Command;

use App\Client\TVDBClient;
use App\Entity\Anime;
use App\Model\TVDBImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class ScrapTvdbCommand extends Command
{

    private const BATCH_SIZE = 100;

    private $client;
    private $em;

    public function __construct(TVDBClient $client, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->client = $client;
        $this->em = $em;
    }

    protected static $defaultName = 'app:scrap-tvdb';

    protected function configure(): void
    {
        $this
            ->setDescription('Scraps TVDB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $i = 0;
        $q = $this->em->createQuery('SELECT a FROM ' . Anime::class . ' a');
        $iterableResult = $q->iterate();
        foreach ($iterableResult as $row) {
            /** @var Anime $anime */
            $anime = $row[0];
            echo $i . ' - ' . $anime->getTitle() . "\n";
            if (!$anime->getPosterImageUrl() && $posterUrl = $this->getBestPoster($anime->getTitle())) {
                echo 'Found best poster for anime ' .
                    $anime->getTitle() . ' : ' . $posterUrl->getPublicPath() .
                    " ({$posterUrl->getRating()} - {$posterUrl->getResolution()}) " . "\n";
                $anime->setPosterImageUrl($posterUrl->getPublicPath());
            }
            if (!$anime->getBackgroundImageUrl() && $fanartUrl = $this->getBestFanart($anime->getTitle())) {
                echo 'Found best fanart for anime ' .
                    $anime->getTitle() . ' : ' . $fanartUrl->getPublicPath() .
                    " ({$fanartUrl->getRating()} - {$fanartUrl->getResolution()}) " . "\n";
                $anime->setBackgroundImageUrl($fanartUrl->getPublicPath());
            }
            if (($i % self::BATCH_SIZE) === 0) {
                echo "BATCHING\n";
                $this->em->flush();
                $this->em->clear();
            }
            ++$i;
        }
        $this->em->flush();

        exit;
    }

    private function getBestFanart(string $serie): ?TVDBImage
    {
        try {
            $fanarts = collect(array_map(static function (array $apiImage) {
                return TVDBImage::fromApi($apiImage);
            }, $this->client->fetchSerieFanarts($serie)));

            return $fanarts->sortByDesc(static function (TVDBImage $image) {
                return $image->getRating();
            })->first();
        } catch (ClientExceptionInterface $exception) {
            echo "\n{$exception->getMessage()}\n";
            return null;
        }
    }

    private function getBestPoster(string $serie): ?TVDBImage
    {
        try {
            $posters = collect(array_map(static function (array $apiImage) {
                return TVDBImage::fromApi($apiImage);
            }, $this->client->fetchSeriePosters($serie)));

            return $posters->sortByDesc(static function (TVDBImage $image) {
                return $image->getRating();
            })->first();
        } catch (ClientExceptionInterface $exception) {
            echo "\n{$exception->getMessage()}\n";
            return null;
        }
    }

}
