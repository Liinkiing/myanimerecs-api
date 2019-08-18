<?php


namespace App\Command;


use App\Helper\GenrePersister;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppScrapMyAnimeListGenresCommand extends Command
{
    private const MAX_TO_FETCH = 50;
    protected static $defaultName = 'app:scrap:myanimelist:genres';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $em;
    private $genrePersister;

    public function __construct(
        EntityManagerInterface $em,
        GenrePersister $genrePersister
    )
    {
        $this->em = $em;
        $this->genrePersister = $genrePersister;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Scrap MyAnimeList genres by using Jikan unofficial API.')
            ->addArgument('ids', InputArgument::OPTIONAL, 'A commat separated list of ids to scrap')
            ->addOption('startMalId', 's', InputOption::VALUE_REQUIRED, 'Starts scrapping from the MyAnimeList ID', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $ids = $input->getArgument('ids') ?
            array_map(static function (string $id) {
                return (int)$id;
            }, explode(',', trim($input->getArgument('ids'))))
            : null;

        $iterations = 0;
        if ($ids && count($ids) > 0) {
            foreach ($ids as $malId) {
                if ($genre = $this->genrePersister->addAnimeGenre($malId)) {
                    $this->em->persist($genre);
                }
                ++$iterations;
            }
        } else {
            for ($malId = (int)$input->getOption('startMalId'); $iterations <= self::MAX_TO_FETCH; $malId++) {
                if ($genre = $this->genrePersister->addAnimeGenre($malId)) {
                    $this->em->persist($genre);
                }
                ++$iterations;
            }
        }

        $this->em->flush();
        $this->io->success('Successfully scrapped MyAnimeList genres!');

        return 0;
    }

}
