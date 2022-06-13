<?php

namespace App\Utils;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class BaseRepository extends ServiceEntityRepository
{
    public function persist($entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}