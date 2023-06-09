<?php

namespace App\Repository;

use App\Entity\Quack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quack>
 *
 * @method Quack|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quack|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quack[]    findAll()
 * @method Quack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quack::class);
    }

    public function save(Quack $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Quack $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getParentQuacksOnly(): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.parent_id IS NULL')
            ->orderBy('q.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getQuacksAnswers(Quack $quack): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.parent_id = :parent_id')
            ->setParameter('parent_id', $quack->getId())
            ->orderBy('q.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
