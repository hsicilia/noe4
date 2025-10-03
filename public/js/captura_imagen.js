/**
 * captura_imagen.js
 *
 * Mostrar imagen de captura
 */


$(document).ready(function() {
    // Mostrar imágenes centradas en pantalla (Captura)
    var lanzador = $('.lanzador');
    var cortinilla = $('#cortinilla');
    var superpuesto = $('#superpuesto');
    var imagen = $('#superpuesto img');

    var showSuperpuesto = function(){
        cortinilla.css({'display':'block','opacity':1});
        superpuesto.css({
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
    };

    var hideSuperpuesto = function(){
        cortinilla.css({'display':'none','opacity':0});
        superpuesto.css({'display':'none','opacity':0});
    };

    lanzador.on('click',function(e){
        e.preventDefault();
        showSuperpuesto();
    });

    cortinilla.on('click',function(e){
        hideSuperpuesto();
    });

    imagen.on('click', function(e){
        hideSuperpuesto();
    });
});