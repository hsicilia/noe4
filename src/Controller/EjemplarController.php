<?php

namespace App\Controller;

use App\Entity\Ejemplar;
use App\Form\BusquedaMapaType;
use App\Form\EjemplarBuscarOtroIdType;
use App\Form\EjemplarCrearType;
use App\Form\EjemplarEditarType;
use App\Repository\EjemplarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ejemplar')]
class EjemplarController extends AbstractController
{
    public function __construct(
        private readonly DataManager $dataManager,
        private readonly FilterManager $filterManager,
        private readonly CacheManager $cacheManager,
    ) {
    }

    #[Route('/ver/{id}', name: 'ejemplar_ver')]
    public function ver(int $id, EjemplarRepository $ejemplarRepository): Response
    {
        $ejemplar = $ejemplarRepository->find($id);

        if (!$ejemplar instanceof Ejemplar) {
            throw $this->createNotFoundException('No se encontró el ejemplar con id ' . $id);
        }

        // Obtener información de capturas
        $numCapturas = $ejemplarRepository->numCapturas($id);
        $primeraCaptura = null;
        $ultimaCaptura = null;

        if ($numCapturas > 0) {
            $primeraCaptura = $ejemplarRepository->primeraCaptura($id);
            $ultimaCaptura = ($numCapturas === 1) ? null : $ejemplarRepository->ultimaCaptura($id);
        }

        return $this->render('ejemplar/ver.html.twig', [
            'ejemplar' => $ejemplar,
            'num_capturas' => $numCapturas,
            'primera_captura' => $primeraCaptura,
            'ultima_captura' => $ultimaCaptura,
            'creado_por' => $ejemplar->getCreadoPor(),
            'creado_el' => $ejemplar->getCreadoEl(),
            'modificado_por' => $ejemplar->getModificadoPor(),
            'modificado_el' => $ejemplar->getModificadoEl(),
        ]);
    }

    #[Route('/clonar/{id}', name: 'ejemplar_clonar')]
    public function clonar(int $id, EjemplarRepository $ejemplarRepository): Response
    {
        $ejemplarOriginal = $ejemplarRepository->find($id);

        if (!$ejemplarOriginal instanceof Ejemplar) {
            throw $this->createNotFoundException('No se encontró el ejemplar con id ' . $id);
        }

        // Crear un nuevo ejemplar copiando los datos del original
        $ejemplar = new Ejemplar();
        $ejemplar->setFechaRegistro(new \DateTime());
        $ejemplar->setEspecie($ejemplarOriginal->getEspecie());
        $ejemplar->setSexo($ejemplarOriginal->getSexo());
        $ejemplar->setOrigen($ejemplarOriginal->getOrigen());
        $ejemplar->setRecinto($ejemplarOriginal->getRecinto());
        $ejemplar->setLugar($ejemplarOriginal->getLugar());
        $ejemplar->setGeoLat($ejemplarOriginal->getGeoLat());
        $ejemplar->setGeoLong($ejemplarOriginal->getGeoLong());
        $ejemplar->setDocumentacion($ejemplarOriginal->getDocumentacion());
        $ejemplar->setProgenitor1($ejemplarOriginal->getProgenitor1());
        $ejemplar->setProgenitor2($ejemplarOriginal->getProgenitor2());
        $ejemplar->setDepositoNombre($ejemplarOriginal->getDepositoNombre());
        $ejemplar->setDepositoDNI($ejemplarOriginal->getDepositoDNI());
        $ejemplar->setDeposito($ejemplarOriginal->getDeposito());
        $ejemplar->setObservaciones($ejemplarOriginal->getObservaciones());
        $ejemplar->setInvasora($ejemplarOriginal->getInvasora());
        $ejemplar->setCites($ejemplarOriginal->getCites());
        $ejemplar->setPeligroso($ejemplarOriginal->getPeligroso());
        // No copiamos: idMicrochip, idAnilla, idOtro, idOtro2, fechaBaja, causaBaja
        // No copiamos: imágenes (path1, path2, path3)
        // No copiamos: capturas (se quedan vacías)

        // Redirigir al formulario de crear con el ejemplar clonado
        return $this->render('ejemplar/crear.html.twig', [
            'formulario' => $this->createForm(EjemplarCrearType::class, $ejemplar, [
                'action' => $this->generateUrl('ejemplar_crear'),
                'method' => 'POST',
            ])->createView(),
            'clonado' => true,
        ]);
    }

    #[Route('/crear', name: 'ejemplar_crear')]
    public function crear(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ejemplar = new Ejemplar();

        // Establecer valores por defecto
        $ejemplar->setFechaRegistro(new \DateTime());
        $ejemplar->setGeoLat($this->getUser()->getGeoLatDefecto());
        $ejemplar->setGeoLong($this->getUser()->getGeoLongDefecto());
        $ejemplar->setLugar($this->getUser()->getLugarDefecto());

        $formulario = $this->createForm(EjemplarCrearType::class, $ejemplar, [
            'action' => $this->generateUrl('ejemplar_crear'),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Añadir el usuario actual como creador y modificador
            $ejemplar->setCreadoPor($this->getUser());
            $ejemplar->setModificadoPor($this->getUser());

            // Procesar imágenes
            $imagen1 = $formulario->get('imagen1')->getData();
            $imagen2 = $formulario->get('imagen2')->getData();
            $imagen3 = $formulario->get('imagen3')->getData();

            $entityManager->persist($ejemplar);
            $entityManager->flush();

            // Guardar imágenes después del flush para tener el ID
            if ($imagen1) {
                $this->guardarImagen($ejemplar, $imagen1, 1);
            }

            if ($imagen2) {
                $this->guardarImagen($ejemplar, $imagen2, 2);
            }

            if ($imagen3) {
                $this->guardarImagen($ejemplar, $imagen3, 3);
            }

            if ($imagen1 || $imagen2 || $imagen3) {
                $entityManager->flush();
            }

            $this->addFlash('notice', 'ejemplar.mensaje.ejemplar_creado');

            return $this->redirectToRoute('ejemplar_ver', [
                'id' => $ejemplar->getId(),
            ]);
        }

        return $this->render('ejemplar/crear.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }

    #[Route('/editar/{id}', name: 'ejemplar_editar')]
    public function editar(int $id, Request $request, EjemplarRepository $ejemplarRepository, EntityManagerInterface $entityManager): Response
    {
        $ejemplar = $ejemplarRepository->find($id);

        if (!$ejemplar instanceof Ejemplar) {
            throw $this->createNotFoundException('No se encontró el ejemplar con id ' . $id);
        }

        $formulario = $this->createForm(EjemplarEditarType::class, $ejemplar, [
            'action' => $this->generateUrl('ejemplar_editar', [
                'id' => $id,
            ]),
            'method' => 'POST',
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            // Añadir el usuario actual como modificador
            $ejemplar->setModificadoPor($this->getUser());

            // Procesar imágenes
            $imagen1 = $formulario->get('imagen1')->getData();
            $imagen2 = $formulario->get('imagen2')->getData();
            $imagen3 = $formulario->get('imagen3')->getData();

            // Checkboxes de borrar
            $borrarImagen1 = $formulario->get('borrarImagen1')->getData();
            $borrarImagen2 = $formulario->get('borrarImagen2')->getData();
            $borrarImagen3 = $formulario->get('borrarImagen3')->getData();

            // Borrar imágenes si se marcó el checkbox
            if ($borrarImagen1 && ! $imagen1) {
                $this->borrarImagen($ejemplar, 1);
                $ejemplar->setPath1(null);
            }

            if ($borrarImagen2 && ! $imagen2) {
                $this->borrarImagen($ejemplar, 2);
                $ejemplar->setPath2(null);
            }

            if ($borrarImagen3 && ! $imagen3) {
                $this->borrarImagen($ejemplar, 3);
                $ejemplar->setPath3(null);
            }

            // Guardar nuevas imágenes
            if ($imagen1) {
                $this->borrarImagen($ejemplar, 1); // Borrar anterior si existe
                $this->guardarImagen($ejemplar, $imagen1, 1);
            }

            if ($imagen2) {
                $this->borrarImagen($ejemplar, 2);
                $this->guardarImagen($ejemplar, $imagen2, 2);
            }

            if ($imagen3) {
                $this->borrarImagen($ejemplar, 3);
                $this->guardarImagen($ejemplar, $imagen3, 3);
            }

            $entityManager->flush();

            $this->addFlash('notice', 'ejemplar.mensaje.ejemplar_modificado');

            return $this->redirectToRoute('ejemplar_ver', [
                'id' => $ejemplar->getId(),
            ]);
        }

        // Obtener información de capturas
        $numCapturas = $ejemplarRepository->numCapturas($id);
        $primeraCaptura = null;
        $ultimaCaptura = null;

        if ($numCapturas > 0) {
            $primeraCaptura = $ejemplarRepository->primeraCaptura($id);
            $ultimaCaptura = ($numCapturas === 1) ? null : $ejemplarRepository->ultimaCaptura($id);
        }

        return $this->render('ejemplar/editar.html.twig', [
            'formulario' => $formulario->createView(),
            'ejemplar' => $ejemplar,
            'num_capturas' => $numCapturas,
            'primera_captura' => $primeraCaptura,
            'ultima_captura' => $ultimaCaptura,
            'creado_por' => $ejemplar->getCreadoPor(),
            'creado_el' => $ejemplar->getCreadoEl(),
            'modificado_por' => $ejemplar->getModificadoPor(),
            'modificado_el' => $ejemplar->getModificadoEl(),
        ]);
    }

    #[Route('/eliminar/{id}', name: 'ejemplar_eliminar')]
    public function eliminar(int $id): Response
    {
        return $this->render('ejemplar/eliminar.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/eliminar-final/{id}', name: 'ejemplar_eliminar_final')]
    public function eliminarFinal(int $id, EjemplarRepository $ejemplarRepository, EntityManagerInterface $entityManager): Response
    {
        $ejemplar = $ejemplarRepository->find($id);

        if ($ejemplar instanceof Ejemplar) {
            $entityManager->remove($ejemplar);
            $entityManager->flush();

            $this->addFlash('notice', 'ejemplar.mensaje.ejemplar_eliminado');
        }

        return $this->redirectToRoute('ejemplar_buscar_id');
    }

    #[Route('/buscar-id', name: 'ejemplar_buscar_id')]
    public function buscarId(Request $request, EjemplarRepository $ejemplarRepository, PaginatorInterface $paginator): Response
    {
        // Formulario de búsqueda por ID numérico
        $formularioId = $this->createFormBuilder()
            ->add('id', TextType::class, [
                'label' => 'ejemplar.campo.id',
            ])
            ->add('buscar', SubmitType::class, [
                'label' => 'formulario.buscar',
            ])
            ->getForm();

        $formularioId->handleRequest($request);

        // Formulario de búsqueda por otros identificadores
        $ejemplar = new Ejemplar();
        $formularioOtroId = $this->createForm(EjemplarBuscarOtroIdType::class, $ejemplar, [
            'action' => $this->generateUrl('ejemplar_buscar_id'),
            'method' => 'GET',
        ]);

        $formularioOtroId->handleRequest($request);
        if ($formularioId->isSubmitted() && $formularioId->isValid()) {
            $datos = $formularioId->getData();
            $ejemplares = $ejemplarRepository->findBy([
                'id' => $datos['id'],
            ]);
            $paginacion = $paginator->paginate(
                $ejemplares,
                $request->query->getInt('p', 1),
                20
            );
            return $this->render('ejemplar/resultadosBusqueda.html.twig', [
                'paginacion' => $paginacion,
            ]);
        }

        if ($formularioOtroId->isSubmitted() && $formularioOtroId->isValid()) {
            $ejemplares = $ejemplarRepository->encontrarOtroId($formularioOtroId->get('otroId')->getData());
            $paginacion = $paginator->paginate(
                $ejemplares,
                $request->query->getInt('p', 1),
                20
            );
            return $this->render('ejemplar/resultadosBusqueda.html.twig', [
                'paginacion' => $paginacion,
            ]);
        }

        return $this->render('ejemplar/buscar.html.twig', [
            'form_id' => $formularioId->createView(),
            'form_otroid' => $formularioOtroId->createView(),
        ]);
    }

    #[Route('/buscar-mapa', name: 'ejemplar_buscar_mapa')]
    public function buscarMapa(Request $request, EjemplarRepository $ejemplarRepository, PaginatorInterface $paginator): Response
    {
        $ejemplar = new Ejemplar();

        $lat = $this->getUser()->getGeoLatDefecto();
        $long = $this->getUser()->getGeoLongDefecto();

        $formulario = $this->createForm(BusquedaMapaType::class, $ejemplar, [
            'latitud' => $lat,
            'longitud' => $long,
        ]);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $fechaInicial = $formulario->get('fechaInicial')->getData();
            $fechaFinal = $formulario->get('fechaFinal')->getData();
            $fechaBajaInicial = $formulario->get('fechaBajaInicial')->getData();
            $fechaBajaFinal = $formulario->get('fechaBajaFinal')->getData();
            $latitud = $formulario->get('geoLat')->getData();
            $longitud = $formulario->get('geoLong')->getData();
            $distancia = $formulario->get('distancia')->getData();
            $tipoEjemplar = $formulario->get('tipoEjemplar')->getData();

            // Si presionó "Generar informe", redirigir a la pantalla de informe
            if ($formulario->get('informe')->isClicked()) {
                $params = ['tipoEjemplar' => $tipoEjemplar];

                if ($fechaInicial) {
                    $params['fechaInicial'] = $fechaInicial->format('Y-m-d');
                }
                if ($fechaFinal) {
                    $params['fechaFinal'] = $fechaFinal->format('Y-m-d');
                }
                if ($fechaBajaInicial) {
                    $params['fechaBajaInicial'] = $fechaBajaInicial->format('Y-m-d');
                }
                if ($fechaBajaFinal) {
                    $params['fechaBajaFinal'] = $fechaBajaFinal->format('Y-m-d');
                }
                if ($latitud !== null) {
                    $params['latitud'] = $latitud;
                }
                if ($longitud !== null) {
                    $params['longitud'] = $longitud;
                }
                if ($distancia !== null) {
                    $params['distancia'] = $distancia;
                }
                if ($ejemplar->getEspecie()) {
                    $params['especieId'] = $ejemplar->getEspecie()->getId();
                }
                if ($ejemplar->getSexo() !== null && $ejemplar->getSexo() !== 0) {
                    $params['sexo'] = $ejemplar->getSexo();
                }
                if ($ejemplar->getRecinto() !== null && $ejemplar->getRecinto() !== '') {
                    $params['recinto'] = $ejemplar->getRecinto();
                }
                if ($ejemplar->getLugar() !== null && $ejemplar->getLugar() !== '') {
                    $params['lugar'] = $ejemplar->getLugar();
                }
                if ($ejemplar->getOrigen() !== null && $ejemplar->getOrigen() !== 0) {
                    $params['origen'] = $ejemplar->getOrigen();
                }
                if ($ejemplar->getDocumentacion() !== null && $ejemplar->getDocumentacion() !== 0) {
                    $params['documentacion'] = $ejemplar->getDocumentacion();
                }
                if ($ejemplar->getProgenitor1() !== null && $ejemplar->getProgenitor1() !== '') {
                    $params['progenitor1'] = $ejemplar->getProgenitor1();
                }
                if ($ejemplar->getDepositoNombre() !== null && $ejemplar->getDepositoNombre() !== '') {
                    $params['depositoNombre'] = $ejemplar->getDepositoNombre();
                }
                if ($ejemplar->getDepositoDNI() !== null && $ejemplar->getDepositoDNI() !== '') {
                    $params['depositoDNI'] = $ejemplar->getDepositoDNI();
                }
                if ($ejemplar->getInvasora() !== null) {
                    $params['invasora'] = $ejemplar->getInvasora() ? 1 : 0;
                }
                if ($ejemplar->getPeligroso() !== null) {
                    $params['peligroso'] = $ejemplar->getPeligroso() ? 1 : 0;
                }
                if ($ejemplar->getCites() !== null) {
                    $params['cites'] = $ejemplar->getCites();
                }
                if ($ejemplar->getCausaBaja() !== null) {
                    $params['causaBaja'] = $ejemplar->getCausaBaja();
                }

                return $this->redirectToRoute('informe_busqueda_resultados', $params);
            }

            // Si presionó "Buscar", mostrar resultados paginados
            $ejemplares = $ejemplarRepository->buscarMapa(
                $ejemplar,
                $fechaInicial,
                $fechaFinal,
                $fechaBajaInicial,
                $fechaBajaFinal,
                $latitud,
                $longitud,
                $distancia,
                $tipoEjemplar
            );

            $paginacion = $paginator->paginate(
                $ejemplares,
                $request->query->getInt('p', 1),
                20
            );

            return $this->render('ejemplar/resultadosBusqueda.html.twig', [
                'paginacion' => $paginacion,
            ]);
        }

        return $this->render('ejemplar/buscarMapa.html.twig', [
            'formulario' => $formulario->createView(),
            'lat' => $lat,
            'long' => $long,
        ]);
    }

    private function guardarImagen(Ejemplar $ejemplar, $archivo, int $numero): void
    {
        // Obtener la extensión ANTES de mover el archivo
        $extension = $archivo->guessExtension();
        $nombreArchivo = 'I' . $numero . '-' . $ejemplar->getId() . '.' . $extension;
        $directorioEjemplares = $this->getParameter('ejemplares_directory');

        $archivo->move($directorioEjemplares, $nombreArchivo);

        // Redimensionar la imagen
        $this->redimensionarImagen($ejemplar, $numero, $extension);

        // Actualizar el path en la entidad
        match ($numero) {
            1 => $ejemplar->setPath1($extension),
            2 => $ejemplar->setPath2($extension),
            3 => $ejemplar->setPath3($extension),
        };
    }

    private function redimensionarImagen(Ejemplar $ejemplar, int $numero, string $extension): void
    {
        $nombreArchivo = 'I' . $numero . '-' . $ejemplar->getId() . '.' . $extension;
        $directorioEjemplares = $this->getParameter('ejemplares_directory');
        $rutaOriginal = $directorioEjemplares . '/' . $nombreArchivo;
        $rutaRedimensionada = $rutaOriginal . '.red';

        // Aplicar el filtro 'recorte' para redimensionar
        $webPath = 'uploads/ejemplares/' . $nombreArchivo;
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

    private function borrarImagen(Ejemplar $ejemplar, int $numero): void
    {
        $path = match ($numero) {
            1 => $ejemplar->getPath1(),
            2 => $ejemplar->getPath2(),
            3 => $ejemplar->getPath3(),
        };

        if ($path) {
            $nombreArchivo = 'I' . $numero . '-' . $ejemplar->getId() . '.' . $path;
            $rutaCompleta = $this->getParameter('ejemplares_directory') . '/' . $nombreArchivo;

            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }

            // Borrar cache de miniaturas
            $webPath = 'uploads/ejemplares/' . $nombreArchivo;
            $this->cacheManager->remove($webPath, 'thumbnail');
            $this->cacheManager->remove($webPath, 'recorte');
        }
    }
}
