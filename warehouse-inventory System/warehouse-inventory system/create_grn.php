<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Goods Received Note';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

$all_Supplier = find_by_sql("call spSelectAllSuppliers();");
$all_locations = find_by_sql("call spSelectAllLocations();");

//$default_flow = ReadSystemConfig('DefaultPOWorkFlow');
$default_location = ReadSystemConfig('DefaultGRNLocation');

 if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
 {
   unset($_SESSION['details']);
   unset($_SESSION['header']);
 }

$arr_item = array();


if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php


if(isset($_POST['create_grn'])){

    if($_POST['create_grn'] == "save")
    {
        $req_fields = array('PoNo','SupplierCode','LocationCode');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_SupplierCode  = remove_junk($db->escape($_POST['SupplierCode']));
            $p_PurchaseOrderNo  = remove_junk($db->escape($_POST['PoNo']));
            $p_LocationCode  = remove_junk($db->escape($_POST['LocationCode']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));
            $date    = make_date();
            $datetime    = make_datetime();
            $user = "anush";

            //Get Po last process date time
            $po_last_process_dt = getLastPoProcessDateTime($p_PurchaseOrderNo);

            $po_process = new DateTime($po_last_process_dt);
            $po_select = new DateTime($_SESSION['begindt']);

            //Refresh page
            if($po_select < $po_process)
            {

                $flashMessages->error('This transaction cannot process.','create_grn.php');
            }


            //Select default bin
            $default_stock_bin = DefaultBinFromLocation($p_LocationCode);

            //Read system settings
            $Sales_Profit = ReadSystemConfig('SalesMarginPercentage');
            $avg_cost_update_all_stocks = ReadSystemConfig('UpdateAvgCostForAllStocks');

            //Get all sessions values
            $arr_item= $_SESSION['details'];


            //check details values
            if(count($arr_item)>0)
            {
                //save purchase order 
                
                try
                {
                    $p_GRNCode  = autoGenerateNumber('tfmGrnHT',1);

                    $db->begin();

                    $Grn_count = find_by_sp("call spSelectGRNFromCode('{$p_GRNCode}');");


                    if($default_stock_bin == null)
                    {
                        $flashMessages->warning('Default stock bin not found for this selected location.','create_grn.php');
                    }


                    if($Grn_count)
                    {
                        $flashMessages->warning('This good received note number exist in the system.','create_grn.php');
                    }

                    $IsQtyExist = false;

                    foreach($arr_item as $row => $value)
                        if ($value[5] > 0)
                            $IsQtyExist = true;

                    if(!$IsQtyExist)
                    {
                        $flashMessages->warning('Good received item(s) details not found.','create_grn.php');
                    }

                    //Insert good received note header details
                    $query  = "call spInsertGoodReceivedH('{$p_GRNCode}','{$p_LocationCode}','{$p_PurchaseOrderNo}','{$p_SupplierCode}','{$date}','{$p_Remarks}','{$datetime}','{$date}','{$user}');";
                    $db->query($query);
                   
                    //Update purchase order process date
                    $query  = "call spUpdatePurchaseOrderProcessDate('{$p_PurchaseOrderNo}','{$datetime}');";
                    $db->query($query);

                    //Insert good received note item details
                    foreach($arr_item as $row => $value)
                    {
                        if ($value[5] > 0)
                        {
                          $query  = "call spInsertGoodReceivedD('{$p_GRNCode}','{$value[0]}','{$value[1]}','{$value[2]}',{$value[3]},0,'{$value[6]}',{$value[5]});";
                          $db->query($query);
                        }
                    }

      
                    //==========re-Calculate stock lot to cost price=====================================================================
                    $maintain_stock = ReadSystemConfig('StockLot');
                    $StockLotToCost = ReadSystemConfig('StockLotToCost');

                    if(filter_var($maintain_stock,FILTER_VALIDATE_BOOLEAN) == true)
                    {
                        if(filter_var($StockLotToCost,FILTER_VALIDATE_BOOLEAN) == true)
                        {
                            foreach($arr_item as $row => $value)
                            {
                                $productdetails = ReadProductDatails($value[1]);

                                if ($productdetails["CostPrice"] != $value[3])
                                    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,0,$productdetails["ProductCode"]."-".($productdetails["StockNo"] + 1));
                                else
                                    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,0,$productdetails["ProductCode"]."-".($productdetails["StockNo"]==0?1:$productdetails["StockNo"]));
                            }
                        }
              
                    }
                    //====================================================================================================================

                
                    //==========re-Calculate stock lot to Expire Date ====================================================================
                    $StockLotToExpDate = ReadSystemConfig('StockLotToExpDate');

                    if(filter_var($maintain_stock,FILTER_VALIDATE_BOOLEAN) == true)
                    {
                        if(filter_var($StockLotToExpDate,FILTER_VALIDATE_BOOLEAN) == true)
                        {
                            foreach($arr_item as $row => $value)
                            {
                                $productdetails = ReadProductDatails($value[1]);

                                if ($productdetails["ExpireDate"] != $value[6])
                                    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,0,$productdetails["ProductCode"]."-".($productdetails["StockNo"] + 1));
                                else
                                    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,0,$productdetails["ProductCode"]."-".($productdetails["StockNo"]==0?1:$productdetails["StockNo"]));
                            }
                        }
                    }
                    //====================================================================================================================

                    //Calculate stock       
                    foreach($arr_item as $row => $value)
                    {
                        if ($value[5] > 0)
                        {

                            //Update purchase order received item qty
                            $query  = "call spUpdatePurchaseOrderRecivedQty('{$p_PurchaseOrderNo}','{$value[1]}',{$value[5]});";
                            $db->query($query);

                            //avg cost and sale price calculation
                            $AverageCost =  CalculateAverageCost($value[1],$value[5],$value[3]);
                            $Sale_Price =   round($AverageCost + (($AverageCost * $Sales_Profit) /100));

                            //Insert or update Stock
                            $query  = "call spStock('{$value[0]}','{$p_LocationCode}','{$default_stock_bin}',
                                       '{$value[1]}','{$p_SupplierCode}',{$value[3]},{$Sale_Price},0,{$AverageCost},0,0,0,{$value[5]},'{$value[6]}',
                                         '{$date}','{$date}');";
                            $db->query($query);

                            //Get latest lot code
                            $StockLotNumber = substr($value[0],strrpos($value[0],"-")+1);

                            //Update product
                            $query  = "call spUpdateProductDetailsFromGRN('{$value[1]}','{$p_SupplierCode}',{$value[3]},
                                       {$Sale_Price},0,{$StockLotNumber},'{$value[6]}',
                                         '{$date}','{$user}');";
                            $db->query($query);

                            //Insert stock movement
                            $query  = "call spStockMovement('{$value[0]}','{$p_LocationCode}','{$default_stock_bin}',
                                       '{$value[1]}','','{$p_GRNCode}','{$p_SupplierCode}','002',{$value[3]},{$Sale_Price},0,{$AverageCost},0,{$value[5]},'{$value[6]}',
                                         '{$date}','{$user}');";
                            $db->query($query);


                            //Update average cost price
                            if(filter_var($avg_cost_update_all_stocks,FILTER_VALIDATE_BOOLEAN) == true)
                            {
                              $query  = "call spUpdateAvgCostForAllStock('{$value[1]}',{$AverageCost});";
                              $db->query($query);
                            }


                            //Save Serial 
                            for($i = 1;$i<= $value[5] ;$i++)
                            {
                                $SerialNo  = autoGenerateSerialNumber();
                                $query  = "call spGrnSerialT('{$p_GRNCode}','{$value[0]}','{$value[1]}','{$SerialNo}','{$p_LocationCode}','{$default_stock_bin}','{$value[6]}','{$date}');";

                                $db->query($query);
                            }
                            
                        }
                    }

                    //Change Po status
                    $query  = "call spUpdatePOToProcessStatus('{$p_PurchaseOrderNo}');";
                    $db->query($query);


                    $db->commit();
                    
                    $flashMessages->success('Good received note has been saved successfully,\n   Your good received note No: '.$p_GRNCode,'create_grn.php');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to create good received note. '.$ex->getMessage(),'create_grn.php');

                }

            }
            else
            {
                $flashMessages->warning('Good received note item(s) not found!','create_grn.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'create_grn.php');
        }

    }
}

if (isset($_POST['_prodcode'])) {
    $prodcode = remove_junk($db->escape($_POST['_prodcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$prodcode);
    $_SESSION['details'] = $arr_item;

    $_SESSION['begindt'] =  make_datetime();

    return include('_partial_grndetails.php');  
}

if (isset($_POST['Edit'])) {
    $maintain_stock = ReadSystemConfig('StockLot');
    $StockLotToExpDate = ReadSystemConfig('StockLotToExpDate');


    $ProductCode = remove_junk($db->escape($_POST['hProductCode']));
    $StockCode = remove_junk($db->escape($_POST['hStockCode']));
    $GrnQty = remove_junk($db->escape($_POST['GrnQty']));
    $ExpireDate = convert_date(remove_junk($db->escape($_POST['ExpireDate'])));

    $arr_item = $_SESSION['details'];
    //Change Grn qty
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,5,$GrnQty);
    //Change Expire date
    $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,6,$ExpireDate);

    $productdetails = ReadProductDatails($ProductCode);

    if(filter_var($maintain_stock,FILTER_VALIDATE_BOOLEAN) == true)
    {
        if(filter_var($StockLotToExpDate,FILTER_VALIDATE_BOOLEAN) == true)
        {  
            if ($productdetails["ExpireDate"] != $ExpireDate)
                $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,0,$productdetails["ProductCode"]."-".($productdetails["StockNo"] + 1));
            else
                $arr_item = ChangValueFromListOfArray( $arr_item,$ProductCode,0,$productdetails["ProductCode"]."-".($productdetails["StockNo"]==0?1:$productdetails["StockNo"]));
        }
     }

    $_SESSION['details'] = $arr_item;

    return include('_partial_grndetails.php');  
}


if (isset($_POST['_PoNo'])) {
    $_SESSION['header']  = null; 
    $_SESSION['details'] = null;

    $PoNo = remove_junk($db->escape($_POST['_PoNo']));

    $maintain_stock = ReadSystemConfig('StockLot');
    $StockLotToCost = ReadSystemConfig('StockLotToCost');

    $all_PoDetsils = find_by_sql("call spSelectAllPODetailsFromPONo('{$PoNo}');");
    $arr_item = $_SESSION['details'];

    if(filter_var($maintain_stock,FILTER_VALIDATE_BOOLEAN) == true)
    {
        if(filter_var($StockLotToCost,FILTER_VALIDATE_BOOLEAN) == true)
        {
            foreach($all_PoDetsils as $row => $value)
            {
                $productdetails = ReadProductDatails($value["ProductCode"]);

                if ($productdetails["CostPrice"] != $value["CostPrice"])
                    $arr_item[]  = array($productdetails["ProductCode"]."-".($productdetails["StockNo"] + 1),$value["ProductCode"],$value["ProductDesc"],$value["CostPrice"],($value["Qty"]-$value["RecivedQty"]),0,'');
                else
                    $arr_item[]  = array($productdetails["ProductCode"]."-".($productdetails["StockNo"]==0?1:$productdetails["StockNo"]),$value["ProductCode"],$value["ProductDesc"],$value["CostPrice"],($value["Qty"]-$value["RecivedQty"]),0,'');
            }
        }
        else
        {
            foreach($all_PoDetsils as $row => $value)
            {
                $arr_item[]  = array($value["ProductCode"],$value["ProductCode"],$value["ProductDesc"],$value["CostPrice"],($value["Qty"]-$value["RecivedQty"]),0,'');
            }
        }
    }
    else
    {
        foreach($all_PoDetsils as $row => $value)
        {
            $arr_item[]  = array($value["ProductCode"],$value["ProductCode"],$value["ProductDesc"],$value["CostPrice"],($value["Qty"]-$value["RecivedQty"]),0,'');
        }
    }

    $_SESSION['details'] = $arr_item; 
    
    return include('_partial_grndetails.php'); 
}



if (isset($_POST['_RowNo'])) {
    $ProductCode = remove_junk($db->escape($_POST['_RowNo']));
    $serchitem = ArraySearch($arr_item,$ProductCode);

    return include('_partial_grnitem.php'); 
}


if (isset($_POST['Supplier'])) {
    unset($_SESSION['details']); 
    
    $SupplierCode = remove_junk($db->escape($_POST['Supplier']));

    $all_Po = find_by_sql("call spSelectRelesePurchaseOrderFromSupplierCode('{$SupplierCode}');");
    echo "<option>Select Purchase Order</option>";

    foreach($all_Po as &$value){
        $arr_PRNNo[]  = array('PoNo' =>$value["PoNo"]);
        echo "<option value ={$value["PoNo"]}>{$value["PoNo"]}</option>";
    }

    return;
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
       Create Goods Received Note
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Goods Received Note</li>
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
    <form method="post" action="create_grn.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="create_grn" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
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
                                <label>Goods Received No</label>
                                <input type="text" class="form-control" name="GRNNo" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                            </div>
                        </div>


                        <div class="form-group">
                            <label>Purchase Order</label>
                            <select class="form-control select2" style="width: 100%;" name="PoNo" id="PoNo" onchange="FillDetails();" required="required">
                                <option value="">Select Purchase Order</option>
                            </select>
                        </div>
                       
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2" style="width: 100%;" name="SupplierCode" id="SupplierCode" required="required" onchange="FillPO();">
                                <option value="">Select Supplier</option><?php  foreach ($all_Supplier as $supp): ?>
                                <option value="<?php echo $supp['SupplierCode'] ?>" <?php if($supp['SupplierCode'] === $arr_header['SupplierCode']): echo "selected"; endif; ?>><?php echo $supp['SupplierName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control select2" style="width: 100%;" name="LocationCode" id="LocationCode" required="required">
                                <option value="">Select Location</option><?php  foreach ($all_locations as $loc): ?>
                                <option value="<?php echo $loc['LocationCode'] ?>" <?php if($loc['LocationCode'] === $default_location): echo "selected"; endif; ?>><?php echo $loc['LocationName'] ?>
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
                                <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.."><?php echo remove_junk($arr_header['Remarks']) ?></textarea>
                            </div>
                        </div>
                       


                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Goods Receive Note Item(s)</h3>

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
                        <?php include('_partial_grndetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">
    function AddItem(ctrl, event) {
        event.preventDefault();

        if ($('#ProductCode').val() == "") {
            $("#ProductCode").focus();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#ProductDesc').val() == "") {
            $("#ProductCode").focus();
            bootbox.alert('Please select a product code.');
        }
        else if ($('#CostPrice').val() <= 0) {
            $("#CostPrice").focus();
            bootbox.alert('Please enter valid cost price.');
        }
        else if ($('#Qty').val() <= 0) {
            $("#Qty").focus();
            bootbox.alert('Please enter valid purchase qty.');
        }
        else {
            $.ajax({
                url: 'create_grn.php',
                type: "POST",
                data: $("form").serialize(),
                success: function (result) {
                    $("#table").html(result);
                    $('#message').load('_partial_message.php');
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

    function FillPO() {
        var Supplier = $('#SupplierCode').val();
        $.ajax({
            url: "create_grn.php",
            type: "POST",
            data: { Supplier: Supplier},
            success: function (result) {
                $("#PoNo").html(""); // clear before appending new list
                $("#PoNo").html(result);
            }
        });

    }


    function FillDetails() {
        var PrnNo = $('#PoNo').val();

        $.ajax({
            type: "POST",
            url: "create_grn.php", // Name of the php files
            data: { "_PoNo": PrnNo },
            success: function (result) {
                $("#table").html(result);
            }
        });

    }
</script>

<?php include_once('layouts/footer.php'); ?>

