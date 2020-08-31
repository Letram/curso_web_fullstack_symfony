<?php

namespace App\Repository;

use App\Entity\UserRegister;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserRegister|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRegister|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRegister[]    findAll()
 * @method UserRegister[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRegisterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRegister::class);
    }

    // /**
    //  * @return UserRegister[] Returns an array of UserRegister objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserRegister
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
