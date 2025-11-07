<?php

namespace App\Controller;

use App\Entity\Especie;
use App\Form\EspecieType;
use App\Repository\EspecieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/especie')]
class EspecieController extends AbstractController
{
    #[Route('/crear', name: 'especie_crear')]
    public function crear(Request $request, EntityManagerInterface $entityManager): Response
    {
        $especie = new Especie();

        $formulario = $this->createForm(EspecieType::class, $especie, [
            'action' => $this->generateUrl('especie_crear'),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $entityManager->persist($especie);
            $entityManager->flush();

            $this->addFlash('notice', 'especie.mensaje.especie_creada');

            return $this->redirectToRoute('especie_ver', [
                'id' => $especie->getId(),
            ]);
        }

        return $this->render('especie/crear.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }

    #[Route('/editar/{id}', name: 'especie_editar')]
    public function editar(int $id, Request $request, EspecieRepository $especieRepository, EntityManagerInterface $entityManager): Response
    {
        $especie = $especieRepository->find($id);

        if (!$especie instanceof Especie) {
            throw $this->createNotFoundException('No se encontró la especie con id ' . $id);
        }

        $formulario = $this->createForm(EspecieType::class, $especie, [
            'action' => $this->generateUrl('especie_editar', [
                'id' => $id,
            ]),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'especie.mensaje.especie_modificada');

            return $this->redirectToRoute('especie_buscar');
        }

        return $this->render('especie/editar.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }

    #[Route('/ver/{id}', name: 'especie_ver')]
    public function ver(int $id, EspecieRepository $especieRepository): Response
    {
        $especie = $especieRepository->find($id);

        if (!$especie instanceof Especie) {
            throw $this->createNotFoundException('No se encontró la especie con id ' . $id);
        }

        return $this->render('especie/ver.html.twig', [
            'especie' => $especie,
        ]);
    }

    #[Route('/eliminar/{id}', name: 'especie_eliminar')]
    public function eliminar(int $id): Response
    {
        return $this->render('especie/eliminar.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/eliminar-final/{id}', name: 'especie_eliminar_final')]
    public function eliminarFinal(int $id, EspecieRepository $especieRepository, EntityManagerInterface $entityManager): Response
    {
        $especie = $especieRepository->find($id);

        if ($especie instanceof Especie) {
            $entityManager->remove($especie);
            $entityManager->flush();

            $this->addFlash('notice', 'especie.mensaje.especie_eliminada');
        }

        return $this->redirectToRoute('especie_buscar');
    }

    #[Route('/buscar', name: 'especie_buscar')]
    public function buscar(Request $request, EspecieRepository $especieRepository, PaginatorInterface $paginator): Response
    {
        $especie = new Especie();

        $formulario = $this->createForm(EspecieType::class, $especie, [
            'action' => $this->generateUrl('especie_buscar'),
            'method' => 'GET',
            'required_fields' => false,
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Si presionó "Informe PDF" o "Informe CSV", redirigir al generador de informes
            if ($formulario->get('informePDF')->isClicked() || $formulario->get('informeCSV')->isClicked()) {
                $salida = $formulario->get('informePDF')->isClicked() ? 'PDF' : 'CSV';

                return $this->redirectToRoute('informe_especies_salida', [
                    'salida' => $salida,
                    'nombre' => $especie->getNombre(),
                    'comun' => $especie->getComun(),
                ]);
            }

            // Si presionó "Enviar", mostrar resultados paginados
            $resultados = $especieRepository->encontrarEspecies($especie);

            $paginacion = $paginator->paginate(
                $resultados,
                $request->query->getInt('p', 1),
                20 // resultados por página
            );

            return $this->render('especie/resultadosBusqueda.html.twig', [
                'paginacion' => $paginacion,
                'formulario' => $formulario->createView(),
            ]);
        }

        return $this->render('especie/buscar.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }
}
