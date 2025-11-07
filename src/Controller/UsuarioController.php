<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioBuscarType;
use App\Form\UsuarioCrearType;
use App\Form\UsuarioEditarType;
use App\Form\UsuarioPerfilType;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/usuario')]
class UsuarioController extends AbstractController
{
    #[Route('/buscar', name: 'usuario_buscar')]
    public function buscar(Request $request, UsuarioRepository $usuarioRepository, PaginatorInterface $paginator): Response
    {
        $usuario = new Usuario();

        $formulario = $this->createForm(UsuarioBuscarType::class, $usuario, [
            'action' => $this->generateUrl('usuario_buscar'),
            'method' => 'GET',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $usuarios = $usuarioRepository->encontrarUsuarios($usuario);

            $paginacion = $paginator->paginate(
                $usuarios,
                $request->query->getInt('p', 1),
                20
            );

            return $this->render('usuario/resultadosBusqueda.html.twig', [
                'paginacion' => $paginacion,
            ]);
        }

        return $this->render('usuario/buscar.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }

    #[Route('/ver/{id}', name: 'usuario_ver')]
    public function ver(int $id, UsuarioRepository $usuarioRepository): Response
    {
        $usuario = $usuarioRepository->find($id);

        if (!$usuario instanceof Usuario) {
            throw $this->createNotFoundException('No se encontró el usuario con id ' . $id);
        }

        return $this->render('usuario/ver.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    #[Route('/crear', name: 'usuario_crear')]
    public function crear(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $usuario = new Usuario();

        $formulario = $this->createForm(UsuarioCrearType::class, $usuario, [
            'action' => $this->generateUrl('usuario_crear'),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Hash de la contraseña
            $plainPassword = $formulario->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($usuario, $plainPassword);
                $usuario->setPassword($hashedPassword);
            }

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('notice', 'usuario.mensaje.usuario_creado');

            return $this->redirectToRoute('usuario_ver', [
                'id' => $usuario->getId(),
            ]);
        }

        return $this->render('usuario/crear.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }

    #[Route('/editar/{id}', name: 'usuario_editar')]
    public function editar(int $id, Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $usuario = $usuarioRepository->find($id);

        if (!$usuario instanceof Usuario) {
            throw $this->createNotFoundException('No se encontró el usuario con id ' . $id);
        }

        $formulario = $this->createForm(UsuarioEditarType::class, $usuario, [
            'action' => $this->generateUrl('usuario_editar', [
                'id' => $id,
            ]),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Hash de la contraseña solo si se proporcionó una nueva
            $plainPassword = $formulario->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($usuario, $plainPassword);
                $usuario->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('notice', 'usuario.mensaje.usuario_modificado');

            return $this->redirectToRoute('usuario_ver', [
                'id' => $usuario->getId(),
            ]);
        }

        return $this->render('usuario/editar.html.twig', [
            'formulario' => $formulario->createView(),
            'usuario' => $usuario,
        ]);
    }

    #[Route('/perfil', name: 'usuario_perfil')]
    public function perfil(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $usuario = $this->getUser();

        $formulario = $this->createForm(UsuarioPerfilType::class, $usuario, [
            'action' => $this->generateUrl('usuario_perfil'),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Hash de la contraseña solo si se proporcionó una nueva
            $plainPassword = $formulario->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($usuario, $plainPassword);
                $usuario->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('notice', 'usuario.mensaje.perfil_modificado');

            return $this->redirectToRoute('usuario_perfil');
        }

        return $this->render('usuario/perfil.html.twig', [
            'formulario' => $formulario->createView(),
            'usuario' => $usuario,
        ]);
    }

    #[Route('/eliminar/{id}', name: 'usuario_eliminar')]
    public function eliminar(int $id): Response
    {
        return $this->render('usuario/eliminar.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/eliminar-final/{id}', name: 'usuario_eliminar_final')]
    public function eliminarFinal(int $id, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): Response
    {
        $usuario = $usuarioRepository->find($id);

        if ($usuario instanceof Usuario) {
            $entityManager->remove($usuario);
            $entityManager->flush();

            $this->addFlash('notice', 'usuario.mensaje.usuario_eliminado');
        }

        return $this->redirectToRoute('usuario_buscar');
    }
}
