<?php
ob_start();
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);


//Product code auto generate
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

//Purchase requisition auto generate for PR edit form
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

 //Purchase requisition auto generate for PO creation form
if (isset($_POST['PRNoForPO'])) {
    $query = $_POST['PRNoForPO'];
    $result = $db->query ("call spPurchaseRequisitionAutoCompleteForPO({$query})");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array[] = array (
            'text' => $row['PRNo'],
            'value' => $row['PRNo'],
            'SupplierCode' => $row['SupplierCode'],
            'Remarks' => $row['Remarks']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}




//Get po headrer some details
if (isset($_POST['_PONoForHeader'])) {
    $PONo = remove_junk($db->escape($_POST['_PONoForHeader']));
    $result = $db->query("call spSelectAllPOHeaderDetailsFromPONo('{$PONo}');");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array = array (
            'PRNo' => $row['PRNo'],
            'PoDate' => $row['PoDate'],
            'WorkFlowCode' => $row['WorkFlowCode'],
            'Remarks' => $row['Remarks']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode($array);
}





?>
