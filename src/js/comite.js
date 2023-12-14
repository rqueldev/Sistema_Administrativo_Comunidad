$(document).ready(function(){
    var dataTable = $('#datos-comite').DataTable({

        responsive: true,  //cuando la pantalla sea mas pequeña permitira mostrar las columnas faltantes
        dom: 'Bfrtip', //'lBf',  //sin esto no aparecen los botones
        buttons: [      //botones para exportar la tabla
            {
                extend: 'excelHtml5',
                text: document.getElementById('excel'),             //'<i class="fa-solid fa-file-excel"></i>',
                title: 'Comites del Consejo Comunal',    //Listado de Manzanas.xlsx
                titleAttr: 'Exportar a Excel',   //aparece cuando el usuario pasa el mouse por el boton
                className: 'btn btn-success',  //no esta agarrando
                exportOptions:{
                    columns: ':not(.no-exportar)'  //el archivvo exportado no contendra las colunmas con esta clase
                }
            },
            {
                extend: 'pdfHtml5',
                text: document.getElementById('pdf'), //'<i class="fa-solid fa-file-pdf"></i>',
                title: 'Comites del Consejo Comunal',
                titleAttr: 'Exportar a PDF',
                className: 'btn btn-danger',
                exportOptions:{
                    columns: ':not(.no-exportar)'  
                }
            },
            {
                extend: 'excelHtml5',
                text: document.getElementById('print'), //'<i class="fa-solid fa-print"></i>',
                title: 'Comites del Consejo Comunal',
                titleAttr: 'Imprimir',
                className: 'btn btn-info',
                exportOptions:{
                    columns: ':not(.no-exportar)'  
                }
            }
        ],

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



/*__________________________________________________ MODULO DE RECUPERACIÓN ____________________________________________________________________*/

$(document).ready(function(){
    var dataTable = $('#tabla_recuperacion').DataTable({

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



/*__________________________________________________________________________________________________________________________________________*/





/*
$('#container').css( 'display', 'block' );
datatable.columns.adjust().draw();


/*El boton del sidebar cambia de color al ser presionado */

let btn = document.getElementById("btn");   // no corre
btn.onclick = function() {
     btn.style.color = "rgb(107, 21, 187)" ;
}