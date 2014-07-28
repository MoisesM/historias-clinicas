<?php
/*
    Codigos:
    0 = Algún campo está vacío
    1 = Desarrollo psicomotor del paciente actualizado correctamente en la BD
    2 = El desarrollo psicomotor del paciente no se pudo actualizar en la BD
*/
session_start();
$msg = NULL;

if(isset($_SESSION['super_administrador']) || isset($_SESSION['administrador']) || isset($_SESSION['general'])) {
    $flag = 1;
    
    foreach ($_POST as $valor)
        if(!isset($valor) || empty($valor)){
            $flag = 0;
            break;
        }

    if($flag){
        require_once('../config.php');
        $conexion = pg_connect('host='.$app['db']['host'].' port='.$app['db']['port'].' dbname='.$app['db']['name'].' user='.$app['db']['user'].' password='.$app['db']['pass']) OR die('Error de conexión con la base de datos');

        if(isset($_SESSION['super_administrador']))
            $id_usuario = $_SESSION['super_administrador'];
        else if(isset($_SESSION['administrador']))
            $id_usuario = $_SESSION['administrador'];
        else if(isset($_SESSION['general']))
            $id_usuario = $_SESSION['general'];
        
        $cont = 0;    
        $select = 'SELECT id_paciente FROM desarrollo_psicomotor WHERE id_paciente = '.$_POST['id_paciente'];
        
        if($query = pg_query($select)){
            $respuesta = pg_fetch_array($query);
            if(empty($respuesta['id_paciente'])){
                date_default_timezone_set('Etc/GMT+4');                
                $len = count($_POST);
                $columnas = 'INSERT INTO desarrollo_psicomotor (fecha_ua, usuario_ua, creador, ';
                $valores = 'VALUES (\''.date('Y-m-d').'\', '.$id_usuario.', '.$id_usuario.', ';
                $lastValue = '\'%s\');';
                
            } else{
                $id_paciente = $_POST['id_paciente'];
                unset($_POST['id_paciente']);
                $len = count($_POST);
                $columnas = 'UPDATE desarrollo_psicomotor SET (fecha_ua, usuario_ua, ';
                $valores = '= (\''.date('Y-m-d').'\', '.$id_usuario.', ';
                $lastValue = '\'%s\') WHERE id_paciente = '.$id_paciente.';';
            }
            
            foreach ($_POST as $clave => $valor){
                if($cont == $len - 1) {
                    $columnas .= sprintf('%s) ', $clave);
                    $valores .= sprintf($lastValue, $valor);
                }
                else {
                    $columnas .= sprintf('%s,', $clave); 
                    $valores .= sprintf('\'%s\',', $valor);
                }
                $cont++;
            }
            $query = $columnas . $valores;

            if(pg_query($query)) {
                $msg['codigo'] = 1;
            } else {
                $msg['codigo'] = 2;
            }
        }
        pg_close($conexion);
    }else{
        $msg['codigo'] = 0;
    }
}
echo json_encode($msg);
?>