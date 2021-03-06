<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Update Quotation & Approve';
require_once('includes/load.php');

// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Quotation Update');

preventGetAction('home.php');

$default_flow = ReadSystemConfig('DefaultSOWorkFlow');
$default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');

$current_user = current_user();

$SalesOrder  = $_SESSION['SalesOrder'];
$Level  = $_SESSION['Level'];

//$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_workflows = find_by_sql("call spSelectAllWorkFlow();");
$all_locations = find_by_sql("call spSelectAllLocations();");
$all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");

$SalesOrderH = find_by_sp("call spSelectSalesOrderHFromCode('{$SalesOrder}');");

$Customer = find_by_sql("call spSelectCustomerFromCode('{$SalesOrderH["CustomerCode"]}');");


if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$arr_item = array();

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];

?>

<?php

if(isset($_POST['edit_salesorder_'])){

    if($_POST['edit_salesorder_'] == "save")
    {
        $req_fields = array();

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_SalesOrderCode  = remove_junk($db->escape($_SESSION['SalesOrder']));
            $date    = make_date();
            $user =  current_user();

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {
                //update quotation order
                try
                {

                    $So_count = find_by_sp("call spSelectSalesOrderHFromCode('{$p_SalesOrderCode}');");

                    if(!$So_count)
                    {
                        $flashMessages->warning('This quotation number not exist in the system.','edit_salesorder_.php');

                    }

                    $db->begin();

                    //Update quotation item details
                    foreach($arr_item as $row => $value)
                    {
                        $query  = "call spUpdateSalesOrderFromCode('{$p_SalesOrderCode}','{$value[0]}',{$value[2]},{$value[3]},{$value[4]},{$value[5]},{$value[6]},{$value[7]},{$value[8]},'{$date}','{$user["username"]}');";
                        $db->query($query);
                    }

                    //Transaction Approve
                    $query  = "call spTransactionApproved('004','{$SalesOrder}',{$Level});";
                    $db->query($query);


                    InsertRecentActvity("Quotation update & approve","Reference No. ".$SalesOrder);


                    $db->commit();



                    unset($_SESSION['SalesOrder']);
                    unset($_SESSION['Level']);

                    $flashMessages->success('Quotation has been successfully updated & approved.','approval_task.php?TransactionCode=004');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to update quotation. '.$ex->getMessage(),'edit_salesorder_.php');
                }

            }
            else
            {
                $flashMessages->warning('Quotation item(s) not found!','edit_salesorder_.php');

            }
        }
        else
        {
            $flashMessages->warning($errors,'edit_salesorder_.php');
        }

    }
}


if (isset($_POST['_productcode'])) {
    $productcode = remove_junk($db->escape($_POST['_productcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$productcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_sodetails.php');
}


if (isset($_SESSION['redirect'])) {
    $SO_Details = find_by_sql("call spSelectSalesOrderDFromCode('{$SalesOrder}');");

    foreach($SO_Details as &$value){
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["SellingPrice"],$value["Qty"],$value["Amount"],$value["TaxAmount"],$value["ExcludeTax"],$value["AverageCost"],$value["SalesPercentage"],$value["TaxRate"]);
    }

    $_SESSION['details'] = $arr_item;
    unset($_SESSION['redirect']);
}




if (isset($_POST['Add'])) {
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $ProductDesc = remove_junk($db->escape($_POST['ProductDesc']));
    $AverageCost = remove_junk($db->escape($_POST['AverageCost']));
    $SalesPercentage = remove_junk($db->escape($_POST['SalesPercentage']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $Qty = remove_junk($db->escape($_POST['Qty']));


    $arr_item = $_SESSION['details'];

    if($ProductCode == "")
    {
        $flashMessages->warning('Product code is not found!');
    }
    else if($SalePrice == "" || $SalePrice <= 0)
    {
        $flashMessages->warning('Invalid sales price.');
    }
    else if($Qty <= 0)
    {
        $flashMessages->warning('Invalid item qty.');
    }
    else
    {

        if(ExistInArray($arr_item,$ProductCode))
        {
            $flashMessages->warning('This item exist in the list.');
        }
        else
        {

            $product = find_by_sp("call spSelectProductFromCode('{$ProductCode}');");

            $ToatlTax = 0;

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

            $ItemAmount = $Qty * $SalePrice;
            $TaxAmount = (($ItemAmount * $ToatlTax)/100);
            $ToatlAmount = $TaxAmount + $ItemAmount;


            $arr_item[] = array($ProductCode,$ProductDesc,$SalePrice,$Qty,$ToatlAmount,$TaxAmount,0,$AverageCost,$SalesPercentage);
            $_SESSION['details'] = $arr_item;
        }
    }
    return include('_partial_sodetails.php');
}



if (isset($_POST['_RowNo'])) {
    $ProductCode = remove_junk($db->escape($_POST['_RowNo']));
    $serchitem = ArraySearch($arr_item,$ProductCode);

    return include('_partial_soitem.php');
}



if (isset($_POST['SalesOrderCode'])) {
    $_SESSION['details']  = null;

    $SalesOrderCode = remove_junk($db->escape($_POST['SalesOrderCode']));

    $SO_Details = find_by_sql("call spSelectSalesOrderDFromCode('{$SalesOrderCode}');");

    foreach($SO_Details as &$value){
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["SellingPrice"],$value["Qty"],$value["Amount"],$value["TaxAmount"],$value["ExcludeTax"],$value["AverageCost"],$value["SalesPercentage"]);
    }
    $_SESSION['details'] = $arr_item;

    return include('_partial_sodetails.php');
}

if (isset($_POST['Edit'])) {
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $Qty = remove_junk($db->escape($_POST['Qty']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $ExcludeTax = remove_junk($db->escape($_POST['ExcludeTax']));
    $AverageCost = remove_junk($db->escape($_POST['AverageCost']));
    $SalesPercentage = remove_junk($db->escape($_POST['SalesPercentage']));

    $arr_item = $_SESSION['details'];



    $product = find_by_sp("call spSelectProductFromCode('{$ProductCode}');");

    $ToatlTax = 0;

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

    $ItemAmount = $Qty * $SalePrice;
    $TaxAmount = (($ItemAmount * $ToatlTax)/100);
    $ToatlAmount = $TaxAmount + $ItemAmount;

    //Change Qty
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,3,$Qty);
    //Change sale price
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,2,$SalePrice);
    //Change Amount
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,4,$ToatlAmount);
    //Change Tax Amount
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,5,$TaxAmount);
    //Change Exclude Tax
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,6,$ExcludeTax == 'true' ? 1:0);
    //Change Average Cost
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,7,$AverageCost);
    //Change Sales Percentage
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,8,$SalesPercentage);

    $_SESSION['details'] = $arr_item;


    return include('_partial_sodetails.php');
}

if (isset($_POST['Edit2'])) {
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $Qty = remove_junk($db->escape($_POST['Qty']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $ExcludeTax = remove_junk($db->escape($_POST['ExcludeTax']));
    $AverageCost = remove_junk($db->escape($_POST['AverageCost']));
    $SalesPercentage = remove_junk($db->escape($_POST['SalesPercentage']));
    $Tax = remove_junk($db->escape($_POST['Tax']));

    $arr_item = $_SESSION['details'];



    //$product = find_by_sp("call spSelectProductFromCode('{$ProductCode}');");

    $ToatlTax = 0;

    //if($Tax != 0){
    //    if(!filter_var($ExcludeTax,FILTER_VALIDATE_BOOLEAN)){
    //        if(filter_var($product["Tax"],FILTER_VALIDATE_BOOLEAN))
    //        {
    //            $ProductTax = find_by_sql("call spSelectProductTaxFromProductCode('{$ProductCode}');");
    //            foreach($ProductTax as &$pTax)
    //            {

    //                $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$pTax["TaxCode"]}');");
    //                foreach($TaxRatesM as &$TaxRt)
    //                {
    //                    $ToatlTax += $TaxRt["TaxRate"];
    //                }
    //            }
    //        }
    //    }

    //    $ItemAmount = $Qty * $SalePrice;
    //    $TaxAmount = (($ItemAmount * $ToatlTax)/100);
    //    $ToatlAmount = $TaxAmount + $ItemAmount;
    //}
    //else{
    //    $ItemAmount = $Qty * $SalePrice;
    //    $TaxAmount = $Tax;
    //    $ToatlAmount = $TaxAmount + $ItemAmount;
    //}

    $ItemAmount = $Qty * $SalePrice;
    $TaxAmount = $Tax;
    $ToatlAmount = $TaxAmount + $ItemAmount;

    //Change Qty
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,3,$Qty);
    //Change sale price
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,2,$SalePrice);
    //Change Amount
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,4,$ToatlAmount);
    //Change Tax Amount
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,5,$TaxAmount);
    //Change Exclude Tax
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,6,$ExcludeTax == 'true' ? 1:0);
    //Change Average Cost
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,7,$AverageCost);
    //Change Sales Percentage
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,8,$SalesPercentage);

    $_SESSION['details'] = $arr_item;


    return include('_partial_sodetails.php');
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Update Quotation & Approve
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Quotation</li>
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
    <form method="post" action="edit_salesorder_.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" id="btnApprove" name="edit_salesorder_" class="btn btn-primary" value="save">&nbsp;Approve&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'approval_task.php?TransactionCode=004'">Back  </button>
                        </div>
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
                            <label>Salesman</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesmanCode" id="SalesmanCode" required="required" onchange="FillCustomer();" disabled>
                                <option value="">Select Salesman</option><?php  foreach ($all_salesrep as $srep): ?>
                                <option value="<?php echo $srep['EpfNumber'] ?>" <?php if($srep['EpfNumber'] === $SalesOrderH['SalesmanCode']): echo "selected"; endif; ?>><?php echo $srep['EmployeeName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>



                        <div class="form-group">
                            <label>Approvals Flow</label>
                            <select class="form-control select2" style="width: 100%;" name="WorkFlowCode" id="WorkFlowCode" required="required" disabled>
                                <option value="">Select Approvals Work-Flow</option><?php  foreach ($all_workflows as $wflow): ?>
                                <option value="<?php echo $wflow['WorkFlowCode'] ?>" <?php if($wflow['WorkFlowCode'] === $SalesOrderH['WorkFlowCode']): echo "selected"; endif; ?>><?php echo $wflow['Description'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" >
                            <label>Remarks</label>
                            <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.." disabled></textarea>
                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="form-group">
                            <label>Customer</label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required" onchange="FillSO();" disabled>
                                <option value="">Select Customer</option><?php  foreach ($Customer as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>"<?php if($cus['CustomerCode'] === $SalesOrderH['CustomerCode']): echo "selected"; endif; ?>><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Valid Period</label>
                            <input type="text" class="form-control pull-right integer" autocomplete="off" name="ValidThru" id="ValidThru" placeholder="Days" value="<?php  echo $SalesOrderH['ValidThru']; ?>"   required="required" />
                        </div>

                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Quotation</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesOrderCode" id="SalesOrderCode" required="required" onchange="FillSODetails();" disabled>
                                <option value="<?php echo $SalesOrderH['SoNo']; ?>" selected><?php echo $SalesOrderH['SoNo']; ?></option>
                                <!--<option value="">Select Quotation</option>-->
                            </select>
                        </div>



                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="SoDate" id="SoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo $SalesOrderH['SoDate']; ?>" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <!-- /.box-header -->
        <form method="post" action="edit_salesorder_.php">
            <input type="hidden" value="edit_salesorder_" name="edit_salesorder_" />

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product Code</label>
                            <input type="text" class="form-control" name="ProductCode" id="ProductCode" placeholder="Product Code" required="required" autocomplete="off" disabled />
                        </div>

                        <div class="form-group">
                            <label>Average Cost</label>
                            <input type="text" class="form-control" name="AverageCost" id="AverageCost" placeholder="Average Cost" readonly="readonly" disabled="disabled" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product Description</label>
                            <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hProductDesc" id="hProductDesc" />
                        </div>

                         <div class="form-group">
                            <label>Stock In Hand</label>
                            <input type="text" class="form-control" name="SIH" id="SIH" placeholder="Stock In Hand" readonly="readonly" disabled="disabled" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sale Price</label>
                            <input type="text" class="form-control decimal" name="SalePrice" id="SalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Sale Price" required="required" disabled />
                        </div>

                        <div class="form-group">
                            <label>Sales Percentage (%)</label>
                            <input type="number" class="form-control integer" name="SalesPercentage" id="SalesPercentage" placeholder="Sales Percentage (%)" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control integer" name="pQty" id="Qty" placeholder="Qty" required="required" disabled />
                        </div>

                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-info" id="item" onclick="AddItem(this, event);" value="item" disabled>&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success" disabled>&nbsp;Reset&nbsp;</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
  </div>




    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Quotation Item(s)</h3>

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
                        <button id="btnUpdateChanges" class="btn btn-primary">Update Changes </button>
                        <?php include('_partial_sodetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">
    function AddItem(ctrl, event) {
        //event.preventDefault();
        var ProductCode = $('#ProductCode').val();
        var ProductDesc = $('#ProductDesc').val();
        var CostPrice = $('#CostPrice').val()
        var SalePrice = $('#SalePrice').val();
        var AverageCost = $('#AverageCost').val() == null || $('#AverageCost').val() == "" ? 0 : $('#AverageCost').val();
        var SalesPercentage = $('#SalesPercentage').val() == null || $('#SalesPercentage').val() == "" ? 0 : $('#SalesPercentage').val();
        var Qty = $('#Qty').val();


        if ($('#ProductCode').val() == "") {
            $("#ProductCode").focus();
            bootbox.alert('Please select correct product.');
        }
        else if ($('#SalePrice').val() == "" || $('#SalePrice').val() <= 0) {
            $("#SalePrice").focus();
            bootbox.alert('Please enter valid sale price.');
        }
        else if ($('#Qty').val() <= 0) {
            bootbox.alert('Please enter valid item qty.');
            $("#Qty").focus();
        }
        else {
            $('.loader').show();

            $.ajax({
                url: 'edit_salesorder.php',
                type: "POST",
                data: { Add: 'Add', ProductCode: ProductCode, ProductDesc: ProductDesc, SalePrice: SalePrice, Qty: Qty, AverageCost: AverageCost, SalesPercentage: SalesPercentage },
                success: function (result) {
                    $("#table").html(result);
                    $('#message').load('_partial_message.php');
                },
                complete: function (result)
                {
                    $('#ProductCode').val('');
                    $('#ProductDesc').val('');
                    $('#SalePrice').val('');
                    $('#Qty').val('');

                    $('#AverageCost').val('');
                    $('#SIH').val('');
                    $('#SalesPercentage').val('');

                    $('.loader').fadeOut();
                    $('#StockCode').focus();
                }
            });
        }
    }

    $(document).ready(function () {
        $('#ProductCode').typeahead({
            hint: true,
            highlight: true,
            minLength: 3,
            source: function (request, response) {
                $('.loader').show();

                $.ajax({
                    url: "autocomplete.php",
                    data: { productcode: request},
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
                        items = [];
                        map = {};
                        $.each(data, function (i, item) {
                            var id = item.value;
                            var name = item.text;
                            var cprice = parseFloat(item.cprice).toFixed(2);
                            var sprice = parseFloat(item.sprice).toFixed(2);
                            var avgcost = parseFloat(item.avgcost).toFixed(2);
                            var sih = parseFloat(item.sih);
                            map[name] = { id: id, name: name, cprice: cprice, sprice: sprice, avgcost: avgcost, sih: sih };
                            items.push(name);
                        });
                        response(items);
                        $(".dropdown-menu").css("height", "auto");
                        $('.loader').fadeOut();
                    }
                });
            },
            updater: function (item) {
                $('#ProductDesc').val(map[item].name.substring(map[item].name.indexOf('|') + 2));
                $('#hProductDesc').val(map[item].name.substring(map[item].name.indexOf('|') + 2));
                $('#CostPrice').val(map[item].cprice);
                $('#SalePrice').val(map[item].sprice);
                $('#AverageCost').val(map[item].avgcost);
                $('#SIH').val(map[item].sih);

                $('#SalePrice').focus();
                return map[item].id;
            }
        });
    });


    function FillCustomer() {
        $('.loader').show();

        $('#SalesOrderCode').val('').trigger('change');

        var SalesmanCode = $('#SalesmanCode').val();
        $.ajax({
            url: "edit_salesorder.php",
            type: "POST",
            data: { SalesmanCodeSelection: SalesmanCode },
            success: function (result) {
                $("#CustomerCode").html(""); // clear before appending new list
                $("#CustomerCode").html(result);
                $('.loader').fadeOut();
            }
        });
    }


    $('#SalesPercentage').bind('input', function () {

        var AverageCost = $('#AverageCost').val();
        var SalesPercentage = $(this).val();

        if ($(this).val() < 0) {
            bootbox.alert('Sales percentage cannot be negative.');
            $(this).val('');
        }
        else if (AverageCost == "" || AverageCost == null || AverageCost == 0) {
            bootbox.alert('Invalid average cost price.');
            $(this).val('');
        }
        else {
            var value = (parseFloat((AverageCost * SalesPercentage) / 100) + parseFloat(AverageCost)).toFixed(2);
            $('#SalePrice').val(value);
        }
    });


  function FillSO() {
      $('.loader').show();
      var Customer = $('#CustomerCode').val();
      $.ajax({
          url: "edit_salesorder.php",
          type: "POST",
          data: { Customer: Customer },
          success: function (result) {
              $("#SalesOrderCode").html(""); // clear before appending new list
              $("#SalesOrderCode").html(result);
          }
      });


    $.ajax({
        url: "edit_salesorder.php",
        type: "POST",
        data: { CustomerChanged: 'OK'},
        success: function (result) {
            $("#table").html(result);
            $('#message').load('_partial_message.php');
            $('.loader').fadeOut();
        }
    });
  }


  function FillSODetails() {
      $('.loader').show();

      var SalesOrderCode = $('#SalesOrderCode').val();

      if (SalesOrderCode == "")
      {
          $('#WorkFlowCode').val('').trigger('change');
          //$('#SalesmanCode').val('').trigger('change');
          $('#SoDate').val('');
          $('#Remarks').val('');
          $('#ValidThru').val('');
      }

    //Fill header details
      $.ajax({
          url: "autocomplete.php",
          type: "POST",
          data: { SalesOrderCode: SalesOrderCode },
          dataType: 'json',
          success: function (data) {
              //Fill header details
              jQuery(data).each(function (i, item) {
                //$('#SalesmanCode').val(item.SalesmanCode).trigger('change');
                $('#WorkFlowCode').val(item.WorkFlowCode).trigger('change');
                $('#SoDate').val(item.SoDate);
                $('#Remarks').val(item.Remarks);
                $('#ValidThru').val(item.ValidThru);
            });

          }
      });


      $.ajax({
          url: "edit_salesorder.php",
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
          url: "edit_salesorder.php", // Name of the php files
          data: { SalesOrderCode: SalesOrderCode },
          success: function (result) {
              $("#table").html(result);
              $('.loader').fadeOut();
          }
      });
  }

  $('#ProductCode').keypress(function (e) {
      if (e.which == 13) {
          $('#SalePrice').focus();
      }
  });

  $('#SalePrice').keypress(function (e) {
      if (e.which == 13)
      {
          $('#Qty').focus();
      }
  });

  $('#Qty').keypress(function (e) {
      if (e.which == 13) {
          var elem = document.getElementById("item");
          var evnt = elem["onclick"];
          evnt.call(elem);
      }
  });
</script>

<?php include_once('layouts/footer.php'); ?>

