<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Purchase Requisition';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

$all_Supplier = find_by_sql("call spSelectAllSuppliers();");


if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$arr_item = array();

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



if(isset($_POST['create_pr'])){
    if($_POST['create_pr'] == "save")
    {
        $req_fields = array('SupplierCode');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_SupplierCode  = remove_junk($db->escape($_POST['SupplierCode']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));
            $date    = make_date();
            $user =  current_user();

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {
                //save purchase requisition 
                
                try
                {
                    $p_PRCode  = autoGenerateNumber('tfmtPurchaseRequisitionHT',1);

                    $db->begin();

                    $Pr_count = find_by_sp("call spSelectPurchaseRequisitionFromCode('{$p_PRCode}');");

                    if($Pr_count)
                    {
                        $flashMessages->warning('This purchase requisition code exist in the system.','create_pr.php');
                    }

                    //Insert purchase requisition header details
                    $query  = "call spInsertPurchaseRequisitionH('{$p_PRCode}','{$date}','{$p_SupplierCode}','{$p_Remarks}','{$date}','{$user["username"]}');";
                    $db->query($query);

                    //Insert purchase requisition item details
                    foreach($arr_item as $row => $value)
                    {
                        $query  = "call spInsertPurchaseRequisitionD('{$p_PRCode}','{$value[0]}','{$value[1]}',{$value[2]},{$value[3]});";
                        $db->query($query);
                    }

                    $db->commit();

                    $flashMessages->success('Purchase requisition has been saved successfully,\n   Your Purchase Requisition No: '.$p_PRCode,'create_pr.php');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to create purchase requisition. '.$ex->getMessage(),'create_pr.php');

                }

            }
            else
            {
                $flashMessages->warning('Purchase requisition item(s) not found!','create_pr.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'create_pr.php');
        }

    }
}

if (isset($_POST['_prodcode'])) {
    $prodcode = remove_junk($db->escape($_POST['_prodcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$prodcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_pritems.php');  
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
       Create Purchase Requisition
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
    <form method="post" action="create_pr.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="create_pr" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
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
                                <label>Purchase Requisition No</label>
                                <input type="text" class="form-control" name="PRNo" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="Remarks" class="form-control" placeholder="Enter remarks here.." ></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="PrDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2" style="width: 100%;" name="SupplierCode" required="required">
                                <option value="">Select Supplier</option><?php  foreach ($all_Supplier as $supp): ?>
                                <option value="<?php echo $supp['SupplierCode'] ?>"><?php echo $supp['SupplierName'] ?>
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
        <form method="post" action="create_pr.php">
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
                            <label>Last Purchase Price</label>
                            <input type="text" class="form-control decimal" name="LastPurchasePrice" id="LastPurchasePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Last Purchase Price" required="required" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control integer" name="Qty" id="Qty" placeholder="Qty" required="required" />
                        </div>
                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-info" name="create_pr" onclick="AddItem(this, event);" value="item">&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;</button>
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
                    <div class="form-group">
                        <?php include('_partial_pritems.php'); ?>
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
            $('.loader').fadeOut();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#ProductDesc').val() == "") {
            $("#ProductCode").focus();
            $('.loader').fadeOut();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#LastPurchasePrice').val() <= 0) {
            $("#LastPurchasePrice").focus();
            $('.loader').fadeOut();
            bootbox.alert('Please enter valid purchase price.');
        }
        else if ($('#Qty').val() <= 0) {
            $("#Qty").focus();
            $('.loader').fadeOut();
            bootbox.alert('Please enter valid purchase qty.');
        }
        else
        {
            $.ajax({
                url: 'create_pr.php',
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
   
</script>

<?php include_once('layouts/footer.php'); ?>

