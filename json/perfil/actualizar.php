<?php
/*
    Codigos:
    0 = Algún campo está vacío
    1 = Usuario actualizado correctamente en la BD
    2 = El usuario no se pudo actualizar en la BD
    3 = No se puede insertar en la BD porque el nombre de usuario indicado ya existe y debe ser único
    4 = No se puede insertar en la BD porque la cedula indicado del usuario ya existe y debe ser única
    5 = Error consultando en la base de datos
    6 = No posee permisos para realizar la operación
*/
session_start();
$msg['msg'] = 'No posee permisos para actualizar el usuario';
$msg['flag'] = 6;

if(isset($_SESSION['super_administrador']) || isset($_SESSION['administrador']) || isset($_SESSION['general'])) {    
    $flag = 1;
    
    foreach ($_POST as $clave => $valor){
        if( $clave != 'especialidad' && $clave != 'codigo_postal' && $clave != 'correo_alternativo' && (!isset($valor) || empty($valor)) ){
                $flag = 0;
                break;
        }
    }

    if($flag){        
        require_once('../../config.php');
        $conexion = pg_connect('host='.$app['db']['host'].' port='.$app['db']['port'].' dbname='.$app['db']['name'].' user='.$app['db']['user'].' password='.$app['db']['pass']) OR die('Error de conexión con la base de datos');
        
        if(isset($_SESSION['super_administrador']))
            $id_usuario = $_SESSION['super_administrador'];
        else if(isset($_SESSION['administrador']))
            $id_usuario = $_SESSION['administrador'];
        else if(isset($_SESSION['general']))
            $id_usuario = $_SESSION['general'];
        
        $select = 'SELECT id, nombre_usuario FROM usuario WHERE nombre_usuario = \''.$_POST['nombre_usuario'].'\'';
        
        if($query = pg_query($select)){
            $respuesta = pg_fetch_assoc($query);
            
            if(empty($respuesta['nombre_usuario']) || $respuesta['id'] === $id_usuario){
                $select = 'SELECT id, cedula FROM usuario WHERE cedula = \''.$_POST['cedula'].'\'';
                
                if($query = pg_query($select)){
                    $respuesta = pg_fetch_assoc($query);
                
                    if(empty($respuesta['cedula']) || $respuesta['id'] === $id_usuario){

                        $_POST['fecha_nacimiento'] = date('Y-m-d', strtotime(str_replace('/','-',$_POST['fecha_nacimiento'])));
                        $tlf_movil = '';
                        $tlf_casa = '';

                        foreach ($_POST['tlf_movil'] as $clave => $valor){
                            $tlf_movil .= $valor.'-';
                            unset($_POST['tlf_movil'][$clave]);
                        }
                        $_POST['tlf_movil'] = substr_replace($tlf_movil, '', strlen($tlf_movil) - 1);

                        foreach ($_POST['tlf_casa'] as $clave => $valor){
                            $tlf_casa .= $valor.'-';
                            unset($_POST['tlf_casa'][$clave]);
                        }
                        $_POST['tlf_casa'] = substr_replace($tlf_casa, '', strlen($tlf_casa) - 1);
                        date_default_timezone_set('Etc/GMT+4');
                        $columnas = 'UPDATE usuario SET (fecha_ua, usuario_ua, ';
                        $valores = '= (\''.date('Y-m-d').'\', '.$id_usuario.', ';
                        $len = count($_POST);
                        $cont = 0;

                        foreach ($_POST as $clave => $valor){
                            if($cont === $len - 1) {
                                $columnas .= $clave.') ';
                                $valores .= '\''.$valor.'\') WHERE id = '.$id_usuario.';';

                            } else {
                                $columnas .= $clave.',';
                                $valores .= '\''.$valor.'\',';
                            }
                            $cont++;
                        }
                        $query = $columnas . $valores;

                        if(pg_query($query)) {
                            $msg['usuario'] = $_SESSION['nombre_usuario'] = $_POST['nombre_usuario'];
                            $msg['msg'] = 'Actualización de usuario exitosa';
                            $msg['flag'] = 1;

                        } else {
                            $msg['msg'] = 'Error con la base de datos, no se pudo actualizar el usuario';
                            $msg['flag'] = 2;
                        }
                    } else {
                        $msg['msg'] = 'La cédula indicada del usuario ya existe';
                        $msg['flag'] = 4;
                    }
                } else {
                    $msg['msg'] = 'Error de consulta en la base de datos';
                    $msg['flag'] = 5;
                }
            } else {
                $msg['msg'] = 'El nombre de usuario indicado ya existe';
                $msg['flag'] = 3;
            }
        } else {
            $msg['msg'] = 'Error de consulta en la base de datos';
            $msg['flag'] = 5;
        }
        pg_close($conexion);
        
    } else {
        $msg['msg'] = 'Debe llenar todos los campos';
        $msg['flag'] = 0;
    }
}
echo json_encode($msg);
?>