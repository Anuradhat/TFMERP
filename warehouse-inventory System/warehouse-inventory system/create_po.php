<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Purchase Order';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'PO Create');

$all_Supplier = find_by_sql("call spSelectAllSuppliers();");
$all_workflows = find_by_sql("call spSelectAllWorkFlow();");

$default_flow = ReadSystemConfig('DefaultPOWorkFlow');
$all_Taxs = find_by_sql("call spSelectAllTaxRates();");

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$arr_item = array();
$arr_potax = array();

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php

if(isset($_POST["Add"]) && isset($_POST["ProductCode"]))
{
    $req_fields = array('ProductCode','hProductDesc','CostPrice','pQty');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_ProductCode  = remove_junk($db->escape($_POST['ProductCode']));
        $p_ProductDesc  = remove_junk($db->escape($_POST['hProductDesc']));
        $p_CostPrice  = remove_junk($db->escape($_POST['CostPrice']));
        $p_Qty = remove_junk($db->escape($_POST['pQty']));
        $p_Tax  =    remove_junk($db->escape($_POST['Taxs']));

        $prod_count = find_by_sp("call spSelectProductFromCode('{$p_ProductCode}');");


        //------------------  Tax calculation ----------------------------------------------
        $ToatlTax = 0;


        $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$p_Tax}');");
        foreach($TaxRatesM as &$TaxRt)
        {
            $ToatlTax += $TaxRt["TaxRate"];
        }


        $Amount = $p_CostPrice * $p_Qty;
        $TaxAmount = (($Amount * $ToatlTax)/100);
        //-----------------------------------------------------------------------------------


        if(!$prod_count)
        {

            $flashMessages->warning('This product code not exist in the system.');
            return include('_partial_podetails.php');
        }


        if ($_SESSION['details'] == null)
        {

            $arr_item[]  = array($p_ProductCode,$p_ProductDesc,$p_CostPrice,$p_Qty,$ToatlTax,$TaxAmount,$p_Tax);
            $_SESSION['details'] = $arr_item;
            return include('_partial_podetails.php');
        }
        else
        {
            $arr_item= $_SESSION['details'];

            if(!ExistInArray($arr_item,$p_ProductCode))
            {
                $arr_item[] = array($p_ProductCode,$p_ProductDesc,$p_CostPrice,$p_Qty,$ToatlTax,$TaxAmount,$p_Tax);
                $_SESSION['details'] = $arr_item;
                return include('_partial_podetails.php');
            }
            else
            {
                $flashMessages->warning('This product exist in the list.');
                return include('_partial_podetails.php');
            }

        }

    }
}


if(isset($_POST['create_po'])){

    if($_POST['create_po'] == "save")
    {
        $req_fields = array('SupplierCode','WorkFlowCode');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_SupplierCode  = remove_junk($db->escape($_POST['SupplierCode']));
            $p_PurchaseRequisition  = remove_junk($db->escape($_POST['PRNo']));
            $p_WorkFlowCode  = remove_junk($db->escape($_POST['WorkFlowCode']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));

            $date    = make_date();
            $user =  current_user();

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {
                //save purchase order

                try
                {
                    $p_POCode  = autoGenerateNumber('tfmPoHT',1);



                    $Po_count = find_by_sp("call spSelectPurchaseOrderFromCode('{$p_POCode}');");

                    if($Po_count)
                    {
                        $flashMessages->warning('This purchase order number exist in the system.','create_po.php');

                    }

                    $db->begin();

                    //Insert purchase order header details
                    $query  = "call spInsertPurchaseOrderH('{$p_POCode}','{$p_PurchaseRequisition}','{$p_SupplierCode}','{$date}','{$p_WorkFlowCode}','{$p_Remarks}','{$date}','{$user["username"]}');";
                    $db->query($query);

                     $TotalAmount = 0;
                    //Insert purchase order item details
                    foreach($arr_item as $row => $value)
                    {
                        $amount = $value[2] * $value[3] + $value[5];
                        $TotalAmount += $amount;

                        $query  = "call spInsertPurchaseOrderD('{$p_POCode}','{$value[0]}','{$value[1]}',{$value[2]},{$value[3]},{$value[4]},{$value[5]},'{$value[6]}',{$amount});";
                        $db->query($query);
                    }

                    InsertRecentActvity("Purchase order","Reference No. ".$p_POCode);

                    $db->commit();


                    //Send Mail
                    $WorkFlowDetForMail = find_by_sp("call spSelectWorkFlowLevel1DetailsForMail('{$p_WorkFlowCode}');");
                    $Supplier = find_by_sp("call spSelectSupplierByCode('{$p_SupplierCode}');");

                    $Subject = 'You have to Approve Purchase Order';

                    $htmlContent = '
                    <html>
                    <head></head>
                    <body>
                        <p>Hi '.$WorkFlowDetForMail['EmployeeName'].',<p>
                        <h1>'.$Subject.'</h1>
                        <table cellspacing="0" style="border: 2px dashed #008000; width: 400px; height: 300px;">
                            <tr>
                                <th align="left">Purchase Order No: </th><td>'.$p_POCode.'</td>
                            </tr>
                            <tr style="background-color: #e0e0e0;">
                                <th align="left">Purchase Order Date: </th><td>'.$date.'</td>
                            </tr>
                            <tr >
                                <th align="left">Supplier: </th><td>'.$Supplier['SupplierName'].'</td>
                            </tr>
                            <tr style="background-color: #e0e0e0;">
                                <th align="left">Purchase Order Amount:</th><td>'.number_format($TotalAmount,2).'</td>
                            </tr>
                            <tr>
                                <th align="left">Log In</th><td><a href="http://erp.tfm.lk/">TFM ERP System</a></td>
                            </tr>
                        </table>
                         <br><br>
                          <i>This is a system generated email – please do not reply. </i>
                    </body>
                    </html>';

                    SendMailForApprovals($WorkFlowDetForMail['Email'],$Subject,$htmlContent);

                    $flashMessages->success('Purchase order has been saved successfully,\n   Your Purchase order No: '.$p_POCode,'create_po.php');
                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to create purchase order. '.$ex->getMessage(),'create_po.php');
                }

            }
            else
            {

                $flashMessages->warning('Purchase order item(s) not found!','create_po.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'create_po.php');
        }

    }
}

if (isset($_POST['_prodcode'])) {
    $prodcode = remove_junk($db->escape($_POST['_prodcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$prodcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_podetails.php');
}

if (isset($_POST['Edit'])) {
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $Qty = remove_junk($db->escape($_POST['Qty']));
    $CostPrice = remove_junk($db->escape($_POST['CostPrice']));
    $p_Tax  =  remove_junk($db->escape($_POST['Taxs']));

    $arr_item = $_SESSION['details'];

    //------------------  Tax calculation ----------------------------------------------
    $ToatlTax = 0;


    $TaxRatesM = find_by_sql("call spSelectTaxRatesFromCode('{$p_Tax}');");
    foreach($TaxRatesM as &$TaxRt)
    {
        $ToatlTax += $TaxRt["TaxRate"];
    }


    $Amount = $CostPrice * $Qty;
    $TaxAmount = (($Amount * $ToatlTax)/100);
    //-----------------------------------------------------------------------------------



    //Change Qty
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,3,$Qty);
    //Change Cost price
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,2,$CostPrice);
    //Change total tax rate
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,4,$ToatlTax);
    //Change total tax amount
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,5,$TaxAmount);
    //Change tax code
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,6,$p_Tax);

    $_SESSION['details'] = $arr_item;

    return include('_partial_podetails.php');
}


if (isset($_POST['_PRNo'])) {
    $_SESSION['details'] = null;

    $PRNo = remove_junk($db->escape($_POST['_PRNo']));
    //$all_PRHeader = find_by_sql("call spSelectAllPRHeaderDetailsFromPRNo('{$PRNo}');");


    $all_PRDetsils = find_by_sql("call spSelectAllPRDetailsFromPRNo('{$PRNo}');");
    if($_SESSION['details'] == null) $arr_item = $_SESSION['details']; else $arr_item[] = $_SESSION['details'];
    foreach($all_PRDetsils as $row => $value){
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["LastPurchasePrice"],$value["Qty"],$value["TotalTaxRate"],$value["TaxAmount"],$value["TaxCode"]);
        $_SESSION['details'] = $arr_item;
    }
    return include('_partial_podetails.php');
}



if (isset($_POST['_RowNo'])) {
    $ProductCode = remove_junk($db->escape($_POST['_RowNo']));
    $serchitem = ArraySearch($arr_item,$ProductCode);

    return include('_partial_poitem.php');
}


if (isset($_POST['Supplier'])) {

    $SupplierCode = remove_junk($db->escape($_POST['Supplier']));
    $Remarks = remove_junk($db->escape($_POST['Remarks']));

    $all_PRN = find_by_sql("call spSelectAllPurchaseRequisitionFromSupplierCode('{$SupplierCode}');");

    echo "<option>Select Purchase Requisition</option>";
    foreach($all_PRN as &$value){
        $arr_PRNNo[]  = array('PRNo' =>$value["PRNo"]);
        echo "<option value ={$value["PRNo"]}>{$value["PRNo"]}</option>";
    }
    return;
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
       Create Purchase Order
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Purchase Order</li>
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
    <form method="post" action="create_po.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="create_po" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
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
                            <div class="form-group">
                                <label>Purchase Oder No</label>
                                <input type="text" class="form-control" name="PoNo" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                            </div>
                        </div>


                        <div class="form-group">
                            <label>Purchase Requisition</label>
                            <select class="form-control select2" style="width: 100%;" name="PRNo" id="PRNo" onchange="FillDetails();">
                                <option value="" selected disabled>Select Purchase Requisition</option>
                            </select>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2" style="width: 100%;" name="SupplierCode" id="SupplierCode" required="required" onchange="FillPRN();">
                                <option value="">Select Supplier</option><?php  foreach ($all_Supplier as $supp): ?>
                                <option value="<?php echo $supp['SupplierCode'] ?>"><?php echo $supp['SupplierName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Approvals Flow</label>
                            <select class="form-control select2" style="width: 100%;" name="WorkFlowCode" id="WorkFlowCode" required="required">
                                <option value="">Select Approvals Work-Flow</option><?php  foreach ($all_workflows as $wflow): ?>
                                <option value="<?php echo $wflow['WorkFlowCode'] ?>" <?php if($wflow['WorkFlowCode'] === $default_flow): echo "selected"; endif; ?>><?php echo $wflow['Description'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="PoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.."></textarea>
                            </div>
                        </div>



                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <!-- /.box-header -->
        <form method="post" action="create_po.php">
            <input type="hidden" value="Add"name="Add" />

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product Code</label>
                            <input type="text" class="form-control" id="ProductCode" name="ProductCode" placeholder="Product Code" required="required" autocomplete="off" />
                        </div>

                        <div class="form-group">
                            <label>Item Tax(s)</label>
                            <select class="form-control select2" name="Taxs" style="width: 100%;" id="Taxs">
                                <option value="">Select Tax</option><?php  foreach ($all_Taxs as $tax): ?>
                                <option value="<?php echo $tax['TaxCode'] ?>"><?php echo $tax['TaxDesc'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
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
                            <input type="text" class="form-control decimal" name="CostPrice" id="CostPrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Cost Price" required="required" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control integer" name="pQty" id="Qty" placeholder="Qty" required="required" />
                        </div>

                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-info" name="create_po" onclick="AddItem(this, event);" value="item">&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
  </div>




    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Purchase Order Item(s)</h3>

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
                        <?php include('_partial_podetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">
    function AddItem(ctrl, event) {
        event.preventDefault();
        $('.loader').show();

        if ($('#ProductCode').val() == "") {
            $("#ProductCode").focus();
            $('.loader').fadeOut();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#ProductDesc').val() == "") {
            $("#ProductCode").focus();
            $('.loader').fadeOut();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#CostPrice').val() <= 0) {
            $("#CostPrice").focus();
            $('.loader').fadeOut();
            bootbox.alert('Please enter valid cost price.');
        }
        else if ($('#Qty').val() <= 0) {
            $("#Qty").focus();
            $('.loader').fadeOut();
            bootbox.alert('Please enter valid purchase qty.');
        }
        else {
            $.ajax({
                url: 'create_po.php',
                type: "POST",
                data: $("form").serialize(),
                success: function (result) {
                    $("#table").html(result);
                    $('#message').load('_partial_message.php');
                },
                complete: function (result) {
                    $('#ProductCode').val('');
                    $('#Taxs').val('').trigger('change');
                    $('#ProductDesc').val('');
                    $('#CostPrice').val('');
                    $('#Qty').val('');

                    $('.loader').fadeOut();
                    $('#ProductCode').focus();

                    $('.loader').fadeOut();
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
                $.ajax({
                    url: "autocomplete.php",
                    data: 'productcode=' + request,
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
                        items = [];
                        map = {};
                        $.each(data, function (i, item) {
                            var id = item.value;
                            var name = item.text;
                            var cprice = item.cprice;
                            map[name] = { id: id, name: name,cprice: cprice };
                            items.push(name);
                        });
                        response(items);
                        $(".dropdown-menu").css("height", "auto");
                    }
                });
            },
            updater: function (item) {
                $('#ProductDesc').val(map[item].name.substring(map[item].name.indexOf('|') + 2));
                $('#hProductDesc').val(map[item].name.substring(map[item].name.indexOf('|') + 2));
                $('#CostPrice').val(map[item].cprice);

                return map[item].id;
            }
        });
    });

    function FillPRN() {
        var Supplier = $('#SupplierCode').val();
        var Remarks = $('#Remarks').val();

        $('.loader').show();

        $.ajax({
            url: "create_po.php",
            type: "POST",
            data: { Supplier: Supplier, Remarks: Remarks},
            success: function (result) {
                $("#PRNo").html(""); // clear before appending new list
                $("#PRNo").html(result);
            },
            complete: function (result)
            {
                $('.loader').fadeOut();
            }
        });

    }

    function FillDetails() {
        var PrnNo = $('#PRNo').val();
        $('.loader').show();

        $.ajax({
            type: "POST",
            url: "create_po.php", // Name of the php files
            data: { "_PRNo": PrnNo },
            success: function (result) {
                $("#table").html(result);
            },
            complete: function (result) {
                $('.loader').fadeOut();
            }
        });

    }
</script>

<?php include_once('layouts/footer.php'); ?>

