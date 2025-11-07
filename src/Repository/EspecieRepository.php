<?php

namespace App\Repository;

use App\Entity\Especie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Especie>
 */
class EspecieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Especie::class);
    }

    /**
     * Lista todas las especies ordenadas por nombre
     */
    public function listarEspecies(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra especies por nombre científico y/o común
     */
    public function encontrarEspecies(Especie $especie): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($especie->getNombre()) {
            $qb->andWhere('e.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $especie->getNombre() . '%');
        }

        if ($especie->getComun()) {
            $qb->andWhere('e.comun LIKE :comun')
                ->setParameter('comun', '%' . $especie->getComun() . '%');
        }

        return $qb->orderBy('e.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
