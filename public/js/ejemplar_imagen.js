/**
 * ejemplar_imagen.js
 *
 * Mostrar imagen de ejemplar
 */


$(document).ready(function() {
    // Mostrar imágenes centradas en pantalla
    var lanzador = $('.lanzador_facial_activo');
    var cortinilla = $('#cortinilla');
    var superpuesto = $('.superpuesto');
    var numero = null;

    function mostrar_imagen(id) {
        var superpuesto_id = $('#superpuesto-' + id);
        var imagen = $('#superpuesto-' + id + ' img');

        cortinilla.css({'display':'block','opacity':1});
        superpuesto_id.css({
            'display': 'block',
            'opacity': 1,
            });
        var ancho = imagen.width();
        var alto = imagen.height();
        superpuesto.css({
            'width'  : ancho,
            'height' : alto,
            'margin-left' : -Math.floor(ancho / 2),
            'margin-top'  : -Math.floor(alto / 2)
            });
    }

    var hideSuperpuesto = function(){
        cortinilla.css({'display':'none','opacity':0});
        superpuesto.css({'display':'none','opacity':0});
    };

    lanzador.on('click',function(e){
        e.preventDefault();
        numero = $(this).attr('id')
        mostrar_imagen(numero);
    });

    cortinilla.on('click',function(e){
        e.preventDefault();
        hideSuperpuesto();
    });
});