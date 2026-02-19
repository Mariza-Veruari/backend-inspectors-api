<?php

namespace App\Repository;

use App\Entity\Auditor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** Auditors by id or email. */
/**
 * @extends ServiceEntityRepository<Auditor>
 */
class AuditorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auditor::class);
    }
}
