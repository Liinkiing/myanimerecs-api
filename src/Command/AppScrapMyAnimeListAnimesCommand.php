<?php


namespace App\Command;


use App\Entity\Anime;
use App\Entity\Genre;
use App\Entity\Recommendation;
use App\Entity\Recommendation as RecommendationEntity;
use App\Helper\AnimePersister;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppScrapMyAnimeListAnimesCommand extends Command
{
    protected static $defaultName = 'app:scrap:myanimelist:animes';

    private const MAX_TO_FETCH = 10000;
    private const PERSIST_BATCH_NUMBER = 1;

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $em;
    private $animePersister;

    public function __construct(
        EntityManagerInterface $em,
        AnimePersister $animePersister
    )
    {
        $this->em = $em;
        $this->animePersister = $animePersister;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Scrap MyAnimeList by using Jikan unofficial API.')
            ->addArgument('ids', InputArgument::OPTIONAL, 'A commat separated list of ids to scrap')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force re-adding animes recommendations')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Update exisiting animes')
            ->addOption('startMalId', 's', InputOption::VALUE_REQUIRED, 'Starts scrapping from the MyAnimeList ID', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        $update = $input->getOption('update');
        $ids = $input->getArgument('ids') ?
            array_map(static function (string $id) {
                return (int)$id;
            }, explode(',', trim($input->getArgument('ids'))))
            : null;
        $iterations = 0;
        if ($ids && count($ids) > 0) {
            foreach ($ids as $malId) {
                if ($iterations % self::PERSIST_BATCH_NUMBER === 0) {
                    $this->persistInDatabase();
                }
                $this->animePersister->addMyAnimeListAnime($malId, $this->io, $force, $update);
                ++$iterations;
            }
        } else {
            for ($malId = (int)$input->getOption('startMalId'); $iterations <= self::MAX_TO_FETCH; $malId++) {
                if ($iterations % self::PERSIST_BATCH_NUMBER === 0) {
                    $this->persistInDatabase();
                }
                $this->animePersister->addMyAnimeListAnime($malId, $this->io, $force, $update);
                ++$iterations;
            }
        }

        $this->io->success('Successfully scrapped MyAnimeList!');

        return 0;
    }

    protected function persistInDatabase(): void
    {
        //$this->io->writeln('<info>Persisting entities in db...</info>');
        $this->em->flush();
        $this->em->clear(Anime::class);
        $this->em->clear(RecommendationEntity::class);
        $this->em->clear(Genre::class);
        //$this->io->success('Successfully persisted entities in db');
    }

}
