<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Purchase Order';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

$all_Supplier = find_by_sql("call spSelectAllSuppliers();");

$arr_item = array();
$arr_header = array();
$arr_PRNNo = array();

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
if($_SESSION['header'] != null) $arr_header = $_SESSION['header'];
if($_SESSION['PrnNos'] != null) $arr_PRNNo = $_SESSION['PrnNos'];
?>

<?php

if(isset($_POST['create_po'])){
    if($_POST['create_po'] == "item")
    {
        $req_fields = array('ProductCode','hProductDesc','CostPrice','Qty');   

        validate_fields($req_fields);

        if(empty($errors)){
            $p_ProductCode  = remove_junk($db->escape($_POST['ProductCode']));
            $p_ProductDesc  = remove_junk($db->escape($_POST['hProductDesc']));
            $p_CostPrice  = remove_junk($db->escape($_POST['CostPrice']));
            $p_Qty = remove_junk($db->escape($_POST['Qty']));

            $prod_count = find_by_sp("call spSelectProductFromCode('{$p_ProductCode}');");


            if(!$prod_count)
            {
                $session->msg("d", "This product code not exist in the system.");
                redirect('create_po.php',false);
            }


            if ($_SESSION['details'] == null)
            {
                $arr_item[]  = array($p_ProductCode,$p_ProductDesc,$p_CostPrice,$p_Qty);
                $_SESSION['details'] = $arr_item; 
            }
            else
            {
                $arr_item= $_SESSION['details'];

                if(!ExistInArray($arr_item,$p_ProductCode))
                {
                    $arr_item[] = array($p_ProductCode,$p_ProductDesc,$p_CostPrice,$p_Qty);
                    $_SESSION['details'] = $arr_item;
                }
                else
                {
                    $session->msg("w", "This product exist in the table.");
                    redirect('create_po.php',false);
                }

            }

        }
        else
        {
            $session->msg("d", $errors);
            redirect('create_po.php',false);
        }
    }
    else if($_POST['create_po'] == "save")
    {
        $req_fields = array('SupplierCode');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_SupplierCode  = remove_junk($db->escape($_POST['SupplierCode']));
            $p_PurchaseRequisition  = remove_junk($db->escape($_POST['PRNo']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));
            $date    = make_date();
            $user = "anush";

            //Get all sessions values
            //$arr_header = array("Supplier"=>$p_SupplierCode, "Remarks"=> $p_Remarks);
            $arr_item= $_SESSION['details'];

            //$_SESSION['header'] = $arr_header;

            //check details values
            if(count($arr_item)>0)
            {
                //save purchase order 
                
                try
                {
                    $p_POCode  = autoGenerateNumber('tfmPoHT',1);

                    $db->begin();

                    $Po_count = find_by_sp("call spSelectPurchaseOrderFromCode('{$p_POCode}');");

                    if($Po_count)
                    {
                        $session->msg("d", "This purchase order number exist in the system.");
                        redirect('create_po.php',false);
                    }

                    //Insert purchase order header details
                    $query  = "call spInsertPurchaseOrderH('{$p_POCode}','{$p_PurchaseRequisition}','{$p_SupplierCode}','{$date}','{$p_Remarks}','{$date}','{$user}');";
                    $db->query($query);

                    //Insert purchase order item details
                    foreach($arr_item as $row => $value)
                    {
                        $amount = $value[2] * $value[3];
                        $query  = "call spInsertPurchaseOrderD('{$p_POCode}','{$value[0]}','{$value[1]}',{$value[2]},{$value[3]},{$amount});";
                        $db->query($query);
                    }

                    $db->commit();
                    
                    unset($_SESSION['header']);
                    unset($_SESSION['details']);
                    unset($_SESSION['PrnNos']);

                    $session->msg('s',"Purchase order has been saved successfully,\n   Your Purchase order No: ".$p_POCode);
                    redirect('create_po.php', false);

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $session->msg('d',' Sorry failed to added!');
                    redirect('create_po.php', false);
                }

            }
            else
            {
                $session->msg("w",' Purchase order item(s) not found!');
                redirect('create_po.php',false);
            }
        }
        else
        {
            $session->msg("d", $errors);
            redirect('create_po.php',false);
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

    $arr_item = $_SESSION['details'];
    //Change Qty
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,3,$Qty);
    $_SESSION['details'] = $arr_item;

    return include('_partial_podetails.php');  
}


if (isset($_POST['_PRNo'])) {
    $_SESSION['header']  = null; 
    $_SESSION['details'] = null;

    $PRNo = remove_junk($db->escape($_POST['_PRNo']));
    $arr_header = $_SESSION['header'];
    $all_PRHeader = find_by_sql("call spSelectAllPRHeaderDetailsFromPRNo('{$PRNo}');");
    $arr_header = array('PRNo' => $all_PRHeader[0]["PRNo"],'PrDate' => $all_PRHeader[0]["PrDate"],'SupplierCode' => $all_PRHeader[0]["SupplierCode"],'Remarks' => $all_PRHeader[0]["Remarks"],'ProcessedFlg' => $all_PRHeader[0]["ProcessedFlg"]);
    $_SESSION['header'] = $arr_header; 

    $all_PRDetsils = find_by_sql("call spSelectAllPRDetailsFromPRNo('{$PRNo}');");
    if($_SESSION['details'] == null) $arr_item = $_SESSION['details']; else $arr_item[] = $_SESSION['details'];
    foreach($all_PRDetsils as $row => $value){
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["LastPurchasePrice"],$value["Qty"]);
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
    $_SESSION['header']  = null; 
    //$_SESSION['details'] = null;
    $_SESSION['PrnNos']  = null;
    
    $SupplierCode = remove_junk($db->escape($_POST['Supplier']));
    $Remarks = remove_junk($db->escape($_POST['Remarks']));

    //$arr_header = $_SESSION['header'];
    $all_PRN = find_by_sql("call spSelectAllPurchaseRequisitionFromSupplierCode('{$SupplierCode}');");
    $arr_header = array('PRNo' => '0','SupplierCode' => $SupplierCode,'Remarks' => $Remarks);
    $_SESSION['header'] = $arr_header; 

    echo "<option>Select Purchase Requisition</option>";
    foreach($all_PRN as &$value){
        $arr_PRNNo[]  = array('PRNo' =>$value["PRNo"]);
        echo "<option value ={$value["PRNo"]}>{$value["PRNo"]}</option>";
    }
    $_SESSION['PrnNos'] = $arr_PRNNo;

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
            <div class="col-md-12"><?php echo display_msg($msg); ?>
            </div>
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
                                <option value="">Select Purchase Requisition</option><?php  foreach ($arr_PRNNo as $PRN): ?>
                                <option value="<?php echo $PRN['PRNo'] ?>"  <?php if($PRN['PRNo'] === $arr_header['PRNo']): echo "selected"; endif; ?>><?php echo $PRN['PRNo'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                       
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2" style="width: 100%;" name="SupplierCode" id="SupplierCode" required="required" onchange="FillPRN();">
                                <option value="">Select Supplier</option><?php  foreach ($all_Supplier as $supp): ?>
                                <option value="<?php echo $supp['SupplierCode'] ?>" <?php if($supp['SupplierCode'] === $arr_header['SupplierCode']): echo "selected"; endif; ?>><?php echo $supp['SupplierName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.."><?php echo remove_junk($arr_header['Remarks']) ?></textarea>
                            </div>
                        </div>

                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="PoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
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
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product Code</label>
                            <input type="text" class="form-control" id="ProductCode" name="ProductCode" placeholder="Product Code" required="required" autocomplete="off" />
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
                            <input type="text" class="form-control" name="CostPrice" id="CostPrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Cost Price" required="required" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control integer" name="Qty" placeholder="Qty" required="required" />
                        </div>

                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-info" name="create_po" value="item">&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
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

    //$(document).ready(function () {
    //    $('#PRNo').typeahead({
    //        hint: true,
    //        highlight: true,
    //        minLength: 1,
    //        source: function (request, response) {
    //            $.ajax({
    //                url: "autocomplete.php",
    //                data: 'PRNoForPO=' + request,
    //                dataType: "json",
    //                type: "POST",
    //                success: function (data) {
    //                    items = [];
    //                    map = {};
    //                    $.each(data, function (i, item) {
    //                        var id = item.value;
    //                        var name = item.text;
    //                        var Processed = item.ProcessedFlg;
    //                        var PrDate = item.PrDate;
    //                        var SupplierCode = item.SupplierCode;
    //                        var Remarks = item.Remarks;

    //                        map[name] = {
    //                            id: id, name: name, Processed: Processed, PrDate: PrDate,
    //                            SupplierCode: SupplierCode, Remarks: Remarks
    //                        };
    //                        items.push(name);
    //                    });
    //                    response(items);
    //                    $(".dropdown-menu").css("height", "auto");
    //                }
    //            });
    //        },
    //        updater: function (item) {

    //            $("#hPRNo").val(map[item].id);
    //            $("#PoDate").val(map[item].PrDate);
    //            $("#SupplierCode").select2().val(map[item].SupplierCode).trigger('change.select2');
    //            $("#Remarks").val(map[item].Remarks);

    //            $.ajax({
    //                type: "POST",
    //                url: "create_po.php", // Name of the php files
    //                data: { "_PRNo": map[item].id },
    //                success: function (result) {
    //                    $("#table").html(result);
    //                }
    //            });

    //            return map[item].id;
    //        }
    //    });
    //});


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


    //Textbox integer accept
    $(".integer").keypress(function (e) {
        if (e.which < 48 || e.which > 57) {
            return (false);  // stop processing
        }
    });


    function FillPRN() {
        var Supplier = $('#SupplierCode').val();
        var Remarks = $('#Remarks').val();

        $.ajax({
            url: "create_po.php",
            type: "POST",
            data: { Supplier: Supplier, Remarks: Remarks},
            success: function (result) {
                $("#PRNo").html(""); // clear before appending new list
                //$("#PRNo").append($('<option></option>').val(null).html("Select Purchase Requisition"));
                $("#PRNo").html(result);
                //$.each(result, function (i, item) {
                //    $("#PRNo").append(
                //        $('<option></option>').val(item.value).html(item.text));
                //});
            }
        });

    }

    function FillDetails() {
        var PrnNo = $('#PRNo').val();

        $.ajax({
            type: "POST",
            url: "create_po.php", // Name of the php files
            data: { "_PRNo": PrnNo },
            success: function (result) {
                $("#table").html(result);
            }
        });

    }
</script>

<?php include_once('layouts/footer.php'); ?>

