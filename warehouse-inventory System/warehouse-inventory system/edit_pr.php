<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Update Purchase Requisition';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'PR Update');

$all_Supplier = find_by_sql("call spSelectAllSuppliers();");


if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$arr_item = array();
$arr_header = array();


if($_SESSION['header'] != null) $arr_header = $_SESSION['header'];
if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php

if(isset($_POST["ProductCode"]))
{
    $req_fields = array('ProductCode','hProductDesc','LastPurchasePrice','Qty');   

    validate_fields($req_fields);

    if(empty($errors)){
        $p_ProductCode  = remove_junk($db->escape($_POST['ProductCode']));
        $p_ProductDesc  = remove_junk($db->escape($_POST['hProductDesc']));
        $p_LastPurchasePrice  = remove_junk($db->escape($_POST['LastPurchasePrice']));
        $p_Qty = remove_junk($db->escape($_POST['Qty']));

        $prod_count = find_by_sp("call spSelectProductFromCode('{$p_ProductCode}');");


        if(!$prod_count)
        {
            $flashMessages->warning('This product code not exist in the system.');

            return include('_partial_pritems.php');
        }


        if ($_SESSION['details'] == null)
        {
            $arr_item[]  = array($p_ProductCode,$p_ProductDesc,$p_LastPurchasePrice,$p_Qty);
            $_SESSION['details'] = $arr_item; 

            return include('_partial_pritems.php');
        }
        else
        {
            $arr_item= $_SESSION['details'];

            if(!ExistInArray($arr_item,$p_ProductCode))
            {
                $arr_item[] = array($p_ProductCode,$p_ProductDesc,$p_LastPurchasePrice,$p_Qty);
                $_SESSION['details'] = $arr_item;

                return include('_partial_pritems.php');
            }
            else
            {
                $flashMessages->warning('This product exist in the list.');

                return include('_partial_pritems.php');
            }

        }

    }
}


if(isset($_POST['edit_pr'])){

    if($_POST['edit_pr'] == "save")
    {
        $req_fields = array('hPRNo','SupplierCode');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_PRNo = remove_junk($db->escape($_POST['hPRNo']));
            $p_SupplierCode  = remove_junk($db->escape($_POST['SupplierCode']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));
            $date    = make_date();
            $user =  current_user();

            //Get all sessions values
            $arr_header = array("Supplier"=>$p_SupplierCode, "Remarks"=> $p_Remarks);
            $arr_item= $_SESSION['details'];

            $_SESSION['header'] = $arr_header;

            //check details values
            if(count($arr_item)>0)
            {
                //Update purchase requisition 
                
                try
                {

                    $db->begin();

                    $Pr_count = find_by_sp("call spSelectPurchaseRequisitionFromCode('{$p_PRNo}');");

                    if(!$Pr_count)
                    {
                        $flashMessages->warning('This purchase requisition code not exist in the system.','edit_pr.php');
                    }

                    //Update purchase requisition header details
                    $query  = "call spUpdatePurchaseRequisitionH('{$p_PRNo}','{$p_SupplierCode}','{$p_Remarks}','{$date}','{$user["username"]}');";
                    $db->query($query);

                    //Delete purchase requisition item details
                    $query  = "call spDeletePurchaseRequisitionDFromCode('{$p_PRNo}');";
                    $db->query($query);

                    //Update purchase requisition item details
                    foreach($arr_item as $row => $value)
                    {
                        $query  = "call spUpdatePurchaseRequisitionD('{$p_PRNo}','{$value[0]}','{$value[1]}',{$value[2]},{$value[3]});";
                        $db->query($query);
                    }

                    $db->commit();
                    
                    $flashMessages->success('Purchase requisition has been updated successfully','edit_pr.php');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to update purchase requisition. '.$ex->getMessage(),'edit_pr.php');
                }

            }
            else
            {
                $flashMessages->warning('Purchase requisition item(s) not found!','edit_pr.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'edit_pr.php');
        }

    }
}

if (isset($_POST['_prodcode'])) {
    $prodcode = remove_junk($db->escape($_POST['_prodcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray($arr_item,$prodcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_pritems.php');  
}


if (isset($_POST['_PRNo'])) {
    $_SESSION['header']  = null; 
    $_SESSION['details'] = null;

    $PRNo = remove_junk($db->escape($_POST['_PRNo']));
    $arr_header = $_SESSION['header'];
    $all_PRHeader = find_by_sql("call spSelectAllPRHeaderDetailsFromPRNo('{$PRNo}');");
    $arr_header = array($all_PRHeader[0]["PRNo"],$all_PRHeader[0]["PrDate"],$all_PRHeader[0]["SupplierCode"],$all_PRHeader[0]["Remarks"],$all_PRHeader[0]["ProcessedFlg"]);
    $_SESSION['header'] = $arr_header; 

    $all_PRDetsils = find_by_sql("call spSelectAllPRDetailsFromPRNo('{$PRNo}');");
    $arr_item = $_SESSION['details'];
    foreach($all_PRDetsils as $row => $value){
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["LastPurchasePrice"],$value["Qty"]);
        $_SESSION['details'] = $arr_item; 
    }
    return include('_partial_pritems.php'); 
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Update Purchase Requisition
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Purchase Requisition</li>
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
    <form method="post" action="edit_pr.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_pr" class="btn btn-primary" value="save" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>>&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="button" id="btnCancel" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div id="message" class="col-md-12"><?php include('_partial_message.php'); ?> </div>
        </div>

        <div class="box box-default" id="header">
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
                                <label>Purchase Requisition No</label>
                                <input type="text" class="form-control" name="PRNo" id="PRNo" autocomplete="off" placeholder="Select Purchase Requisition No" value="<?php echo remove_junk($arr_header[0]) ?>"/>
                                <input type="hidden" name="hPRNo" id="hPRNo" value="<?php echo remove_junk($arr_header[0]) ?>"/>
                                <label id="lblProcess" style="color:red;"><?php   if(remove_junk($arr_header[4]) == 1) echo "Processed" ?></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="Remarks" class="form-control" placeholder="Enter remarks here.." id="Remarks"  <?php   if(remove_junk($arr_header[4]) == 1) echo "disabled" ?>><?php echo remove_junk($arr_header[3]) ?></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="PrDate" id="PrDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo remove_junk($arr_header[1]) ?>" />
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2" style="width: 100%;" name="SupplierCode" id="SupplierCode" required="required" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>>
                                <option value="">Select Supplier</option><?php  foreach ($all_Supplier as $supp): ?>
                                <option value="<?php echo $supp['SupplierCode'] ?>" <?php if($supp['SupplierCode'] === $arr_header[2]): echo "selected"; endif; ?>><?php echo $supp['SupplierName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </form>

        <div class="box box-default" id="details">
            <!-- /.box-header -->
            <form method="post" action="edit_pr.php">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Product Code</label>
                                <input type="text" class="form-control" id="ProductCode" name="ProductCode" placeholder="Product Code" required="required" autocomplete="off" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>/>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Product Description</label>
                                <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" />
                                <input  type="hidden" name="hProductDesc" id="hProductDesc"/>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Last Purchase Price</label>
                                <input type="text" class="form-control decimal" name="LastPurchasePrice" id="LastPurchasePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Last Purchase Price" required="required" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>/>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number" class="form-control integer" name="Qty" id="Qty" placeholder="Qty"  required="required" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>/>
                            </div>
                            <div class="form-group pull-right">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-info" name="edit_pr" onclick="AddItem(this, event);" value="item" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>>&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                                <button type="reset" class="btn btn-success" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>>&nbsp;Reset&nbsp;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
     </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Purchase Requisition Item(s)</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group"><?php include('_partial_pritems.php'); ?>
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

        if ($('#ProductCode').val() == "")
        {
            $("#ProductCode").focus();
            $('#ProductCode').focus();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#ProductDesc').val() == "") {
            $("#ProductCode").focus();
            $('#ProductCode').focus();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#LastPurchasePrice').val() <= 0) {
            $("#LastPurchasePrice").focus();
            $('#ProductCode').focus();
            bootbox.alert('Please enter valid purchase price.');
        }
        else if ($('#Qty').val() <= 0) {
            $("#Qty").focus();
            $('#ProductCode').focus();
            bootbox.alert('Please enter valid purchase qty.');
        }
        else
        {
            $.ajax({
                url: 'edit_pr.php',
                type: "POST",
                data: $("form").serialize(),
                success: function (result) {
                    $("#table").html(result);
                    $('#message').load('_partial_message.php');
                },
                complete: function (result) {
                    $('#ProductCode').val('');
                    $('#ProductDesc').val('');
                    $('#LastPurchasePrice').val('');
                    $('#Qty').val('');

                    $('.loader').fadeOut();
                    $('#ProductCode').focus();
                }
            });
        }
    }
</script>


<script type="text/javascript">
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
                $('#LastPurchasePrice').val(map[item].cprice);

                return map[item].id;
            }
        });
    });



    $(document).ready(function () {
        $('#PRNo').typeahead({
            hint: true,
            highlight: true,
            minLength: 1,
            source: function (request, response) {
                $('.loader').show();

                $.ajax({
                    url: "autocomplete.php",
                    data: 'PRNo=' + request,
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
                        items = [];
                        map = {};
                        $.each(data, function (i, item) {
                            var id = item.value;
                            var name = item.text;
                            var Processed = item.ProcessedFlg;
                            var PrDate = item.PrDate;
                            var SupplierCode = item.SupplierCode;
                            var Remarks = item.Remarks;

                            map[name] = {id: id, name: name, Processed: Processed,PrDate: PrDate,
                                SupplierCode: SupplierCode, Remarks: Remarks};
                            items.push(name);
                        });
                        response(items);
                        $(".dropdown-menu").css("height", "auto");
                        $('.loader').fadeOut();
                    }
                });
            },
            updater: function (item) {

                $("#hPRNo").val(map[item].id);
                $("#PrDate").val(map[item].PrDate);
                $("#SupplierCode").select2().val(map[item].SupplierCode).trigger('change.select2');
                $("#Remarks").val(map[item].Remarks);

                //$("#SupplierCode option[value=" + map[item].SupplierCode + "]").attr("selected", "selected");
               // $('#SupplierCode').val(map[item].SupplierCode).selectmenu("refresh");
                //$('.id_100 option[value=val2]').attr('selected', 'selected');
                
                if (map[item].Processed == 1) {
                    $('#lblProcess').text("Processed");
                    $('button').attr('disabled', true);
                    $("#header :input").attr("disabled", true);
                    $("#details :input").attr("disabled", true);
                    $('#btnCancel').attr('disabled', false);
                    $('#PRNo').attr('disabled', false);
                }
                else
                {
                    $('#lblProcess').text("");
                    $('button').attr('disabled', false);
                    $("#header :input").attr("disabled", false);
                    $("#details :input").attr("disabled", false);
                }

                $.ajax({
                    type: "POST",
                    url: "edit_pr.php", // Name of the php files
                    data: { "_PRNo": map[item].id },
                    success: function (result) {
                        $("#table").html(result);
                    }
                });

                return map[item].id;
            }
        });
    });
</script>

<?php include_once('layouts/footer.php'); ?>

