<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Invoice';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Invoice Create');

$default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');
$Required_CusPO = ReadSystemConfig('RequiredCustomerPO');

$all_locations = find_by_sql("call spSelectAllLocations();");
$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");

$arr_header = array();
$arr_item = array();

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings()) // $session->msg == null
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
    unset($_SESSION['card']);
    unset($_SESSION['cheque']);
    unset($_SESSION['banktrn']);
    unset($_SESSION['Cash']);
    unset($_SESSION['DiscountAmount']);
    unset($_SESSION['LocationCode']);
}

if($_SESSION['header'] != null) $arr_header = $_SESSION['header'];
if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];

//After page refresh load bin details
$all_CPO =  find_by_sql("call spSelectReleseCustomerPurchaseOrderFromCustomerCode('{$arr_header['CustomerCode']}');");
?>

<?php

if(isset($_POST['create_invoice'])){

    if($_POST['create_invoice'] == "Payment")
    {
        $req_fields = array('LocationCode','CustomerCode','CustomerPoCode','SalesmanCode');

        validate_fields($req_fields);


        if(empty($errors))
        {
            $p_LocationCode  = remove_junk($db->escape($_POST['LocationCode']));
            $p_CustomerCode  = remove_junk($db->escape($_POST['CustomerCode']));
            $p_CustomerPoCode  = remove_junk($db->escape($_POST['CustomerPoCode']));
            $p_SalesmanCode = remove_junk($db->escape($_POST['SalesmanCode']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));
            $date    = make_date();
            $user =  current_user();


            $arr_header = array('LocationCode'=>$p_LocationCode,'CustomerCode'=>$p_CustomerCode,
                                    'CustomerPoCode'=>$p_CustomerPoCode,'SalesmanCode'=>$p_SalesmanCode,'Remarks'=>$p_Remarks,
                                     'GrossAmount' => 0,'NetAmount' => 0, 'Discount' => 0);

            $_SESSION['header'] = $arr_header;

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {

                    //Check transaction qty
                    $IsQtyExist = true;

                    foreach($arr_item as $row => $value)
                        if ($value[4] <= 0)
                            $IsQtyExist = false;

                    if(!$IsQtyExist)
                    {
                        //$session->msg("d", "Some invoice item qty not found.");
                        //redirect('create_invoice.php',false);
                        $flashMessages->warning('Some invoice item qty not found.','create_invoice.php');
                    }


                    //******* Check with SIH ***************************************
                    foreach($arr_item as $row => $value)
                    {
                        if (SelectStockSIHFormProduct($value[0],$p_LocationCode) < $value[4])
                        {
                            //$session->msg("d", "Some invoice qty is greater than SIH.");
                            //redirect('create_invoice.php',false);
                            $flashMessages->warning('Some invoice qty is greater than SIH.','create_invoice.php');
                            exit;
                        }
                    }

                    //********************** Check serial qty ************************
                    foreach($arr_item as $row => $value)
                    {
                        $ProductCode = $value[0];
                        $InvQty = $value[4];

                        if($InvQty > 0)
                        {
                            $SerialCount = count($value[6]);
                            if($InvQty != $SerialCount)
                            {
                                $flashMessages->warning('Invoice serial details are invalid. Reference: '.$ProductCode,'create_invoice.php');
                                //$session->msg("d", "Invoice serial details are invalid. Reference: ");
                                //redirect('create_invoice.php',false);
                                exit;
                            }
                        }
                    }

                    //Invoice Amount
                      $GrossAmount = 0;
                     foreach($arr_item as $row => $value)
                     {
                         $GrossAmount += $value[3] * $value[4];
                     }

                     $_SESSION['DiscountAmount'] = $_SESSION['DiscountAmount'] == null ? 0 : $_SESSION['DiscountAmount'];

                    //Gross Amount
                     $arr_header = ChangValueOfArray($arr_header,'GrossAmount',$GrossAmount);
                    //Discount Amount
                     $arr_header = ChangValueOfArray($arr_header,'Discount',$_SESSION['DiscountAmount']);
                    //Net Amount
                     $arr_header = ChangValueOfArray($arr_header,'NetAmount',($GrossAmount - $_SESSION['DiscountAmount']));
                    $_SESSION['header'] = $arr_header;

                    //Redirect to payment page
                    redirect('invoice_payment.php',false);
            }
            else
            {
                //$session->msg("w",' Invoice item(s) not found!');
                //redirect('create_invoice.php',false);
                $flashMessages->warning('Invoice item(s) not found!','create_invoice.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'create_invoice.php');
            //$session->msg("d", $errors);
            //redirect('create_invoice.php',false);
        }

    }
}


if (isset($_POST['_LocationCode'])) {
    $LocationCode = remove_junk($db->escape($_POST['_LocationCode']));
    $_SESSION['LocationCode'] = $LocationCode;

   return;
}

if (isset($_POST['InvoiceNo'])) {
    $InvoiceNo = remove_junk($db->escape($_POST['InvoiceNo']));
    $_SESSION['InvoiceNo'] = $InvoiceNo;

    echo 'redirect';
}


if (isset($_POST['_productcode'])) {
    $productcode = remove_junk($db->escape($_POST['_productcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$productcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_invoicedetails.php');
}

if (isset($_POST['DiscountAmount'])) {
    $DiscountAmount = remove_junk($db->escape($_POST['DiscountAmount']));
    $_SESSION['DiscountAmount'] = $DiscountAmount;

    return include('_partial_invoicedetails.php');
}


if (isset($_POST['ProductCode']) && isset($_POST['InvQty'])) {
    $_SESSION['ProductCode'] = $_POST['ProductCode'];
    $_SESSION['LocationCode'] = $_POST['LocationCode'];
    $_SESSION['InvQty'] = $_POST['InvQty'];

    return include('_partial_invoiceserial.php');
}


if (isset($_POST['ProductCode']) && isset($_POST['arr'])) {
    $arr_serial = array();

    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $arr_serial = $db->escape_array($_POST['arr']);


    //Get all sessions values
    $arr_item = $_SESSION['details'];

    $arr_item = ChangValueFromListOfArray($arr_item,$ProductCode,6,$arr_serial);

    $_SESSION['details'] = $arr_item;
}

if (isset($_POST['Add'])) {
    $LocationCode = remove_junk($db->escape($_POST['LocationCode']));
    $StockCode = remove_junk($db->escape($_POST['StockCode']));
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $SerialCode = remove_junk($db->escape($_POST['SerialCode']));
    $ProductDesc = remove_junk($db->escape($_POST['ProductDesc']));
    $CostPrice = remove_junk($db->escape($_POST['CostPrice']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    //$Qty = remove_junk($db->escape($_POST['Qty']));


    $arr_item = $_SESSION['details'];

    if($LocationCode == "" || $StockCode == "" || $ProductCode == "")
    {
        $flashMessages->warning('Location or product code is not found!');
    }
    else if($SalePrice == "")
    {
        $flashMessages->warning('Invalid sales price.');
    }
    else
    {

        if(ExistInArray($arr_item,$ProductCode))
        {
            $search_item = array();
            $serial_item = array();

            //Get Result
            $search_item = ArraySearch($arr_item,$ProductCode);
            $serial_item = $search_item[6];

            //Check serial is exist
            if (ExistInArray($serial_item,$SerialCode))
            {
                $flashMessages->warning('This item exist in the invoice.');
            }
            else
            {
                //Add new serial to array
                array_push($serial_item,$SerialCode);
                
                //Change qty
                $Qty = $search_item[4] + 1;
                $SalePrice = $search_item[3];

                $ToatlTax = 0;

                $product = find_by_sp("call spSelectProductFromCode('{$ProductCode}');");

                if(filter_var($product["Tax"],FILTER_VALIDATE_BOOLEAN))
                {
                    $ProductTax = find_by_sql("call spSelectProductTaxFromProductCode('{$ProductCode}');");  
                    foreach($ProductTax as &$pTax)
                    {
                        $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$pTax["TaxCode"]}');");
                        foreach($TaxRatesM as &$TaxRt)
                        {
                            $ToatlTax += $TaxRt["TaxRate"];
                        }
                    }
                }

                $Amount = $SalePrice * $Qty;
                $TaxAmount = round((($Amount * $ToatlTax)/100));
                $ToatlAmount = $TaxAmount + $Amount;

                //Change Qty
                $arr_item =  ChangValueFromListOfArray($arr_item,$ProductCode,4,$Qty);

                //Change Tax Amount
                $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,7,$TaxAmount);

                //Change amount
                $arr_item =  ChangValueFromListOfArray($arr_item,$ProductCode,5,$ToatlAmount);

                //Chnage serial
                $arr_item =  ChangValueFromListOfArray($arr_item,$ProductCode,6,$serial_item);

                $_SESSION['details'] = $arr_item;
            }
        }
        else
        {
            $arr_serial[] = array($SerialCode);
            $ToatlTax = 0;

            $product = find_by_sp("call spSelectProductFromCode('{$ProductCode}');");

            if(filter_var($product["Tax"],FILTER_VALIDATE_BOOLEAN))
            {
                $ProductTax = find_by_sql("call spSelectProductTaxFromProductCode('{$ProductCode}');");  
                foreach($ProductTax as &$pTax)
                {
                    $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$pTax["TaxCode"]}');");
                    foreach($TaxRatesM as &$TaxRt)
                    {
                        $ToatlTax += $TaxRt["TaxRate"];
                    }
                }
            }

            
            $TaxAmount = round((($SalePrice * $ToatlTax)/100));
            $ToatlAmount = $TaxAmount + $SalePrice;

            $arr_item[] = array($ProductCode,$ProductDesc,$CostPrice,$SalePrice,1,$ToatlAmount,$arr_serial,$TaxAmount);
            $_SESSION['details'] = $arr_item;
        }
    }

    return include('_partial_invoicedetails.php');
}

if (isset($_POST['CustomerChanged'])) {
    $arr_item = array();

    $_SESSION['details'] = null;

    return include('_partial_invoicedetails.php');
}


if (isset($_POST['CustomerCode'])) {

    $CustomerCode = remove_junk($db->escape($_POST['CustomerCode']));


    $default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');
    $all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");
    $Customer =    find_by_sp("call spSelectCustomerFromCode('{$CustomerCode}');");

    echo "<option value=''>Select Salesman</option>";
    foreach($all_salesrep as &$value){
        $Selected = $value["EpfNumber"] == $Customer["SalesPersonCode"] ? "selected":"";
        echo "<option value ={$value["EpfNumber"]}  {$Selected} >{$value["EmployeeName"]}</option>";
    }
    return;
}


if (isset($_POST['Customer'])) {
    $_SESSION['details']  = null;

    $CustomerCode = remove_junk($db->escape($_POST['Customer']));

    $all_CPO = find_by_sql("call spSelectReleseCustomerPurchaseOrderFromCustomerCode('{$CustomerCode}');");

    echo "<option value=''>Select Customer PO</option>";
    foreach($all_CPO as &$value){
        echo "<option value ={$value["CusPoNo"]}>{$value["CusPoNo"]}</option>";
    }

    return;
}


if (isset($_POST['FillTable']) &&  isset($_POST['CustomerPoCode'])) {
    $_SESSION['details']  = null;

    $CustomerPoCode = remove_junk($db->escape($_POST['CustomerPoCode']));

    $CPO_Details = find_by_sql("call spSelectCustomerPurchaseOrderDFromCode('{$CustomerPoCode}');");
    $arr_serial = array();

    foreach($CPO_Details as &$value){
    //    $product = find_by_sp("call spSelectProductFromCode('{$value["ProductCode"]}');");

    //    $ToatlTax = 0;

    //    if(filter_var($product["Tax"],FILTER_VALIDATE_BOOLEAN))
    //    {
    //        $ProductTax = find_by_sql("call spSelectProductTaxFromProductCode('{$value["ProductCode"]}');");  
    //        foreach($ProductTax as &$pTax)
    //        {

    //            $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$pTax["TaxCode"]}');");
    //            foreach($TaxRatesM as &$TaxRt)
    //            {
    //                $ToatlTax += $TaxRt["TaxRate"];
    //            }
    //        }
    //    }

    //    $TaxAmount = round((($value["Amount"] * $ToatlTax)/100));
    //    $ToatlAmount = $TaxAmount + $value["Amount"];

        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["CostPrice"],$value["SellingPrice"],$value["Qty"],$value["Amount"],$arr_serial,$value["TaxAmount"]);
    }
    $_SESSION['details'] = $arr_item;

    return include('_partial_invoicedetails.php');
}


if (isset($_POST['_RowNo'])) {
    $ProductCode = remove_junk($db->escape($_POST['_RowNo']));
    $serchitem = ArraySearch($arr_item,$ProductCode);

    return include('_partial_invoiceitem.php');
}

if (isset($_POST['Edit'])) {

    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $ExcludeTax = remove_junk($db->escape($_POST['ExcludeTax']));
    $Qty = remove_junk($db->escape($_POST['Qty']));

    $arr_item = $_SESSION['details'];


    $product = find_by_sp("call spSelectProductFromCode('{$ProductCode}');");

    $ToatlTax = 0;
    $TaxAmount = 0;
    $Amount = 0;

    if(!filter_var($ExcludeTax,FILTER_VALIDATE_BOOLEAN)){
        if(filter_var($product["Tax"],FILTER_VALIDATE_BOOLEAN))
        {
            $ProductTax = find_by_sql("call spSelectProductTaxFromProductCode('{$ProductCode}');");  
            foreach($ProductTax as &$pTax)
            {

                $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$pTax["TaxCode"]}');");
                foreach($TaxRatesM as &$TaxRt)
                {
                    $ToatlTax += $TaxRt["TaxRate"];
                }
            }
        }
    }

    $Amount = $Qty * $SalePrice;
    $TaxAmount = round((($Amount * $ToatlTax)/100));
    $ToatlAmount = $TaxAmount + $Amount;

    //Change Qty
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,4,$Qty);

    //Change Tax Amount
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,7,$TaxAmount);

    //Change Total Amount
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,5,$ToatlAmount);

    $_SESSION['details'] = $arr_item;

    return include('_partial_invoicedetails.php');
}
?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Create Invoice
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Invoice</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<!-- Main content -->
<section class="content">
    <!-- Your Page Content Here -->
    <form method="post" action="create_invoice.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="create_invoice" class="btn btn-primary" value="Payment">&nbsp;Payment&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                        </div>
                        <button type="button" class="btn btn-info pull-right" name="print_invoice" onclick="printInvoice(this, event);" value="printInvoice">&nbsp;&nbsp;Print&nbsp;&nbsp;</button>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div id="message" class="col-md-12"><?php include('_partial_message.php'); ?> </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Basic Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Invoice No</label>
                                <input type="text" class="form-control" name="InvoiceNo" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Customer Purchase Order</label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerPoCode" id="CustomerPoCode"  onchange="FillCPODetails();" <?php if ($Required_CusPO == 1) echo "required=required"  ?> >
                                <option value="">Select Customer PO</option><?php  foreach ($all_CPO as $cpo): ?>
                                <option value="<?php echo $cpo['CusPoNo'] ?>" <?php if($cpo['CusPoNo'] == $arr_header["CustomerPoCode"]) echo "selected";  ?>><?php echo $cpo['CusPoNo'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.."><?php echo remove_junk($arr_header['Remarks']) ?></textarea>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control select2" style="width: 100%;" name="LocationCode" id="LocationCode" required="required" onchange="LocationChange();">
                                <option value="">Select Location</option><?php  foreach ($all_locations as $loc): ?>
                                <option value="<?php echo $loc['LocationCode'] ?>" <?php if($loc['LocationCode'] == $arr_header["LocationCode"]) echo "selected";  ?>><?php echo $loc['LocationName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="SoDate" id="SoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Customer</label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required" onchange="FillCPO();">
                                <option value="">Select Customer</option><?php  foreach ($all_Customers as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>" <?php if($cus['CustomerCode'] == $arr_header["CustomerCode"]) echo "selected";  ?>><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Salesman</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesmanCode" id="SalesmanCode" required="required">
                                <option value="">Select Salesman</option><?php  foreach ($all_salesrep as $srep): ?>
                                <option value="<?php echo $srep['EpfNumber'] ?>" <?php if($srep['EpfNumber'] == $arr_header["SalesmanCode"]) echo "selected";  ?>><?php echo $srep['EmployeeName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <!-- /.box-header -->
        <form method="post" action="create_customerpo.php">
            <input type="hidden" value="create_customerpo"name="create_customerpo" />

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Serial Code</label>
                            <input type="text" class="form-control" name="SerialCode" id="SerialCode" placeholder="Serial Code" required="required" autocomplete="off" disabled/>
                            <input type="hidden" name="ProductCode" id="ProductCode" />
                            <input type="hidden" name="StockCode" id="StockCode" />
                        </div>

                        <!--<div class="form-group">
                         <label>Qty</label>
                          <input type="number" class="form-control integer" name="pQty" id="Qty" placeholder="Qty" required="required" />
                         </div>-->

                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product Description</label>
                            <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hProductDesc" id="hProductDesc" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cost Price</label>
                            <input type="text" class="form-control decimal" name="CostPrice" id="CostPrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Cost Price" required="required" disabled readonly="readonly" />
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sale Price</label>
                            <input type="text" class="form-control decimal" name="SalePrice" id="SalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Sale Price" required="required" disabled/>
                        </div>

                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-info" id="item"  onclick="AddItem(this, event);" value="item" disabled>&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success" disabled>&nbsp;Reset&nbsp;</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
  </div>




    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Invoice Item(s)</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <?php include('_partial_invoicedetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">
    function printInvoice(ctrl, event) {
        event.preventDefault();
        bootbox.prompt({
            title: "Please enter invoice number.",
            inputType: 'number',
             buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel',
                    value: '0'
             },
                confirm: {
                    label: '<i class="fa fa-check"></i> Confirm',
                    value: '1'
             }
            },
            callback: function (result) {
                if (result != null)
                {
                   $('.loader').show();

                    $.ajax({
                        url: 'create_invoice.php',
                        type: 'POST',
                        data: { InvoiceNo: result },
                        success: function (data) {
                            //window.location = 'invoice.php';
                            window.open('invoice.php', "Customer Invoice", "location=0,width=500,height=650");
                        },
                        complete: function (data) {
                            $('.loader').fadeOut();
                        }
                    });

                }
            }
         
        });

    }


    function AddItem(ctrl, event) {
        //event.preventDefault();
        var LocationCode = $('#LocationCode').val();
        var StockCode = $('#StockCode').val();
        var ProductCode = $('#ProductCode').val();
        var SerialCode = $('#SerialCode').val();
        var ProductDesc = $('#ProductDesc').val();
        var CostPrice = $('#CostPrice').val()
        var SalePrice = $('#SalePrice').val();
        var Qty = 1; //$('#Qty').val();

        if (LocationCode == "") {
            $("#LocationCode").focus();
            bootbox.alert('Please select stock location.');
        }
        else if ($('#StockCode').val() == "" || $('#CostPrice').val() == "" || $('#CostPrice').val() <= 0) {
            $("#SerialCode").focus();
            bootbox.alert('Please enter correct item serial.');
        }
        else if ($('#SalePrice').val() == "" || $('#SalePrice').val() <= 0) {
            $("#SalePrice").focus();
            bootbox.alert('Please enter valid sale price.');
        }
        else if (Qty <= 0) {
            bootbox.alert('Please enter valid item qty.');
            $("#Qty").focus();
        }
        else {
            $('.loader').show();

            $.ajax({
                url: 'create_invoice.php',
                type: "POST",
                data: { Add: 'Add', LocationCode: LocationCode, StockCode: StockCode, SerialCode: SerialCode, ProductCode: ProductCode, ProductDesc: ProductDesc, CostPrice: CostPrice, SalePrice: SalePrice, Qty: Qty },
                success: function (result) {
                    $("#table").html(result);
                    $('#message').load('_partial_message.php');
                },
                complete: function (result)
                {
                    $('#StockCode').val('');
                    $('#ProductCode').val('');
                    $('#SerialCode').val('');
                    $('#ProductDesc').val('');
                    $('#SalePrice').val('');
                    $('#CostPrice').val('');
                    //$('#Qty').val('');

                    $('.loader').fadeOut();
                    $('#SerialCode').focus();
                }
            });
        }
    }

    //function LocationChange()
    //{
    //    $.ajax({
    //        url: "create_invoice.php",
    //        type: "POST",
    //        data: { LocationChange: 'OK' },
    //        success: function (result) {
    //            $("#table").html(result);
    //            $('#message').load('_partial_message.php');
    //        }
    //    });
    //}


    //$(document).ready(function () {
    //    $('#StockCode').typeahead({
    //        hint: true,
    //        highlight: true,
    //        minLength: 3,
    //        source: function (request, response) {
    //            var LocationCode = $('#LocationCode').val();
    //            $.ajax({
    //                url: "autocomplete.php",
    //                data: { stockcode: request, LocationCode: LocationCode},
    //                dataType: "json",
    //                type: "POST",
    //                success: function (data) {
    //                    items = [];
    //                    map = {};
    //                    $.each(data, function (i, item) {
    //                        var id = item.value;
    //                        var name = item.text;
    //                        var cprice = parseFloat(item.cprice).toFixed(2);
    //                        var sprice = parseFloat(item.sprice).toFixed(2);
    //                        var sih = parseFloat(item.sih);
    //                        map[name] = { id: id, name: name, cprice: cprice, sprice: sprice,sih: sih};
    //                        items.push(name);
    //                    });
    //                    response(items);
    //                    $(".dropdown-menu").css("height", "auto");
    //                }
    //            });
    //        },
    //        updater: function (item) {
    //            $('#ProductDesc').val(map[item].name.substring(map[item].name.indexOf('|') + 2));
    //            $('#hProductDesc').val(map[item].name.substring(map[item].name.indexOf('|') + 2));
    //            $('#CostPrice').val(map[item].cprice);
    //            $('#SalePrice').val(map[item].sprice);

    //            $('#SalePrice').focus();
    //            return map[item].id;
    //        }
    //    });
    //});


    $("#SerialCode").on('keyup', function (e) {
        

        var SerialCode = $('#SerialCode').val();
        var LocationCode = $('#LocationCode').val();

        if (e.keyCode == 13) {
            $('.loader').show();

            if (LocationCode == "") {
                $('.loader').fadeOut();
                bootbox.alert('Please select stock location.');
            }
            else if (SerialCode == "") {
                $('.loader').fadeOut();
                bootbox.alert('Please enter serial code.');
            }
            else
            {
                var StockCode = "";
                var SerialNo = "";
                var ProductCode = "";
                var ProductDesc = "";
                var CostPrice = 0.00;
                var SalePrice = 0.00;
                var SIH = 0;

                $.ajax({
                    url: 'autocomplete.php',
                    type: 'POST',
                    data: { SerialCode: SerialCode, LocationCode: LocationCode },
                    dataType: 'json',
                    success: function (data) {
                        jQuery(data).each(function (i, item) {
                            StockCode = item.StockCode;
                            ProductCode = item.ProductCode;
                            SerialNo = item.SerialNo;
                            ProductDesc = item.ProductDesc;
                            CostPrice = item.CostPrice;
                            SalePrice = item.SalePrice;
                            SIH = item.SIH;
                        });
                    },
                    complete: function (data) {
                        if (StockCode == "") {
                            bootbox.alert('Invalid serial code.');

                            $('#StockCode').val('');
                            $('#ProductCode').val('');
                            $('#ProductDesc').val('');
                            $('#SalePrice').val(0.00);
                            $('#CostPrice').val(0.00);
                            $('#SerialCode').focus();

                            $('.loader').fadeOut();
                        }
                        else
                        {
                            $('#StockCode').val(StockCode);
                            $('#ProductCode').val(ProductCode);
                            $('#ProductDesc').val(ProductDesc);
                            $('#CostPrice').val(parseFloat(CostPrice).toFixed(2));
                            $('#SalePrice').val(parseFloat(SalePrice).toFixed(2));


                            $('#SalePrice').focus();

                            $('.loader').fadeOut();
                        }
                    }
                });

            }
      }
    });


    function FillCPO() {
        $('.loader').show();

        var Customer = $('#CustomerCode').val();

        $.ajax({
            url: "create_invoice.php",
            type: "POST",
            data: { Customer: Customer },
            success: function (result) {
                $("#CustomerPoCode").html(""); // clear before appending new list
                $("#CustomerPoCode").html(result);
            }
        });


        $.ajax({
            url: "create_invoice.php",
            type: "POST",
            data: { CustomerChanged: 'OK' },
            success: function (result) {
                $("#table").html(result);
                $('#message').load('_partial_message.php');
                $('.loader').fadeOut();
            }
        });
    }


    function FillCPODetails() {
        $('.loader').show();
        var CustomerPoCode = $('#CustomerPoCode').val();

        if (CustomerPoCode == "") {
            //$('#LocationCode').val('').trigger('change');
            $('#SalesmanCode').val('').trigger('change');
        }

        //Fill header details
        $.ajax({
            url: "autocomplete.php",
            type: "POST",
            data: { CustomerPoCode: CustomerPoCode },
            dataType: 'json',
            success: function (data) {
                //Fill header details
                jQuery(data).each(function (i, item) {
                    //$('#LocationCode').val(item.LocationCode).trigger('change');
                    $('#SalesmanCode').val(item.SalesmanCode).trigger('change');
                });

            }
        });


        $.ajax({
            url: "create_invoice.php",
            type: "POST",
            data: { CustomerChanged: 'OK' },
            success: function (result) {
                $("#table").html(result);
                $('#message').load('_partial_message.php');
            }
        });


        //Fill details
        $.ajax({
            type: "POST",
            url: "create_invoice.php", // Name of the php files
            data: { FillTable: 'OK', CustomerPoCode: CustomerPoCode },
            success: function (result) {
                $("#table").html(result);
                $('.loader').fadeOut();
            }
        });
    }

    function LocationChange()
    {
        $('.loader').show();

        var LocationCode = $('#LocationCode').val();
        $.ajax({
            url: "create_invoice.php",
            type: "POST",
            data: { _LocationCode: LocationCode },
            success: function (result) {
                //$("#table").html(result);
                $('.loader').fadeOut();
            }
        });
    }

  $('#StockCode').keypress(function (e) {
      if (e.which == 13) {
          $('#SalePrice').focus();
      }
  });

  $('#SalePrice').keypress(function (e) {
      if (e.which == 13)
      {
          //$('#Qty').focus();
          var elem = document.getElementById("item");
          var evnt = elem["onclick"];
          evnt.call(elem)
      }
  });

  //$('#Qty').keypress(function (e) {
  //    if (e.which == 13) {
  //        var elem = document.getElementById("item");
  //        var evnt = elem["onclick"];
  //        evnt.call(elem);
  //    }
  //});
</script>

<?php include_once('layouts/footer.php'); ?>

