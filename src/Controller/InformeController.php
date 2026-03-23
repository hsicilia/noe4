<?php

namespace App\Controller;

use App\Entity\Ejemplar;
use App\Entity\Especie;
use App\Repository\EspecieRepository;
use App\Repository\EjemplarRepository;
use App\Twig\VariosExtension;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/informe')]
class InformeController extends AbstractController
{
    public function __construct(
        private readonly VariosExtension $variosExtension,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/busqueda/resultados', name: 'informe_busqueda_resultados')]
    public function busquedaResultados(Request $request, EjemplarRepository $ejemplarRepository, EspecieRepository $especieRepository): Response
    {
        // Reconstruir el objeto Ejemplar desde los parámetros
        $ejemplar = new Ejemplar();

        if ($request->query->get('especieId') !== null && $request->query->get('especieId') !== '') {
            $especie = $especieRepository->find($request->query->get('especieId'));
            $ejemplar->setEspecie($especie);
        }

        if ($request->query->get('sexo') !== null && $request->query->get('sexo') !== '') {
            $ejemplar->setSexo((int) $request->query->get('sexo'));
        }

        if ($request->query->get('recinto') !== null && $request->query->get('recinto') !== '') {
            $ejemplar->setRecinto($request->query->get('recinto'));
        }

        if ($request->query->get('lugar') !== null && $request->query->get('lugar') !== '') {
            $ejemplar->setLugar($request->query->get('lugar'));
        }

        if ($request->query->get('origen') !== null && $request->query->get('origen') !== '') {
            $ejemplar->setOrigen((int) $request->query->get('origen'));
        }

        if ($request->query->get('documentacion') !== null && $request->query->get('documentacion') !== '') {
            $ejemplar->setDocumentacion((int) $request->query->get('documentacion'));
        }

        if ($request->query->get('progenitor1') !== null && $request->query->get('progenitor1') !== '') {
            $ejemplar->setProgenitor1($request->query->get('progenitor1'));
        }

        if ($request->query->get('depositoNombre') !== null && $request->query->get('depositoNombre') !== '') {
            $ejemplar->setDepositoNombre($request->query->get('depositoNombre'));
        }

        if ($request->query->get('depositoDNI') !== null && $request->query->get('depositoDNI') !== '') {
            $ejemplar->setDepositoDNI($request->query->get('depositoDNI'));
        }

        if ($request->query->get('invasora') !== null && $request->query->get('invasora') !== '') {
            $ejemplar->invasora = (int) $request->query->get('invasora') === 1;
        }

        if ($request->query->get('peligroso') !== null && $request->query->get('peligroso') !== '') {
            $ejemplar->peligroso = (int) $request->query->get('peligroso') === 1;
        }

        if ($request->query->get('cites') !== null && $request->query->get('cites') !== '') {
            $ejemplar->cites = (int) $request->query->get('cites');
        }

        if ($request->query->get('causaBaja') !== null && $request->query->get('causaBaja') !== '') {
            $ejemplar->setCausaBaja((int) $request->query->get('causaBaja'));
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
        $latitud = $request->query->get('latitud') ? (float) $request->query->get('latitud') : null;
        $longitud = $request->query->get('longitud') ? (float) $request->query->get('longitud') : null;
        $distancia = $request->query->get('distancia') ? (float) $request->query->get('distancia') : null;
        $tipoEjemplar = $request->query->get('tipoEjemplar', 'alta');

        // Contar total
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
        EspecieRepository $especieRepository,
        Pdf $pdf
    ): Response {
        // Reconstruir búsqueda igual que busquedaResultados
        $ejemplar = new Ejemplar();

        if ($request->query->get('especieId') !== null && $request->query->get('especieId') !== '') {
            $especie = $especieRepository->find($request->query->get('especieId'));
            $ejemplar->setEspecie($especie);
        }

        if ($request->query->get('sexo') !== null && $request->query->get('sexo') !== '') {
            $ejemplar->setSexo((int) $request->query->get('sexo'));
        }

        if ($request->query->get('recinto') !== null && $request->query->get('recinto') !== '') {
            $ejemplar->setRecinto($request->query->get('recinto'));
        }

        if ($request->query->get('lugar') !== null && $request->query->get('lugar') !== '') {
            $ejemplar->setLugar($request->query->get('lugar'));
        }

        if ($request->query->get('origen') !== null && $request->query->get('origen') !== '') {
            $ejemplar->setOrigen((int) $request->query->get('origen'));
        }

        if ($request->query->get('documentacion') !== null && $request->query->get('documentacion') !== '') {
            $ejemplar->setDocumentacion((int) $request->query->get('documentacion'));
        }

        if ($request->query->get('progenitor1') !== null && $request->query->get('progenitor1') !== '') {
            $ejemplar->setProgenitor1($request->query->get('progenitor1'));
        }

        if ($request->query->get('depositoNombre') !== null && $request->query->get('depositoNombre') !== '') {
            $ejemplar->setDepositoNombre($request->query->get('depositoNombre'));
        }

        if ($request->query->get('depositoDNI') !== null && $request->query->get('depositoDNI') !== '') {
            $ejemplar->setDepositoDNI($request->query->get('depositoDNI'));
        }

        if ($request->query->get('invasora') !== null && $request->query->get('invasora') !== '') {
            $ejemplar->invasora = (int) $request->query->get('invasora') === 1;
        }

        if ($request->query->get('peligroso') !== null && $request->query->get('peligroso') !== '') {
            $ejemplar->peligroso = (int) $request->query->get('peligroso') === 1;
        }

        if ($request->query->get('cites') !== null && $request->query->get('cites') !== '') {
            $ejemplar->cites = (int) $request->query->get('cites');
        }

        if ($request->query->get('causaBaja') !== null && $request->query->get('causaBaja') !== '') {
            $ejemplar->setCausaBaja((int) $request->query->get('causaBaja'));
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
        $latitud = $request->query->get('latitud') ? (float) $request->query->get('latitud') : null;
        $longitud = $request->query->get('longitud') ? (float) $request->query->get('longitud') : null;
        $distancia = $request->query->get('distancia') ? (float) $request->query->get('distancia') : null;
        $tipoEjemplar = $request->query->get('tipoEjemplar', 'alta');

        // Obtener ejemplares con paginación si es PDF
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

        // Para PDF, limitar a 500 ejemplares por volumen
        if ($salida === 'PDF' && $volumen > 0) {
            $inicio = ($volumen - 1) * 500;
            $ejemplares = array_slice($ejemplares, $inicio, 500);
        }

        if ($salida === 'PDF') {
            return $this->ejemplaresPDF($ejemplares, $pdf);
        }

        if ($salida === 'EXCEL') {
            return $this->ejemplaresExcel($ejemplares);
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

        if ($salida === 'EXCEL') {
            return $this->ejemplaresExcel($ejemplares);
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
        $invasora = $request->query->get('invasora');
        $cites = $request->query->get('cites');
        $peligroso = $request->query->get('peligroso');

        // Crear objeto Especie para la búsqueda
        $especie = new Especie();
        if ($nombre) {
            $especie->setNombre($nombre);
        }

        if ($comun) {
            $especie->setComun($comun);
        }

        if ($invasora !== null && $invasora !== '') {
            $especie->setInvasora((bool) $invasora);
        }

        if ($cites !== null && $cites !== '') {
            $especie->setCites((int) $cites);
        }

        if ($peligroso !== null && $peligroso !== '') {
            $especie->setPeligroso((bool) $peligroso);
        }

        // Obtener especies
        $especies = $especieRepository->encontrarEspecies($especie)->getResult();

        if ($salida === 'PDF') {
            return $this->especiesPDF($especies, $pdf);
        }

        if ($salida === 'EXCEL') {
            return $this->especiesExcel($especies);
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
              . $this->col('Fecha Ingreso')
              . $this->col('Fecha Baja')
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
                  . $this->col($ejemplar->getFechaBaja()?->format('d/m/Y') ?? '')
                  . $this->col($ejemplar->getEspecie()?->getNombre() ?? '')
                  . $this->col($ejemplar->getEspecie()?->getComun() ?? '')
                  . $this->col($ejemplar->getIdMicrochip())
                  . $this->col($ejemplar->getIdAnilla())
                  . $this->col($ejemplar->getIdOtro())
                  . $this->col($ejemplar->getIdOtro2())
                  . $this->col($this->translator->trans($this->variosExtension->sexoFilter($ejemplar->getSexo())))
                  . $this->col($ejemplar->getRecinto())
                  . $this->col($ejemplar->getLugar())
                  . $this->col($ejemplar->getGeoLong())
                  . $this->col($ejemplar->getGeoLat())
                  . $this->col($this->translator->trans($this->variosExtension->origenFilter($ejemplar->getOrigen())))
                  . $this->col($this->translator->trans($this->variosExtension->documentacionFilter($ejemplar->getDocumentacion())))
                  . $this->col($ejemplar->getProgenitor1())
                  . $this->col($ejemplar->getProgenitor2())
                  . $this->col($ejemplar->getDepositoNombre())
                  . $this->col($ejemplar->getDepositoDNI())
                  . $this->col($ejemplar->getDeposito())
                  . $this->col($this->variosExtension->sinoFilter($ejemplar->getEspecie()->getInvasora()))
                  . $this->col($this->variosExtension->sinoFilter($ejemplar->getEspecie()->getPeligroso()))
                  . $this->col($this->translator->trans($this->variosExtension->citesFilter($ejemplar->getEspecie()->getCites())))
                  . $this->col($ejemplar->getObservaciones())
                  . "\n";
        }

        return $csv;
    }

    private function col(?string $texto): string
    {
        $sep = ',';
        $texto = $this->eliminarSaltos($texto ?? '');
        $texto = str_replace('"', '""', $texto);

        return '"' . $texto . '"' . $sep;
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
              . $this->col('Invasora')
              . $this->col('CITES')
              . $this->col('Peligroso')
              . "\n";

        // Especies
        foreach ($especies as $especie) {
            $csv .= $this->col($especie->getId())
                  . $this->col($especie->getNombre())
                  . $this->col($especie->getComun())
                  . $this->col($especie->getInvasora() ? 'Sí' : 'No')
                  . $this->col($this->translator->trans($this->variosExtension->citesFilter($especie->getCites())))
                  . $this->col($especie->getPeligroso() ? 'Sí' : 'No')
                  . "\n";
        }

        return $csv;
    }

    private function ejemplaresExcel(array $ejemplares): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $cabecera = [
            'ID', 'Fecha Ingreso', 'Fecha Baja', 'Especie', 'Nombre Común', 'Microchip', 'Anilla',
            'Otro ID', 'Otro ID 2', 'Sexo', 'Recinto', 'Lugar', 'Longitud', 'Latitud',
            'Origen', 'Documentación', 'Progenitor 1', 'Progenitor 2', 'Depósito Nombre',
            'Depósito DNI', 'Depósito', 'Invasora', 'Peligroso', 'CITES', 'Observaciones',
        ];
        $worksheet->fromArray($cabecera, null, 'A1');

        $fila = 2;
        foreach ($ejemplares as $ejemplar) {
            $worksheet->fromArray([
                $ejemplar->getId(),
                $ejemplar->getFechaRegistro()?->format('d/m/Y') ?? '',
                $ejemplar->getFechaBaja()?->format('d/m/Y') ?? '',
                $ejemplar->getEspecie()?->getNombre() ?? '',
                $ejemplar->getEspecie()?->getComun() ?? '',
                null,
                $ejemplar->getIdAnilla(),
                $ejemplar->getIdOtro(),
                $ejemplar->getIdOtro2(),
                $this->translator->trans($this->variosExtension->sexoFilter($ejemplar->getSexo())),
                $ejemplar->getRecinto(),
                $ejemplar->getLugar(),
                $ejemplar->getGeoLong(),
                $ejemplar->getGeoLat(),
                $this->translator->trans($this->variosExtension->origenFilter($ejemplar->getOrigen())),
                $this->translator->trans($this->variosExtension->documentacionFilter($ejemplar->getDocumentacion())),
                $ejemplar->getProgenitor1(),
                $ejemplar->getProgenitor2(),
                $ejemplar->getDepositoNombre(),
                $ejemplar->getDepositoDNI(),
                $ejemplar->getDeposito(),
                $this->variosExtension->sinoFilter($ejemplar->getEspecie()->getInvasora()),
                $this->variosExtension->sinoFilter($ejemplar->getEspecie()->getPeligroso()),
                $this->translator->trans($this->variosExtension->citesFilter($ejemplar->getEspecie()->getCites())),
                $ejemplar->getObservaciones(),
            ], null, 'A' . $fila);
            $worksheet->setCellValueExplicit('F' . $fila, (string) ($ejemplar->getIdMicrochip() ?? ''), DataType::TYPE_STRING);
            ++$fila;
        }

        $xlsx = new Xlsx($spreadsheet);
        $streamedResponse = new StreamedResponse(static function () use ($xlsx): void {
            $xlsx->save('php://output');
        });
        $streamedResponse->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="informe_ejemplares.xlsx"');
        $streamedResponse->headers->set('Cache-Control', 'max-age=0');

        return $streamedResponse;
    }

    private function especiesExcel(array $especies): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $cabecera = ['ID', 'Nombre científico', 'Nombre común', 'Invasora', 'CITES', 'Peligroso'];
        $worksheet->fromArray($cabecera, null, 'A1');

        $fila = 2;
        foreach ($especies as $especie) {
            $worksheet->fromArray([
                $especie->getId(),
                $especie->getNombre(),
                $especie->getComun(),
                $especie->getInvasora() ? 'Sí' : 'No',
                $this->translator->trans($this->variosExtension->citesFilter($especie->getCites())),
                $especie->getPeligroso() ? 'Sí' : 'No',
            ], null, 'A' . $fila);
            ++$fila;
        }

        $xlsx = new Xlsx($spreadsheet);
        $streamedResponse = new StreamedResponse(static function () use ($xlsx): void {
            $xlsx->save('php://output');
        });
        $streamedResponse->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="informe_especies.xlsx"');
        $streamedResponse->headers->set('Cache-Control', 'max-age=0');

        return $streamedResponse;
    }

}
