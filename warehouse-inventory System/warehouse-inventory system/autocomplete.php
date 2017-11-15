<?php
ob_start();
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);



if (isset($_POST['productcode'])) {
    $query = $_POST['productcode'];
    $result = $db->query ("call spProductAutoComplete('{$query}')");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array[] = array (
            'text' => $row['ProductCode'].' | '.$row['ProductDesc'],
            'value' => $row['ProductCode'],
            'cprice' => $row['CostPrice']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}


if (isset($_POST['PRNo'])) {
    $query = $_POST['PRNo'];
    $result = $db->query ("call spPurchaseRequisitionAutoComplete({$query})");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array[] = array (
            'text' => $row['PRNo'],
            'value' => $row['PRNo'],
            'ProcessedFlg' => $row['ProcessedFlg'],
            'SupplierCode' => $row['SupplierCode'],
            'PrDate' => $row['PrDate'],
            'Remarks' => $row['Remarks']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}


?>
