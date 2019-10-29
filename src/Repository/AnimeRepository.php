<?php

namespace App\Repository;

use App\Entity\Anime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Anime|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anime|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anime[]    findAll()
 * @method Anime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnimeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Anime::class);
    }

    /**
     * @param array $malIds
     * @return Anime[]|Collection<int, Anime>
     */
    public function findByAnimeList(array $malIds): Collection
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->select('a', 'recommendations', 'recommended')
            ->leftJoin(
                'a.recommendations', 'recommendations'
            )
            ->leftJoin('recommendations.recommended', 'recommended')
            ->andWhere(
                $qb->expr()->in('a.malId', ':ids')
            )
            ->addOrderBy(
                'FIELD(a.malId, :ids)'
            )
            ->setParameter('ids', $malIds)
            ->getQuery();

        return new ArrayCollection($query->getResult());
    }


    /**
     * @param array $malIds
     * @return Anime[]|Collection<int, Anime>
     */
    public function findByOrderedMalIds(array $malIds, int $limit = 10, int $offset = 0): Collection
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->andWhere(
                $qb->expr()->in('a.malId', ':malIds')
            )
            ->addOrderBy(
                'FIELD(a.malId, :malIds)'
            )
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->setParameter('malIds', $malIds)
            ->getQuery();

        return new ArrayCollection($query->getResult());
    }
}
