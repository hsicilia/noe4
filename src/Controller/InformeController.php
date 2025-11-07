<?php

namespace App\Controller;

use App\Entity\Ejemplar;
use App\Entity\Especie;
use App\Repository\EspecieRepository;
use App\Repository\EjemplarRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/informe')]
class InformeController extends AbstractController
{
    #[Route('/busqueda/resultados', name: 'informe_busqueda_resultados')]
    public function busquedaResultados(Request $request, EjemplarRepository $ejemplarRepository): Response
    {
        // Reconstruir el objeto Ejemplar desde los parámetros
        $ejemplar = new Ejemplar();

        if ($request->query->get('especieId')) {
            $especie = $this->getParameter('kernel.container')->get('doctrine')->getRepository(Especie::class)
                ->find($request->query->get('especieId'));
            $ejemplar->setEspecie($especie);
        }

        if ($request->query->get('sexo')) {
            $ejemplar->setSexo($request->query->get('sexo'));
        }

        if ($request->query->get('recinto')) {
            $ejemplar->setRecinto($request->query->get('recinto'));
        }

        if ($request->query->get('lugar')) {
            $ejemplar->setLugar($request->query->get('lugar'));
        }

        if ($request->query->get('origen')) {
            $ejemplar->setOrigen($request->query->get('origen'));
        }

        if ($request->query->get('documentacion')) {
            $ejemplar->setDocumentacion($request->query->get('documentacion'));
        }

        if ($request->query->get('progenitor1')) {
            $ejemplar->setProgenitor1($request->query->get('progenitor1'));
        }

        if ($request->query->get('depositoNombre')) {
            $ejemplar->setDepositoNombre($request->query->get('depositoNombre'));
        }

        if ($request->query->get('depositoDNI')) {
            $ejemplar->setDepositoDNI($request->query->get('depositoDNI'));
        }

        if ($request->query->get('invasora') !== null) {
            $ejemplar->setInvasora($request->query->get('invasora'));
        }

        if ($request->query->get('peligroso') !== null) {
            $ejemplar->setPeligroso($request->query->get('peligroso'));
        }

        if ($request->query->get('cites') !== null) {
            $ejemplar->setCites($request->query->get('cites'));
        }

        if ($request->query->get('causaBaja')) {
            $ejemplar->setCausaBaja($request->query->get('causaBaja'));
        }

        $fechaInicial = $request->query->get('fechaInicial')
            ? new \DateTime($request->query->get('fechaInicial'))
            : null;
        $fechaFinal = $request->query->get('fechaFinal')
            ? new \DateTime($request->query->get('fechaFinal'))
            : null;
        $fechaBajaInicial = $request->query->get('fechaBajaInicial')
            ? new \DateTime($request->query->get('fechaBajaInicial'))
            : null;
        $fechaBajaFinal = $request->query->get('fechaBajaFinal')
            ? new \DateTime($request->query->get('fechaBajaFinal'))
            : null;
        $latitud = $request->query->get('latitud');
        $longitud = $request->query->get('longitud');
        $distancia = $request->query->get('distancia');

        // Contar total
        $ejemplares = $ejemplarRepository->buscarMapa(
            $ejemplar,
            $fechaInicial,
            $fechaFinal,
            $fechaBajaInicial,
            $fechaBajaFinal,
            $latitud,
            $longitud,
            $distancia
        );

        $total = count($ejemplares);
        $numVolumenes = (int) ceil($total / 500);

        return $this->render('informe/busquedaResultados.html.twig', [
            'total' => $total,
            'numVolumenes' => $numVolumenes,
            'params' => $request->query->all(),
        ]);
    }

    #[Route('/busqueda/{salida}/{volumen}', name: 'informe_busqueda_salida', defaults: [
        'volumen' => 0,
    ])]
    public function busquedaSalida(
        Request $request,
        string $salida,
        int $volumen,
        EjemplarRepository $ejemplarRepository,
        Pdf $pdf
    ): Response {
        // Reconstruir búsqueda igual que busquedaResultados
        $ejemplar = new Ejemplar();

        if ($request->query->get('especieId')) {
            $especie = $this->getParameter('kernel.container')->get('doctrine')->getRepository(Especie::class)
                ->find($request->query->get('especieId'));
            $ejemplar->setEspecie($especie);
        }

        if ($request->query->get('sexo')) {
            $ejemplar->setSexo($request->query->get('sexo'));
        }

        if ($request->query->get('recinto')) {
            $ejemplar->setRecinto($request->query->get('recinto'));
        }

        if ($request->query->get('lugar')) {
            $ejemplar->setLugar($request->query->get('lugar'));
        }

        if ($request->query->get('origen')) {
            $ejemplar->setOrigen($request->query->get('origen'));
        }

        if ($request->query->get('documentacion')) {
            $ejemplar->setDocumentacion($request->query->get('documentacion'));
        }

        if ($request->query->get('progenitor1')) {
            $ejemplar->setProgenitor1($request->query->get('progenitor1'));
        }

        if ($request->query->get('depositoNombre')) {
            $ejemplar->setDepositoNombre($request->query->get('depositoNombre'));
        }

        if ($request->query->get('depositoDNI')) {
            $ejemplar->setDepositoDNI($request->query->get('depositoDNI'));
        }

        if ($request->query->get('invasora') !== null) {
            $ejemplar->setInvasora($request->query->get('invasora'));
        }

        if ($request->query->get('peligroso') !== null) {
            $ejemplar->setPeligroso($request->query->get('peligroso'));
        }

        if ($request->query->get('cites') !== null) {
            $ejemplar->setCites($request->query->get('cites'));
        }

        if ($request->query->get('causaBaja')) {
            $ejemplar->setCausaBaja($request->query->get('causaBaja'));
        }

        $fechaInicial = $request->query->get('fechaInicial')
            ? new \DateTime($request->query->get('fechaInicial'))
            : null;
        $fechaFinal = $request->query->get('fechaFinal')
            ? new \DateTime($request->query->get('fechaFinal'))
            : null;
        $fechaBajaInicial = $request->query->get('fechaBajaInicial')
            ? new \DateTime($request->query->get('fechaBajaInicial'))
            : null;
        $fechaBajaFinal = $request->query->get('fechaBajaFinal')
            ? new \DateTime($request->query->get('fechaBajaFinal'))
            : null;
        $latitud = $request->query->get('latitud');
        $longitud = $request->query->get('longitud');
        $distancia = $request->query->get('distancia');

        // Obtener ejemplares con paginación si es PDF
        $ejemplares = $ejemplarRepository->buscarMapa(
            $ejemplar,
            $fechaInicial,
            $fechaFinal,
            $fechaBajaInicial,
            $fechaBajaFinal,
            $latitud,
            $longitud,
            $distancia
        );

        // Para PDF, limitar a 500 ejemplares por volumen
        if ($salida === 'PDF' && $volumen > 0) {
            $inicio = ($volumen - 1) * 500;
            $ejemplares = array_slice($ejemplares, $inicio, 500);
        }

        if ($salida === 'PDF') {
            return $this->ejemplaresPDF($ejemplares, $pdf);
        }

        return $this->ejemplaresCSV($ejemplares);

    }

    #[Route('/ejemplares/{tipo}/{salida}/{volumen}', name: 'informe_ejemplares_salida', defaults: [
        'volumen' => 0,
    ])]
    public function ejemplaresSalida(string $tipo, string $salida, int $volumen, EjemplarRepository $ejemplarRepository, Pdf $pdf): Response
    {
        $ejemplares = match ($tipo) {
            'cites' => $ejemplarRepository->informeEjemplaresCites($volumen),
            'invasores' => $ejemplarRepository->informeEjemplaresInvasores($volumen),
            'especial' => $ejemplarRepository->informeEjemplaresEspeciales($volumen),
            default => $ejemplarRepository->informeEjemplaresCompleto($volumen),
        };

        if ($salida === 'PDF') {
            return $this->ejemplaresPDF($ejemplares, $pdf);
        }

        return $this->ejemplaresCSV($ejemplares);

    }

    #[Route('/especies/{salida}', name: 'informe_especies_salida')]
    public function especiesSalida(
        Request $request,
        string $salida,
        EspecieRepository $especieRepository,
        Pdf $pdf
    ): Response {
        // Obtener parámetros de búsqueda
        $nombre = $request->query->get('nombre');
        $comun = $request->query->get('comun');

        // Crear objeto Especie para la búsqueda
        $especie = new Especie();
        if ($nombre) {
            $especie->setNombre($nombre);
        }

        if ($comun) {
            $especie->setComun($comun);
        }

        // Obtener especies
        $especies = $especieRepository->encontrarEspecies($especie);

        if ($salida === 'PDF') {
            return $this->especiesPDF($especies, $pdf);
        }

        return $this->especiesCSV($especies);

    }

    private function ejemplaresPDF(array $ejemplares, Pdf $pdf): Response
    {
        $html = $this->renderView('informe/ejemplaresPDF.html.twig', [
            'ejemplares' => $ejemplares,
        ]);

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'informe_ejemplares.pdf'
        );
    }

    private function ejemplaresCSV(array $ejemplares): Response
    {
        $response = new Response($this->generaCSV($ejemplares));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="informe_ejemplares.csv"');

        return $response;
    }

    private function generaCSV(array $ejemplares): string
    {
        // Cabecera
        $csv = $this->col('ID')
              . $this->col('Fecha Registro')
              . $this->col('Especie')
              . $this->col('Nombre Común')
              . $this->col('Microchip')
              . $this->col('Anilla')
              . $this->col('Otro ID')
              . $this->col('Otro ID 2')
              . $this->col('Sexo')
              . $this->col('Recinto')
              . $this->col('Lugar')
              . $this->col('Longitud')
              . $this->col('Latitud')
              . $this->col('Origen')
              . $this->col('Documentación')
              . $this->col('Progenitor 1')
              . $this->col('Progenitor 2')
              . $this->col('Depósito Nombre')
              . $this->col('Depósito DNI')
              . $this->col('Depósito')
              . $this->col('Invasora')
              . $this->col('Peligroso')
              . $this->col('CITES')
              . $this->col('Observaciones')
              . "\n";

        // Ejemplares
        foreach ($ejemplares as $ejemplar) {
            $csv .= $this->col($ejemplar->getId())
                  . $this->col($ejemplar->getFechaRegistro()?->format('d/m/Y') ?? '')
                  . $this->col($ejemplar->getEspecie()?->getNombre() ?? '')
                  . $this->col($ejemplar->getEspecie()?->getComun() ?? '')
                  . $this->col($ejemplar->getIdMicrochip())
                  . $this->col($ejemplar->getIdAnilla())
                  . $this->col($ejemplar->getIdOtro())
                  . $this->col($ejemplar->getIdOtro2())
                  . $this->col($ejemplar->getSexo())
                  . $this->col($ejemplar->getRecinto())
                  . $this->col($ejemplar->getLugar())
                  . $this->col($ejemplar->getGeoLong())
                  . $this->col($ejemplar->getGeoLat())
                  . $this->col($ejemplar->getOrigen())
                  . $this->col($ejemplar->getDocumentacion())
                  . $this->col($ejemplar->getProgenitor1())
                  . $this->col($ejemplar->getProgenitor2())
                  . $this->col($ejemplar->getDepositoNombre())
                  . $this->col($ejemplar->getDepositoDNI())
                  . $this->col($ejemplar->getDeposito())
                  . $this->col($ejemplar->getInvasora())
                  . $this->col($ejemplar->getPeligroso())
                  . $this->col($ejemplar->getCites())
                  . $this->col($ejemplar->getObservaciones())
                  . "\n";
        }

        return $csv;
    }

    private function col(?string $texto): string
    {
        $sep = ',';
        return '"' . $this->eliminarSaltos($texto ?? '') . '"' . $sep;
    }

    private function eliminarSaltos(string $texto): string
    {
        return str_replace(["\r\n", "\n\r", "\n", "\r"], '. ', $texto);
    }

    private function especiesPDF(array $especies, Pdf $pdf): Response
    {
        $html = $this->renderView('informe/especiesPDF.html.twig', [
            'especies' => $especies,
        ]);

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'informe_especies.pdf'
        );
    }

    private function especiesCSV(array $especies): Response
    {
        $response = new Response($this->generaCSVEspecies($especies));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="informe_especies.csv"');

        return $response;
    }

    private function generaCSVEspecies(array $especies): string
    {
        // Cabecera
        $csv = $this->col('ID')
              . $this->col('Nombre científico')
              . $this->col('Nombre común')
              . "\n";

        // Especies
        foreach ($especies as $especie) {
            $csv .= $this->col($especie->getId())
                  . $this->col($especie->getNombre())
                  . $this->col($especie->getComun())
                  . "\n";
        }

        return $csv;
    }
}
