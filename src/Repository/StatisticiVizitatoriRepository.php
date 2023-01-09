<?php

namespace App\Repository;

use App\Entity\StatisticiVizitatori;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatisticiVizitatori>
 *
 * @method StatisticiVizitatori|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatisticiVizitatori|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatisticiVizitatori[]    findAll()
 * @method StatisticiVizitatori[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatisticiVizitatoriRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatisticiVizitatori::class);
    }

    public function save(StatisticiVizitatori $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StatisticiVizitatori $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return StatisticiVizitatori[] Returns an array of StatisticiVizitatori objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StatisticiVizitatori
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
