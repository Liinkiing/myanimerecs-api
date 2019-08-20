<?php


namespace App\MessageHandler;


use App\Helper\AnimePersister;
use App\Message\AddInexistingAnimes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddInexistingAnimesHandler implements MessageHandlerInterface
{
    private $logger;
    private $persister;
    private $em;

    public function __construct(LoggerInterface $logger, AnimePersister $persister, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->persister = $persister;
        $this->em = $em;
    }

    public function __invoke(AddInexistingAnimes $message)
    {
        $ids = $message->getInexistingMalIds();

        $this->logger->info('Fetching new animes from MyAnimeList: ' . implode(', ', $ids));
        foreach ($ids as $malId) {
            $this->persister->addMyAnimeListAnime($malId);
            $this->em->flush();
        }
        $this->logger->info('Finished fetching animes and successfully persisted them in db.');
    }
}
