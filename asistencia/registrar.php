<?php
header("Content-Type:application/json");
require_once "../connection/database.php";

/*
    TODO = RETORNAR SEXO (PARA CAMBIAR ÍCONO DE RESPUESTA) Y NOMBRE DEL ALUMNO
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /* OBTENER LA DATA DEL APP */
    $id_mtri       = isset($_POST['id_mtri']) ? $_POST['id_mtri']:             "";
    $asis_fecha    = isset($_POST['asis_fecha']) ? $_POST['asis_fecha']:       "";
    $asis_hentrada = isset($_POST['asis_hentrada']) ? $_POST['asis_hentrada']: "";
    $asis_hsalida  = isset($_POST['asis_hsalida']) ? $_POST['asis_hsalida']:   "";
    $asis_ctlgasis = isset($_POST['asis_ctlgasis']) ? $_POST['asis_ctlgasis']: "";
    $audt_usuario  = isset($_POST['audt_usuario']) ? $_POST['audt_usuario']:   "";
    $id_area       = isset($_POST['id_area']) ? $_POST['id_area']:             "";

    /* VERIFICAR SI LA ASISTENCIA DEL ALUMNO YA SE REGISTRÓ HOY */
    $sql_existe = "SELECT * FROM EDUTV_ASISTENCIA WHERE ID_MTRI = '{$id_mtri}' AND ASIS_FECHA = TO_CHAR(CURRENT_DATE)";

    $query_existe = oci_parse($db, $sql_existe);
    oci_execute($query_existe);
    $exists       = oci_fetch_all($query_existe, $res);
    unset($res);
    $e = oci_error($query_existe);
    oci_free_statement($query_existe);

    /* OBTENER HORA DE ENTRADA DEL CATÁLOGO */
    $sql_hora_entrada = "SELECT CTLG_VALOR2 AS HORA_INICIO 
                         FROM TC_CATALOGO 
                         WHERE CTLG_TIPOID = '01' AND CTLG_TABLAID = '039' AND CTLG_DATOID = '01'";
                        
    $query_hora_entrada = oci_parse($db, $sql_hora_entrada);

    oci_execute($query_hora_entrada);
    oci_fetch($query_hora_entrada);
    $hora_inicio_clases = oci_result($query_hora_entrada, 'HORA_INICIO');
    oci_free_statement($query_hora_entrada);

    /* PUNTUAL O TARDANZA */
    if (strtotime($asis_hentrada) < strtotime($hora_inicio_clases)) {
        $asis_ctlgasis   = "0107701"; //LLEGÓ TEMPRANO
        $tipo_asistencia = "asistencia";
    } else {
        $asis_ctlgasis   = "0107702"; //LLEGÓ TARDE
        $tipo_asistencia = "tardanza";
    }

    /* SI EXISTE = UPDATE A ASISTENCIA, CASO CONTRARIO INGRESAR ASISTENCIA NUEVA */
    if ($exists > 0) {
		$sql_update_tardanza = "UPDATE EDUTV_ASISTENCIA 
                                SET CTLG_ASISTENCIA = '{$asis_ctlgasis}', ASIS_HORAENTRADA = '{$asis_hentrada}' 
                                WHERE ID_MTRI = '{$id_mtri}' AND ASIS_FECHA = TO_CHAR(CURRENT_DATE)";

		$query_update_tardanza = oci_parse($db, $sql_update_tardanza);
		oci_execute($query_update_tardanza);
		$error = oci_error($query_update_tardanza);
		oci_free_statement($query_update_tardanza);
		
        $response = array(
            "status"  => 0,
            "message" => "Se registró la {$tipo_asistencia} del alumno las {$asis_hentrada} con el ctlg {$asis_ctlgasis}",
            "name"    => $asis_name_alumno,
            "hour"    => $asis_hentrada,
            "sex"     => $asis_sexo_alumno,
        );
    } else {
        $query = 'begin "PK_ASISTENCIA"."SP_GRABAR"(:I_ID_MTRI, :I_ASIS_FECHA, :I_ASIS_HORAENTRADA, :I_ASIS_HORASALIDA, :I_CTLG_ASISTENCIA, :I_AUDT_USUARIO, :I_ID_AREA); end;';

        $stmt = oci_parse($db, $query);

        $fecha = strtotime($asis_fecha);
        $asis_fecha = date("d/M/Y", $fecha);

        oci_bind_by_name($stmt, ":I_ID_MTRI", $id_mtri);
        oci_bind_by_name($stmt, ":I_ASIS_FECHA", $asis_fecha);
        oci_bind_by_name($stmt, ":I_ASIS_HORAENTRADA", $asis_hentrada);
        oci_bind_by_name($stmt, ":I_ASIS_HORASALIDA", $asis_hsalida);
        oci_bind_by_name($stmt, ":I_CTLG_ASISTENCIA", $asis_ctlgasis);
        oci_bind_by_name($stmt, ":I_AUDT_USUARIO", $audt_usuario);
        oci_bind_by_name($stmt, ":I_ID_AREA", $id_area);

        oci_execute($stmt);

        $e = oci_error($stmt);

        if ($e) {
            $response = array(
                "status"  => 1,
                "message" => "Hubo un error, verifique el código QR",
                "name"    => $asis_name_alumno,
                "hour"    => $asis_hentrada,
                "sex"     => $asis_sexo_alumno
            );
        } else {
            $response = array(
                "status"  => 1,
                "message" => "Se registró correctamente la hora de entrada del alumno a las {$asis_hentrada}.",
                "name"    => $asis_name_alumno,
                "hour"    => $asis_hentrada,
                "sex"     => $asis_sexo_alumno
            );
        }

    }

} else {
    $response = array(
        "status"  => 0,
        "message" => "Método no aceptado",
        "name"    => $asis_nombre_alumno,
        "hour"    => $asis_hentrada,
        "sex"     => $asis_sexo_alumno
    );
}

header("Content-Type: application/json");
echo json_encode($response);