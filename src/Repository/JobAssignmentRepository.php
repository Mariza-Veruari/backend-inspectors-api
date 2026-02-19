<?php

namespace App\Repository;

use App\Entity\JobAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** Job assignments; one per job (unique job_id). */
/**
 * @extends ServiceEntityRepository<JobAssignment>
 */
class JobAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobAssignment::class);
    }
}
