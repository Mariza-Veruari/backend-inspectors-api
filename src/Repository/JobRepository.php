<?php

namespace App\Repository;

use App\Entity\Job;
use App\Entity\JobStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    /**
     * @return Job[]
     */
    public function findOpenJobs(): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.status = :status')
            ->setParameter('status', JobStatus::OPEN)
            ->orderBy('j.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
