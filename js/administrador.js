function cargar_usuarios() {
    $.ajax({
        async: false,
        url: basedir + '/json/onload_usuario.php',
        error: function () {
            alert('Disculpe se presentó un error cargando la información');
        },
        success: function (usuarios) {
            var datos = JSON.parse(usuarios);
            var html = "<tr><th class='icono-tabla'></th><th>Nombre</th><th>Cédula</th><th>Fecha de Nacimiento</th><th>Lugar de Nacimiento</th><th>Fecha de Ingreso</th><th>Especialidad</th><th>Nombre de Usuario</th><th>Estado</th><th>Ciudad</th><th>Dirección</th><th>Código Postal</th><th>Lugar de Trabajo</th><th>Móvil</th><th>Tlf de Casa</th><th>Email</th></tr>";

            if(datos.flag){
                for(var i in datos.usuario){
                    html += "<tr><td class='icono-tabla' data-id='" + datos.usuario[i].id + "'><i class='fa fa-trash-o fa-2x icon'></i></td><td>" + datos.usuario[i].primernombre + " " + datos.usuario[i].segundonombre + " " + datos.usuario[i].primerapellido + " " + datos.usuario[i].segundoapellido + "</td><td>" + datos.usuario[i].cedula + "</td><td>" + datos.usuario[i].fechanacimiento + "</td><td>" + datos.usuario[i].lugarnacimiento + "</td><td>" + datos.usuario[i].fechaingreso + "</td><td>" + datos.usuario[i].especialidad + "</td><td>" + datos.usuario[i].nombreusuario + "</td><td>" + datos.usuario[i].estadoresidencia + "</td><td>" + datos.usuario[i].ciudadresidencia + "</td><td>" + datos.usuario[i].direccion + "</td><td>" + datos.usuario[i].codigopostal + "</td><td>" + datos.usuario[i].lugar_trabajo + "</td><td>" + datos.usuario[i].tlfmovil + "</td><td>" + datos.usuario[i].tlfcasa + "</td><td>" + datos.usuario[i].correoelectronico + "</td></tr>";
                }

                $('#usuarios').html(html);
            }else
                alert('Lo sentimos no se encontraron los datos del usuario');
        }
    });
}

function eliminar_usuario(user){
    $.ajax({
        async: false,
        url: basedir + '/json/eliminar_usuario.php',
        type: 'POST',
        data: {
            usuario: user
        },
        error: function () {
            alert('Disculpe no se puedo eliminar el usuario');
        },
        success: function (resultado) {
            var msg = JSON.parse(resultado);
            
            if(msg){
                alert('Usuario eliminado exitosamente');
                cargar_usuarios();
            }
            else
                alert('No se puedo eliminar el usuario por error en la base de datos');
        }
    });
}

$(document).ready(function () {
    //Trae de la base de datos la información necesaria de todos los usuarios registrados
    cargar_usuarios();
    
    $(document).on('click','#usuarios tr .icono-tabla', function(){ 
        var confirmacion = confirm("¿Desea eliminar este usuario?");
        if(confirmacion)
            eliminar_usuario($(this).attr('data-id'));
    });
});