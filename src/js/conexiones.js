$(document).ready(function(){
    var dataTable = $('#datos-conexiones').DataTable({

        responsive: true,  //cuando la pantalla sea mas pequeña permitira mostrar las columnas faltantes
        
        pageLength: 4,    //al cargar la pagina se muestran 5 registros
        language: {       //configuraciones del lenguaje 
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                          "sFirst":    "Primero",
                          "sLast":     "Último",
                          "sNext":     "Siguiente",
                          "sPrevious": "Anterior"
                        },
            "oAria":    {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        }
          },
    })
    
})


/*
$('#container').css( 'display', 'block' );
datatable.columns.adjust().draw();


/*El boton del sidebar cambia de color al ser presionado */

let btn = document.getElementById("btn");   // no corre
btn.onclick = function() {
     btn.style.color = "rgb(107, 21, 187)" ;
}