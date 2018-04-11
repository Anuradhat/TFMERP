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
            'cprice' => $row['CostPrice'],
            'sprice' => $row['SalePrice']
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


//Invoice auto complete for invoice no
if (isset($_POST['InvoiceNo'])) {
    $query = $_POST['InvoiceNo'];
    $result = $db->query ("call spInvoiceAutoComplete({$query})");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array[] = array (
            'text' => $row['InvoiceNo'],
            'value' => $row['InvoiceNo'],
            'LocationCode' => $row['LocationCode'],
            'InvDate' => $row['InvDate'],
            'CustomerCode' => $row['CustomerCode']);
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
if (isset($_POST['V1']) && isset($_POST['StockCode']) && isset($_POST['SerialNo'])) {
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


if (isset($_POST['V2']) && isset($_POST['StockCode']) && isset($_POST['SerialNo'])) {
    $StockCode = remove_junk($db->escape($_POST['StockCode']));
    $SerialNo = remove_junk($db->escape($_POST['SerialNo']));
    $LocationCode = remove_junk($db->escape($_POST['LocationCode']));
    $BinCode = remove_junk($db->escape($_POST['BinCode']));

    $result = $db->query("call spValidateSerailV2('{$StockCode}','{$SerialNo}','{$LocationCode}');");
    $array = array();
    $row = $db->fetch_assoc($result);

    if($row['SetailStatus'] == "OK")
        echo 'true';
    else
        echo 'false';
}

if (isset($_POST['V3']) && isset($_POST['ProductCode']) && isset($_POST['SerialNo'])) {
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $SerialNo = remove_junk($db->escape($_POST['SerialNo']));
    $LocationCode = remove_junk($db->escape($_POST['LocationCode']));
    $BinCode = remove_junk($db->escape($_POST['BinCode']));

    $result = $db->query("call spValidateSerailV3('{$ProductCode}','{$SerialNo}','{$LocationCode}');");
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
            'ProductCode' => $row['ProductCode'],
            'SerialNo' => $row['SerialNo'],
            'ProductDesc' => $row['ProductDesc'],
            'CostPrice' => $row['CostPrice'],
            'SalePrice' => $row['SalePrice']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode($array);
}

//Get customer details from customer code
if (isset($_POST['Customer'])) {
    $Customer = remove_junk($db->escape($_POST['Customer']));

    $result = $db->query("call spSelectCustomerFromCode('{$Customer}');");
    $array = array();

    while ($row = $db->fetch_assoc($result)) {
        $array = array (
            'CustomerCode' => $row['CustomerCode'],
            'CustomerName' => $row['CustomerName'],
            'Credit' => $row['Credit']
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


//Get customer purchase order details
if (isset($_POST['CustomerPoCode'])) {
    $CustomerPoCode = $_POST['CustomerPoCode'];

    $result = $db->query ("call spSelectCustomerPurchaseOrderHFromCode('{$CustomerPoCode}');");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array[] = array (
            'CusPoNo' => $row['CusPoNo'],
            'SoNo' => $row['SoNo'],
            'CustomerCode' => $row['CustomerCode'],
            'LocationCode' => $row['LocationCode'],
            'ReferenceNo' => $row['ReferenceNo'],
            'WorkFlowCode' => $row['WorkFlowCode'],
            'SalesmanCode' => $row['SalesmanCode'],
            'Remarks' => $row['Remarks'],
            'DeliveryCode' => $row['DeliveryCode'],
            'CusPoDate' => $row['CusPoDate']
        );
    }
    //RETURN JSON ARRAY
    echo json_encode ($array);
}



if (isset($_POST['arr'])) {
   $arr_card = array();
   $arr_card = $_POST['arr'];
   $_SESSION['card'] = $arr_card;

   $ToatlCardPayment = 0;
   foreach($arr_card  as &$value)
   {
       $ToatlCardPayment += $value["value"];
   }

    echo ($ToatlCardPayment);
}

if (isset($_POST['chequearr'])) {
    $arr_cheque = array();
    $arr_cheque = $_POST['chequearr'];
    $_SESSION['cheque'] = $arr_cheque;

    $ToatlChequePayment = 0;
    foreach($arr_cheque  as &$value)
    {
        $ToatlChequePayment += $value["value"];
    }

    echo ($ToatlChequePayment);
}

if (isset($_POST['banktranarr'])) {
    $arr_banktrn = array();
    $arr_banktrn = $_POST['banktranarr'];
    $_SESSION['banktrn'] = $arr_banktrn;

    $ToatlBankTrnPayment = 0;
    foreach($arr_banktrn  as &$value)
    {
        $ToatlBankTrnPayment += $value["value"];
    }

    echo ($ToatlBankTrnPayment);
}

//Get Product details from serial no (for credit note purpose)
if (isset($_POST['CreditNoteInvoiceNo'])) {
    $InvoiceNo= $_POST['CreditNoteInvoiceNo'];
    $SerialCode= $_POST['SerialCode'];

    $result = $db->query ("call spSelectProductDetailsFromInvoiceFromSerialCode('{$InvoiceNo}','{$SerialCode}')");
    $array = array();
    while ($row = $db->fetch_assoc($result)) {
        $array = array (
            'SerialNo' => $row['SerialNo'],
            'StockCode' => $row['StockCode'],
            'ProductCode' => $row['ProductCode'],
            'Description' => $row['Description'],
            'SalePrice' =>   $row['SalePrice']
            );
    }

    //RETURN JSON ARRAY
    echo json_encode($array);

}

?>
