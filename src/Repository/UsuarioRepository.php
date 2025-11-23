<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (! $user instanceof Usuario) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function encontrarUsuarios(Usuario $usuario)
    {
        $queryBuilder = $this->createQueryBuilder('u');

        if ($usuario->getUsuario()) {
            $queryBuilder->andWhere('u.usuario LIKE :usuario')
                ->setParameter('usuario', '%' . $usuario->getUsuario() . '%');
        }

        if ($usuario->getNombre()) {
            $queryBuilder->andWhere('u.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $usuario->getNombre() . '%');
        }

        if ($usuario->getOrganizacion()) {
            $queryBuilder->andWhere('u.organizacion LIKE :organizacion')
                ->setParameter('organizacion', '%' . $usuario->getOrganizacion() . '%');
        }

        if ($usuario->getEmail()) {
            $queryBuilder->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $usuario->getEmail() . '%');
        }

        $queryBuilder->orderBy('u.usuario', 'ASC');

        return $queryBuilder->getQuery();
    }
}
