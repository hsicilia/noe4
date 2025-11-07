<?php

namespace App\Controller;

use App\Entity\Captura;
use App\Entity\Ejemplar;
use App\Form\CapturaCrearType;
use App\Form\CapturaEditarType;
use App\Repository\CapturaRepository;
use App\Repository\EjemplarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/captura')]
class CapturaController extends AbstractController
{
    public function __construct(
        private readonly DataManager $dataManager,
        private readonly FilterManager $filterManager,
        private readonly CacheManager $cacheManager,
    ) {
    }

    #[Route('/listar/{id}', name: 'captura_listar')]
    public function listar(int $id, EjemplarRepository $ejemplarRepository, CapturaRepository $capturaRepository): Response
    {
        $ejemplar = $ejemplarRepository->find($id);

        if (!$ejemplar instanceof Ejemplar) {
            throw $this->createNotFoundException('No se encontró el ejemplar con id ' . $id);
        }

        $numCapturas = $ejemplarRepository->numCapturas($id);
        $capturas = $capturaRepository->findBy(
            [
                'ejemplar' => $id,
            ],
            [
                'fechaCaptura' => 'DESC',
                'horaCaptura' => 'DESC',
            ]
        );

        return $this->render('captura/listado.html.twig', [
            'ejemplar' => $ejemplar,
            'num_capturas' => $numCapturas,
            'capturas' => $capturas,
        ]);
    }

    #[Route('/ver/{id_ejemplar}/{id_captura}', name: 'captura_ver')]
    public function ver(int $id_ejemplar, int $id_captura, EjemplarRepository $ejemplarRepository, CapturaRepository $capturaRepository): Response
    {
        $captura = $capturaRepository->find($id_captura);

        if (!$captura instanceof Captura) {
            throw $this->createNotFoundException('No se encontró la incidencia con id ' . $id_captura);
        }

        $ejemplar = $ejemplarRepository->find($id_ejemplar);
        $numCapturas = $ejemplarRepository->numCapturas($id_ejemplar);

        return $this->render('captura/ver.html.twig', [
            'captura' => $captura,
            'ejemplar' => $ejemplar,
            'num_capturas' => $numCapturas,
            'creado_por' => $captura->getCreadoPor(),
            'creado_el' => $captura->getCreadoEl(),
            'modificado_por' => $captura->getModificadoPor(),
            'modificado_el' => $captura->getModificadoEl(),
        ]);
    }

    #[Route('/crear/{id}', name: 'captura_crear')]
    public function crear(int $id, Request $request, EjemplarRepository $ejemplarRepository, EntityManagerInterface $entityManager): Response
    {
        $ejemplar = $ejemplarRepository->find($id);

        if (!$ejemplar instanceof Ejemplar) {
            throw $this->createNotFoundException('No se encontró el ejemplar con id ' . $id);
        }

        $captura = new Captura();

        // Establecer valores por defecto
        $captura->setFechaCaptura(new \DateTime());
        $captura->setHoraCaptura(new \DateTime());
        $captura->setLugarCaptura($this->getUser()->getLugarDefecto());

        $formulario = $this->createForm(CapturaCrearType::class, $captura, [
            'action' => $this->generateUrl('captura_crear', [
                'id' => $id,
            ]),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Añadir el ejemplar
            $captura->setEjemplar($ejemplar);

            // Añadir el usuario actual como creador y modificador
            $captura->setCreadoPor($this->getUser());
            $captura->setModificadoPor($this->getUser());

            // Procesar imagen
            $imagen = $formulario->get('imagen')->getData();

            $entityManager->persist($captura);
            $entityManager->flush();

            // Guardar imagen después del flush para tener el ID
            if ($imagen) {
                $this->guardarImagen($captura, $imagen);
                $entityManager->flush();
            }

            $this->addFlash('notice', 'captura.mensaje.captura_creada');

            return $this->redirectToRoute('captura_listar', [
                'id' => $id,
            ]);
        }

        $numCapturas = $ejemplarRepository->numCapturas($id);

        return $this->render('captura/crear.html.twig', [
            'formulario' => $formulario->createView(),
            'ejemplar' => $ejemplar,
            'num_capturas' => $numCapturas,
        ]);
    }

    #[Route('/editar/{id_ejemplar}/{id_captura}', name: 'captura_editar')]
    public function editar(int $id_ejemplar, int $id_captura, Request $request, EjemplarRepository $ejemplarRepository, CapturaRepository $capturaRepository, EntityManagerInterface $entityManager): Response
    {
        $captura = $capturaRepository->find($id_captura);

        if (!$captura instanceof Captura) {
            throw $this->createNotFoundException('No se encontró la incidencia con id ' . $id_captura);
        }

        $formulario = $this->createForm(CapturaEditarType::class, $captura, [
            'action' => $this->generateUrl('captura_editar', [
                'id_ejemplar' => $id_ejemplar,
                'id_captura' => $id_captura,
            ]),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Añadir el usuario actual como modificador
            $captura->setModificadoPor($this->getUser());

            // Procesar imagen
            $imagen = $formulario->get('imagen')->getData();
            $borrarImagen = $formulario->get('borrarImagen')->getData();

            // Borrar imagen si se marcó el checkbox
            if ($borrarImagen && ! $imagen) {
                $this->borrarImagen($captura);
                $captura->setPath(null);
            }

            // Guardar nueva imagen
            if ($imagen) {
                $this->borrarImagen($captura); // Borrar anterior si existe
                $this->guardarImagen($captura, $imagen);
            }

            $entityManager->flush();

            $this->addFlash('notice', 'captura.mensaje.captura_modificada');

            return $this->redirectToRoute('captura_listar', [
                'id' => $id_ejemplar,
            ]);
        }

        $ejemplar = $ejemplarRepository->find($id_ejemplar);
        $numCapturas = $ejemplarRepository->numCapturas($id_ejemplar);

        return $this->render('captura/editar.html.twig', [
            'formulario' => $formulario->createView(),
            'captura' => $captura,
            'ejemplar' => $ejemplar,
            'num_capturas' => $numCapturas,
            'creado_por' => $captura->getCreadoPor(),
            'creado_el' => $captura->getCreadoEl(),
            'modificado_por' => $captura->getModificadoPor(),
            'modificado_el' => $captura->getModificadoEl(),
        ]);
    }

    #[Route('/eliminar/{id_ejemplar}/{id_captura}', name: 'captura_eliminar')]
    public function eliminar(int $id_ejemplar, int $id_captura): Response
    {
        return $this->render('captura/eliminar.html.twig', [
            'id_ejemplar' => $id_ejemplar,
            'id_captura' => $id_captura,
        ]);
    }

    #[Route('/eliminar-final/{id_ejemplar}/{id_captura}', name: 'captura_eliminar_final')]
    public function eliminarFinal(int $id_ejemplar, int $id_captura, CapturaRepository $capturaRepository, EntityManagerInterface $entityManager): Response
    {
        $captura = $capturaRepository->find($id_captura);

        if ($captura instanceof Captura) {
            $this->borrarImagen($captura);
            $entityManager->remove($captura);
            $entityManager->flush();

            $this->addFlash('notice', 'captura.mensaje.captura_eliminada');
        }

        return $this->redirectToRoute('captura_listar', [
            'id' => $id_ejemplar,
        ]);
    }

    private function guardarImagen(Captura $captura, $archivo): void
    {
        // Obtener la extensión ANTES de mover el archivo
        $extension = $archivo->guessExtension();
        $nombreArchivo = 'C-' . $captura->getId() . '.' . $extension;
        $directorioCapturas = $this->getParameter('capturas_directory');

        $archivo->move($directorioCapturas, $nombreArchivo);

        // Redimensionar la imagen
        $this->redimensionarImagen($captura, $extension);

        // Actualizar el path en la entidad
        $captura->setPath($extension);
    }

    private function redimensionarImagen(Captura $captura, string $extension): void
    {
        $nombreArchivo = 'C-' . $captura->getId() . '.' . $extension;
        $directorioCapturas = $this->getParameter('capturas_directory');
        $rutaOriginal = $directorioCapturas . '/' . $nombreArchivo;
        $rutaRedimensionada = $rutaOriginal . '.red';

        // Aplicar el filtro 'recorte' para redimensionar
        $webPath = 'uploads/capturas/' . $nombreArchivo;
        $binary = $this->dataManager->find('recorte', $webPath);
        $response = $this->filterManager->applyFilter($binary, 'recorte');

        // Guardar la imagen redimensionada
        file_put_contents($rutaRedimensionada, $response->getContent());

        // Reemplazar la original con la redimensionada
        unlink($rutaOriginal);
        rename($rutaRedimensionada, $rutaOriginal);

        // Borrar cache de miniaturas si existe
        $this->cacheManager->remove($webPath, 'thumbnail');
    }

    private function borrarImagen(Captura $captura): void
    {
        $path = $captura->getPath();

        if ($path) {
            $nombreArchivo = 'C-' . $captura->getId() . '.' . $path;
            $rutaCompleta = $this->getParameter('capturas_directory') . '/' . $nombreArchivo;

            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }

            // Borrar cache de miniaturas
            $webPath = 'uploads/capturas/' . $nombreArchivo;
            $this->cacheManager->remove($webPath, 'thumbnail');
            $this->cacheManager->remove($webPath, 'recorte');
        }
    }
}
