<?php

namespace App\Repository;

use App\Entity\Job;
use App\Enum\JobStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Jobs with optional filter by status (OPEN, ASSIGNED, COMPLETED).
 * @extends ServiceEntityRepository<Job>
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    /**
     * @param JobStatus|null $status
     * @return Job[]
     */
    public function findByStatus(?JobStatus $status): array
    {
        $qb = $this->createQueryBuilder('j');
        
        if ($status !== null) {
            $qb->where('j.status = :status')
               ->setParameter('status', $status);
        }
        
        return $qb->orderBy('j.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}
