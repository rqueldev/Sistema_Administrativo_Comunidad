
/*_________________________________________ TABLA JORNADA _________________________________________________________________________________________________*/


$(document).ready(function(){
    var tabla = $('#datos-jornada').DataTable({
        responsive: true,  //cuando la pantalla sea mas pequeña permitira mostrar las columnas faltantes
        dom: 'Bfrtip', //'lBf',  //sin esto no aparecen los botones
        buttons: [      //botones para exportar la tabla
            {
                extend: 'excelHtml5',
                text: document.getElementById('excel'),             //'<i class="fa-solid fa-file-excel"></i>',
                title: 'Listado de Jornadas en la Comunidad',    //Listado de Manzanas.xlsx
                titleAttr: 'Exportar a Excel',   //aparece cuando el usuario pasa el mouse por el boton
                className: 'btn btn-success',  //no esta agarrando
                exportOptions:{
                    columns: ':not(.no-exportar)'  //el archivvo exportado no contendra las colunmas con esta clase
                }
            },
            {
                extend: 'pdfHtml5',
                text: document.getElementById('pdf'), //'<i class="fa-solid fa-file-pdf"></i>',
                title: 'Listado de Jornadas en la Comunidad',
                titleAttr: 'Exportar a PDF',
                className: 'btn btn-danger',
                exportOptions:{
                    columns: ':not(.no-exportar)'  
                }
            },
            {
                extend: 'excelHtml5',
                text: document.getElementById('print'), //'<i class="fa-solid fa-print"></i>',
                title: 'Listado de Jornadas en la Comunidad',
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
          }
    })

/*_________________________________________ TABLA ATENCION  _________________________________________________________________________________________________*/


    var tablaAtencion = $('#datos-atencion').DataTable({
        responsive: true,  //cuando la pantalla sea mas pequeña permitira mostrar las columnas faltantes
        dom: 'Bfrtip', //'lBf',  //sin esto no aparecen los botones
        buttons: [      //botones para exportar la tabla
            {
                extend: 'excelHtml5',
                text: document.getElementById('excel-atencion'),             //'<i class="fa-solid fa-file-excel"></i>',
                title: 'Listado de Atenciones en la Comunidad',    //Listado de Manzanas.xlsx
                titleAttr: 'Exportar a Excel',   //aparece cuando el usuario pasa el mouse por el boton
                className: 'btn btn-success',  //no esta agarrando
                exportOptions:{
                    columns: ':not(.no-exportar)'  //el archivvo exportado no contendra las colunmas con esta clase
                }
            },
            {
                extend: 'pdfHtml5',
                text: document.getElementById('pdf-atencion'), //'<i class="fa-solid fa-file-pdf"></i>',
                title: 'Listado de Atenciones en la Comunidad',
                titleAttr: 'Exportar a PDF',
                className: 'btn btn-danger',
                exportOptions:{
                    columns: ':not(.no-exportar)'  
                }
            },
            {
                extend: 'excelHtml5',
                text: document.getElementById('print-atencion'), //'<i class="fa-solid fa-print"></i>',
                title: 'Listado de Atenciones en la Comunidad',
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
          }
    })
})


/*__________________________________________________ MODULO DE RECUPERACIÓN JORNADA ____________________________________________________________________*/

$(document).ready(function(){
    var dataTable = $('#tabla_recuperacion_jornada').DataTable({

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
/*__________________________________________________ MODULO DE RECUPERACIÓN ATENCION  ____________________________________________________________________*/

$(document).ready(function(){
    var dataTable = $('#tabla_recuperacion_atencion').DataTable({

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

