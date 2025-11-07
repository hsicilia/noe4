<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VariosExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('recortaTexto', [$this, 'recortaTextoFilter']),
            new TwigFilter('hora', [$this, 'horaFilter']),
            new TwigFilter('imagenCache', [$this, 'imagenCacheFilter']),
            new TwigFilter('nombreEspecie', [$this, 'nombreEspecieFilter']),
            new TwigFilter('sexo', [$this, 'sexoFilter']),
            new TwigFilter('origen', [$this, 'origenFilter']),
            new TwigFilter('documentacion', [$this, 'documentacionFilter']),
            new TwigFilter('sino', [$this, 'siNoFilter']),
            new TwigFilter('cites', [$this, 'citesFilter']),
            new TwigFilter('causaBaja', [$this, 'causaBajaFilter']),
        ];
    }

    // Función para obtener el sexo en función del valor de la base de datos
    public function sexoFilter($sexo): string
    {
        return match ($sexo) {
            1 => 'ejemplar.sexo.macho',
            2 => 'ejemplar.sexo.hembra',
            default => 'ejemplar.sexo.desconocido',
        };
    }

    // Función para obtener el origen en función del valor de la base de datos
    public function origenFilter($origen): string
    {
        return match ($origen) {
            0 => 'ejemplar.origen.desconocido',
            1 => 'ejemplar.origen.adquisicion',
            2 => 'ejemplar.origen.captura_directa',
            3 => 'ejemplar.origen.deposito_aduanas',
            4 => 'ejemplar.origen.deposito_ayuntamiento',
            5 => 'ejemplar.origen.deposito_cabildo',
            6 => 'ejemplar.origen.deposito_policia_canaria',
            7 => 'ejemplar.origen.deposito_guardia_civil',
            8 => 'ejemplar.origen.deposito_particulares',
            9 => 'ejemplar.origen.deposito_policia_municipal',
            10 => 'ejemplar.origen.deposito_policia_nacional',
            11 => 'ejemplar.origen.deposito_proteccion_civil',
            12 => 'ejemplar.origen.deposito_otra_administracion',
            13 => 'ejemplar.origen.deposito_santuario',
            14 => 'ejemplar.origen.nacimiento',
            default => 'ejemplar.origen.desconocido',
        };
    }

    // Función para obtener el documento en función del valor de la base de datos
    public function documentacionFilter($documento): string
    {
        return match ($documento) {
            0 => 'ejemplar.documentacion.desconocida',
            1 => 'ejemplar.documentacion.acta_cabildo_lagomera',
            2 => 'ejemplar.documentacion.acta_cabildo_elhierro',
            3 => 'ejemplar.documentacion.acta_cabildo_fuerteventura',
            4 => 'ejemplar.documentacion.acta_cabildo_grancanaria',
            5 => 'ejemplar.documentacion.acta_cabildo_lapalma',
            6 => 'ejemplar.documentacion.acta_cabildo_lanzarote',
            7 => 'ejemplar.documentacion.acta_cabildo_tenerife',
            8 => 'ejemplar.documentacion.acta_gobierno_canarias',
            9 => 'ejemplar.documentacion.acta_aduanas',
            10 => 'ejemplar.documentacion.acta_ayuntamiento',
            11 => 'ejemplar.documentacion.acta_policia_canaria',
            12 => 'ejemplar.documentacion.acta_policia_municipal',
            13 => 'ejemplar.documentacion.acta_policia_nacional',
            14 => 'ejemplar.documentacion.acta_policia_seprona',
            15 => 'ejemplar.documentacion.cites',
            16 => 'ejemplar.documentacion.declaracion_jurada',
            17 => 'ejemplar.documentacion.factura',
            default => 'ejemplar.documentacion.desconocida',
        };
    }

    // Función para mostrar Sí o No
    public function sinoFilter($valor): string
    {
        return $valor ? 'Sí' : 'No';
    }

    // Filtro para recortar un texto a un tamaño máximo. Añade puntos suspensivos al final.
    public function recortaTextoFilter($texto, $tam = 50): string
    {
        if (strlen($texto) > $tam) {
            return substr($texto, 0, $tam) . '...';
        }
        return $texto;
    }

    // Filtro para mostrar la hora de una fecha correctamente.
    public function horaFilter($fecha): string
    {
        if ($fecha !== null) {
            return $fecha->format('H:i');
        }
        return '';
    }

    public function citesFilter($valor): string
    {
        return match ($valor) {
            0 => 'ejemplar.cites.no',
            1 => 'ejemplar.cites.A',
            2 => 'ejemplar.cites.B',
            3 => 'ejemplar.cites.C',
            4 => 'ejemplar.cites.D',
            default => 'ejemplar.cites.no',
        };
    }

    public function causaBajaFilter($valor): string
    {
        return match ($valor) {
            1 => 'ejemplar.causa_baja.devolucion',
            2 => 'ejemplar.causa_baja.liberacion',
            3 => 'ejemplar.causa_baja.muerte',
            4 => 'ejemplar.causa_baja.robo',
            5 => 'ejemplar.causa_baja.traslado',
            default => '',
        };
    }

    // Filtro para poner en cursiva el nombre de las especies dejando en letra normal el resto de datos
    public function nombreEspecieFilter($especie): string
    {
        $pos = strpos($especie, '(');

        if ($pos) {
            return '<em>' . substr($especie, 0, $pos) . '</em>' . substr($especie, $pos);
        }
        return '<em>' . $especie . '</em>';
    }

    // Filtro para añadir un parámetro tipo fecha a las url de las imágenes para que el navegador las cambie al actualizarlas.
    public function imagenCacheFilter($url, $fecha): string
    {
        return $url . '?f=' . $fecha->format('dmYHi');
    }
}
