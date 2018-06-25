<?php
header("Content-Type:application/json");
require_once "../connection/database.php";


if($_SERVER["REQUEST_METHOD"] == "GET"){
     //Get Data
     //$nombre = isset($_GET['nombre']) ? mysql_real_escape_string($_GET['nombre']) : "";

     $mtri_qr = $_GET['mtri_qr'];

     $query = "SELECT ID_MTRI FROM EDUTV_MATRICULAS WHERE MTRI_QR = '{$mtri_qr}'";

     $stmt = oci_parse($db, $query);
     $e = oci_error($stmt);
     
     if(oci_execute($stmt)){
          while($fila = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)){
            foreach($fila as $mtri){
              $id_mtri = $mtri;
              break;
            }
            break;
          }
          if($id_mtri == null) $id_mtri = "not found";
          $response = array("status" => 1,
                            "id_mtri" => $id_mtri);
     }
     else{
          $response = array("status" => 0,
                            "id_mtri" => "not found");
     }

}
else{
     $response = array("status" => 0,
                       "id_mtri" => "El método no es válido");
}

header("Content-Type: application/json");
echo json_encode($response);

oci_free_statement($stmt);
oci_close($db);
?>