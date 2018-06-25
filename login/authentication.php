<?php
header("Content-Type:application/json");
require_once "../connection/database.php";


$result = array();

if($_SERVER["REQUEST_METHOD"] == "GET"){
     //Get Data
     $username = isset($_GET['username']) ? $_GET['username'] : "";
     $password = isset($_GET['password']) ? $_GET['password'] : "";

     $query = 'begin "SEGPK_USUARIO"."SP_AUTENTICAR" (:O_CURSOR, :I_USER_NOMBRE, :I_USER_CLAVE); end;';

     $stmt = oci_parse($db, $query);

     $cursor = oci_new_cursor($db);

     oci_bind_by_name($stmt, ":O_CURSOR", $cursor, -1, OCI_B_CURSOR);
     oci_bind_by_name($stmt, ":I_USER_NOMBRE", $username);
     oci_bind_by_name($stmt, ":I_USER_CLAVE", $password);
      
     if(oci_execute($stmt)){
          oci_execute($cursor);
          oci_fetch_all($cursor, $result, null, null, OCI_FETCHSTATEMENT_BY_ROW);

          $response = current($result);
     }
     else{
          $response = current($result);
     }
}
else{
     $response = current($result);
}

header("Content-Type: application/json");
echo json_encode($response);
?>