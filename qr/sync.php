<?php
header("Content-Type:application/json");
require_once "../connection/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Get Data
    $idalumno = $_POST['idalumno'];
    $codigoqr = $_POST['codigoqr'];


    $sql_alumno_already = "SELECT * FROM EDUTV_MATRICULAS WHERE ID_ALUM = '{$idalumno}' AND MTRI_QR = '{$codigoqr}'";
    $query_alumno_already = oci_parse($db, $sql_alumno_already);
    oci_execute($query_alumno_already);
    $alumno_already = oci_fetch_all($query_alumno_already, $res);
    unset($res);

    $sql = "SELECT * FROM EDUTV_MATRICULAS WHERE MTRI_QR = '{$codigoqr}'";
    $query_existe = oci_parse($db, $sql);
    oci_execute($query_existe);
    $exists = oci_fetch_all($query_existe, $res);
    unset($res);
    
    if($alumno_already > 0){
        $response = array(
            "status" => 0,
            "message" => "Ya has registrado este QR ({$codigoqr}) para este alumno anteriormente."
        );
    }else if ($exists > 0) {
        $response = array(
            "status" => 0,
            "message" => "Este código QR ya ha sido asignado a otro alumno.",
        );
    } else {
        $query = 'begin "EDUPK_MATRICULAS"."SP_G_ALUMNO_QR" (:I_TIPO, :I_ID_ALUM, :I_MTRI_QR); end;';

        $stmt = oci_parse($db, $query);
        $tipo = 1;

        oci_bind_by_name($stmt, ":I_TIPO", $tipo);
        oci_bind_by_name($stmt, ":I_ID_ALUM", $idalumno);
        oci_bind_by_name($stmt, ":I_MTRI_QR", $codigoqr);

        oci_execute($stmt);

        $e = oci_error($stmt); // Para errores de oci_execute, pase el gestor de sentencia
        
        if($e)
        {
            $response = array(
                "status" => 0,
                "message" => "Hubo un error. Intente nuevamente."
            );
        } else{
            $response = array(
                "status" => 1,
                "message" => "Se agregó el QR al alumno correctamente."
            );
        }
    }
} else {
    $response = array(
        "status" => 0,
        "message" => "Método no aceptado",
    );
}

$json_response = json_encode($response);
echo $json_response;
