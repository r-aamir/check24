<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Description of BaseRepository.
 */
abstract class BaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    protected function getTableName() : string
    {
        return $this
                ->getClassMetadata()
                ->getTableName();
    }
}
