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
        $queryBuilder = $this->createQueryBuilder('e');

        if ($especie->getNombre()) {
            $queryBuilder->andWhere('e.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $especie->getNombre() . '%');
        }

        if ($especie->getComun()) {
            $queryBuilder->andWhere('e.comun LIKE :comun')
                ->setParameter('comun', '%' . $especie->getComun() . '%');
        }

        if ($especie->getInvasora() !== null) {
            $queryBuilder->andWhere('e.invasora = :invasora')
                ->setParameter('invasora', $especie->getInvasora());
        }

        if ($especie->getCites() !== null) {
            $queryBuilder->andWhere('e.cites = :cites')
                ->setParameter('cites', $especie->getCites());
        }

        if ($especie->getPeligroso() !== null) {
            $queryBuilder->andWhere('e.peligroso = :peligroso')
                ->setParameter('peligroso', $especie->getPeligroso());
        }

        return $queryBuilder->orderBy('e.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
