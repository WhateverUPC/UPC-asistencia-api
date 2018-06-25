<?php
header("Content-Type:application/json");
require_once "../connection/database.php";


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //Get Data
    //$nombre = isset($_GET['nombre']) ? mysql_real_escape_string($_GET['nombre']) : "";

    $id_empleado = $_GET['employeeId'];

    
    $query = 'begin "PK_EMPLEADO"."SP_S_AUXILIARPLANES" (:O_CURSOR, :I_ID_EMPL, :I_PLNE_ANIO, :I_CTLG_PROGRAMA, :I_TIPO); end;';

    $stmt = oci_parse($db, $query);

    $cursor = oci_new_cursor($db);

    $result = array();

    oci_bind_by_name($stmt, ":O_CURSOR", $cursor, -1, OCI_B_CURSOR);
    oci_bind_by_name($stmt, ":I_ID_EMPL", $id_empleado);
    $anio = "2018";
    $programa = "0100301";
    $tipo = 2;
    oci_bind_by_name($stmt, ":I_PLNE_ANIO", $anio);
    oci_bind_by_name($stmt, ":I_CTLG_PROGRAMA", $programa);
    oci_bind_by_name($stmt, ":I_TIPO", $tipo);


    if (oci_execute($stmt)) {
        oci_execute($cursor);
        oci_fetch_all($cursor, $result, null, null, OCI_FETCHSTATEMENT_BY_ROW);

        $response = array("status" => 1,
            "Secciones" => $result,
            "message" => "Consulta exitosa");
    } else {
        $response = array("status" => 0,
            "Secciones" => $result,
            "message" => "Error en la consulta");
    }

} else {
    $response = array("status" => 0,
        "Secciones" => "$result",
        "message" => "Método no aceptado");
}

header("Content-Type: application/json");
echo json_encode($response);
