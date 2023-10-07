<?php

namespace App\Repository;

use App\Entity\DellinTerminal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DellinTerminal>
 *
 * @method DellinTerminal|null find($id, $lockMode = null, $lockVersion = null)
 * @method DellinTerminal|null findOneBy(array $criteria, array $orderBy = null)
 * @method DellinTerminal[]    findAll()
 * @method DellinTerminal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DellinTerminalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DellinTerminal::class);
    }

    public function save(DellinTerminal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DellinTerminal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DellinTerminal[] Returns an array of DellinTerminal objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DellinTerminal
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
