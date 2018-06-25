<?php
header("Content-Type:application/json");
require_once "../connection/database.php";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //Get Data
    //$nombre = isset($_GET['nombre']) ? mysql_real_escape_string($_GET['nombre']) : "";

    $nombre = $_GET['nombre'];

    $query = 'begin "EDUPK_ALUMNO"."SP_B_ALUMNOAUTOCOMPLETEMOVIL" (:O_CURSOR, :I_PREFIJO); end;';

    $stmt = oci_parse($db, $query);

    $cursor = oci_new_cursor($db);

    $result = array();

    oci_bind_by_name($stmt, ":O_CURSOR", $cursor, -1, OCI_B_CURSOR);
    oci_bind_by_name($stmt, ":I_PREFIJO", $nombre);

    if (oci_execute($stmt)) {
        oci_execute($cursor);
        oci_fetch_all($cursor, $result, null, null, OCI_FETCHSTATEMENT_BY_ROW);

        $response = array("status" => 1,
            "Alumnos" => $result,
            "message" => "Consulta exitosa");
    } else {
        $response = array("status" => 0,
            "Alumnos" => $result,
            "message" => "Error en la consulta");
    }

} else {
    $response = array("status" => 0,
        "Alumnos" => "$result",
        "message" => "Método no aceptado");
}

header("Content-Type: application/json");
echo json_encode($response);
