<?php
header("Content-Type:application/json");
require_once "../connection/database.php";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $codigo = isset($_GET["classCode"]) ? $_GET["classCode"] : "";
    $audt_usuario = isset($_GET["auxId"]) ? $_GET["auxId"] : "";
    $hora_cierre = isset($_GET["closeTime"]) ? $_GET["closeTime"] : "";
    $asis_fecha = isset($_GET['closeDate']) ? $_GET['closeDate'] : "";

    //FORMATO FECHA
    $fecha = strtotime($asis_fecha);
    $asis_fecha = date("d/M/Y", $fecha);

    //QUERY MATRICULAS
    $plan_estudios = substr($codigo, 0, 5);
    $catalogo_seccion = substr($codigo, 5, 7);
    $matriculas_query = "SELECT ID_MTRI
                           FROM EDUTV_MATRICULAS
                           WHERE
                                 ID_PLNE = '{$plan_estudios}' AND
                                 CTLG_SECCION = '{$catalogo_seccion}' AND
                                 ID_MTRI NOT IN (SELECT ID_MTRI FROM EDUTV_ASISTENCIA WHERE ASIS_FECHA = TO_CHAR(CURRENT_DATE))";
    $stmt = oci_parse($db, $matriculas_query);

    $asis_ctlgasis = "0107703"; //FALTA
    $hora_salida = "";
    $id_area = "";

    $cont_total = 0;
    $cont_exito = 0;

    if (oci_execute($stmt)) {
        while ($fila = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
            foreach ($fila as $id_mtri) {
            
                $cont_total = $cont_total + 1;
                $query_asistencia = 'begin "PK_ASISTENCIA"."SP_GRABAR"(:I_ID_MTRI, :I_ASIS_FECHA, :I_ASIS_HORAENTRADA, :I_ASIS_HORASALIDA, :I_CTLG_ASISTENCIA, :I_AUDT_USUARIO, :I_ID_AREA); end;';

                oci_bind_by_name($stmt, ":I_ID_MTRI", $id_mtri);
                oci_bind_by_name($stmt, ":I_ASIS_FECHA", $asis_fecha);
                oci_bind_by_name($stmt, ":I_ASIS_HORAENTRADA", $hora_cierre);
                oci_bind_by_name($stmt, ":I_ASIS_HORASALIDA", $hora_salida);
                oci_bind_by_name($stmt, ":I_CTLG_ASISTENCIA", $asis_ctlgasis);
                oci_bind_by_name($stmt, ":I_AUDT_USUARIO", $audt_usuario);
                oci_bind_by_name($stmt, ":I_ID_AREA", $id_area);

                oci_execute($stmt);

                $e = oci_error($stmt);

                if ($e) {

                } else {
                    $cont_exito = $cont_exito + 1;
                }

                oci_free_statement($query_asistencia);
            
            }
        }
        if($cont_total == 0){
            $message = "Asistencia Cerrada. No se registraron faltas para esta sección";
        } else{
            $message = "Se registraron correctamente {$cont_exito} de {$cont_total} faltas para esta sección.";
        }

        $response = array(
            "status" => 1,
            "message" => $message
        );
    } else {
        $response = array(
            "status" => 0,
            "message" => "Hubo un error, por favor intente nuevamente."
        );
    }

}

header("Content-Type: application/json");
echo json_encode($response);