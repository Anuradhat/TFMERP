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

//Get serail details
if (isset($_POST['StockCode']) && isset($_POST['SerialNo'])) {
    $StockCode = remove_junk($db->escape($_POST['StockCode']));
    $SerialNo = remove_junk($db->escape($_POST['SerialNo']));
    $LocationCode = remove_junk($db->escape($_POST['LocationCode']));
    $BinCode = remove_junk($db->escape($_POST['BinCode']));

    $result = $db->query("call spValidateSerail('{$StockCode}','{$SerialNo}','{$LocationCode}','{$BinCode}');");
    $array = array();
    $row = $db->fetch_assoc($result);

    if($row['SetailStatus'] == "OK")
        echo 'true';
    else
         echo 'false';
}


//Get Stock details from serial
if (isset($_POST['SerialCode']) && isset($_POST['LocationCode'])) {
    $SerialCode = remove_junk($db->escape($_POST['SerialCode']));
    $LocationCode = remove_junk($db->escape($_POST['LocationCode']));

    $result = $db->query("call spSelectStockFromSerial('{$SerialCode}','{$LocationCode}');");
    $array = array();

    while ($row = $db->fetch_assoc($result)) {
        $array = array (
            'StockCode' => $row['StockCode'],
            'SerialNo' => $row['SerialNo'],
            'ProductDesc' => $row['ProductDesc'],
            'CostPrice' => $row['CostPrice'],
            'SalePrice' => $row['SalePrice'],
            'SIH' => $row['SIH']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode($array);
}



//Product code auto generate
if (isset($_POST['stockcode'])) {
    $query = $_POST['stockcode'];
    $LocationCode = $_POST['LocationCode'];

    $result = $db->query ("call spStockAutoComplete('{$query}','{$LocationCode}');");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array[] = array (
            'text' => $row['StockCode'].' | '.$row['ProductDesc'],
            'value' => $row['StockCode'],
            'cprice' => $row['CostPrice'],
            'sprice' => $row['SalePrice'],
            'sih' => $row['SIH']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}


//Get Salesorder details
if (isset($_POST['SalesOrderCode'])) {
    $SalesOrderCode = $_POST['SalesOrderCode'];

    $result = $db->query ("call spSelectSalesOrderHFromCode('{$SalesOrderCode}');");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array[] = array (
            'SoNo' => $row['SoNo'],
            'LocationCode' => $row['LocationCode'],
            'CustomerCode' => $row['CustomerCode'],
            'SalesmanCode' => $row['SalesmanCode'],
            'WorkFlowCode' => $row['WorkFlowCode'],
            'Remarks' => $row['Remarks'],
            'ValidThru' => $row['ValidThru'],
            'SoDate' => $row['SoDate']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}


?>
