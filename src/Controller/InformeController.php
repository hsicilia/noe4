<?php

namespace App\Controller;

use App\Repository\EjemplarRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/informe')]
class InformeController extends AbstractController
{
    #[Route('/ejemplares-menu', name: 'informe_ejemplares_menu')]
    public function ejemplaresMenu(): Response
    {
        return $this->render('informe/ejemplaresMenu.html.twig');
    }

    #[Route('/ejemplares/{tipo}/{salida}/{volumen}', name: 'informe_ejemplares_salida', defaults: ['volumen' => 0])]
    public function ejemplaresSalida(string $tipo, string $salida, int $volumen, EjemplarRepository $repository, Pdf $pdf): Response
    {
        $ejemplares = match($tipo) {
            'cites' => $repository->informeEjemplaresCites($volumen),
            'invasores' => $repository->informeEjemplaresInvasores($volumen),
            'especial' => $repository->informeEjemplaresEspeciales($volumen),
            default => $repository->informeEjemplaresCompleto($volumen),
        };

        if ($salida === 'PDF') {
            return $this->ejemplaresPDF($ejemplares, $pdf);
        } else {
            return $this->ejemplaresCSV($ejemplares);
        }
    }

    private function ejemplaresPDF(array $ejemplares, Pdf $pdf): Response
    {
        $html = $this->renderView('informe/ejemplaresPDF.html.twig', [
            'ejemplares' => $ejemplares
        ]);

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            'informe_ejemplares.pdf'
        );
    }

    private function ejemplaresCSV(array $ejemplares, TranslatorInterface $translator = null): Response
    {
        $response = new Response($this->generaCSV($ejemplares));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="informe_ejemplares.csv"');

        return $response;
    }

    private function generaCSV(array $ejemplares): string
    {
        // Cabecera
        $csv  = $this->col('ID')
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
}
