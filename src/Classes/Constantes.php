<?php

namespace App\Classes;

class Constantes
{
    public const ROLE_ADMIN = 1;

    public const ROLE_OPERADOR_PROPIO = 2;

    public const ROLE_OPERADOR_EXTERNO = 3;

    public const ROLE_OPERADOR_EXTERNO_PRUEBAS = 4;

    public const ROLE_VISITANTE = 5;

    public static array $opciones_sexo = [
        0 => 'ejemplar.sexo.desconocido',
        1 => 'ejemplar.sexo.macho',
        2 => 'ejemplar.sexo.hembra',
    ];

    public static array $opciones_origen = [
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
    ];

    public static array $opciones_documentacion = [
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
    ];

    public static array $opciones_cites = [
        0 => 'ejemplar.cites.no',
        1 => 'ejemplar.cites.A',
        2 => 'ejemplar.cites.B',
        3 => 'ejemplar.cites.C',
        4 => 'ejemplar.cites.D',
    ];

    public static array $opciones_invasora = [
        0 => 'ejemplar.invasora.no',
        1 => 'ejemplar.invasora.si',
    ];

    public static array $opciones_peligroso = [
        0 => 'ejemplar.peligroso.no',
        1 => 'ejemplar.peligroso.si',
    ];

    public static array $causa_baja = [
        1 => 'ejemplar.causa_baja.devolucion',
        2 => 'ejemplar.causa_baja.liberacion',
        3 => 'ejemplar.causa_baja.muerte',
        4 => 'ejemplar.causa_baja.robo',
        5 => 'ejemplar.causa_baja.traslado',
    ];

    public static array $opciones_tipo_usuario = [
        self::ROLE_ADMIN => 'usuario.tipo.administrador',
        self::ROLE_OPERADOR_PROPIO => 'usuario.tipo.operador_propio',
        self::ROLE_OPERADOR_EXTERNO => 'usuario.tipo.operador_externo',
        self::ROLE_OPERADOR_EXTERNO_PRUEBAS => 'usuario.tipo.operador_externo_pruebas',
        self::ROLE_VISITANTE => 'usuario.tipo.visitante',
    ];

    public static array $opciones_usuario_activado = [
        0 => 'usuario.activado.desactivado',
        1 => 'usuario.activado.activado',
    ];
}
