<?php

namespace App\Repository;

use App\Entity\AbcpOrderCustomFieldsEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbcpOrderCustomFieldsEntity>
 *
 * @method AbcpOrderCustomFieldsEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbcpOrderCustomFieldsEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbcpOrderCustomFieldsEntity[]    findAll()
 * @method AbcpOrderCustomFieldsEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbcpOrderCustomFieldsEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbcpOrderCustomFieldsEntity::class);
    }

    public function save(AbcpOrderCustomFieldsEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AbcpOrderCustomFieldsEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AbcpOrderCustomFieldsEntity[] Returns an array of AbcpOrderCustomFieldsEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AbcpOrderCustomFieldsEntity
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
