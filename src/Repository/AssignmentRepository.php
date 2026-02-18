<?php

namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Inspector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assignment>
 */
class AssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assignment::class);
    }

    /**
     * @return Assignment[]
     */
    public function findByInspectorOrderByScheduledAt(Inspector $inspector): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.inspector = :inspector')
            ->setParameter('inspector', $inspector)
            ->orderBy('a.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
