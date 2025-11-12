<?php

namespace App\Command;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:hash-passwords',
    description: 'Convierte las contraseñas de texto plano a formato hash seguro',
)]
class HashPasswordsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migrando contraseñas a formato hash seguro');

        // Obtener todos los usuarios
        $usuarios = $this->entityManager->getRepository(Usuario::class)->findAll();

        if (empty($usuarios)) {
            $io->warning('No se encontraron usuarios en la base de datos.');
            return Command::SUCCESS;
        }

        $io->progressStart(count($usuarios));

        $hashedCount = 0;
        $skippedCount = 0;

        foreach ($usuarios as $usuario) {
            $plainPassword = $usuario->getPassword();

            // Verificar si la contraseña ya está hasheada
            // Los hashes bcrypt empiezan con $2y$ y tienen 60+ caracteres
            // Los hashes sodium empiezan con $argon2
            if (str_starts_with($plainPassword, '$2') || str_starts_with($plainPassword, '$argon2')) {
                $io->note(sprintf('Usuario "%s" ya tiene contraseña hasheada, omitiendo...', $usuario->getUsuario()));
                $skippedCount++;
                $io->progressAdvance();
                continue;
            }

            // Hashear la contraseña
            $hashedPassword = $this->passwordHasher->hashPassword($usuario, $plainPassword);
            $usuario->setPassword($hashedPassword);

            // Limpiar el salt (ya no se usa en Symfony moderno)
            $usuario->setSalt('');

            $hashedCount++;
            $io->progressAdvance();
        }

        $io->progressFinish();

        // Guardar todos los cambios
        $this->entityManager->flush();

        $io->success([
            sprintf('Migración completada: %d contraseñas hasheadas, %d omitidas.', $hashedCount, $skippedCount),
            'Las contraseñas ahora están en formato seguro.'
        ]);

        return Command::SUCCESS;
    }
}
