<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'CPO Preparation';
require_once('includes/load.php');

UserPageAccessControle(1,'Customer PO Preparation');

$arr_ScanItem = array();

if(isset($_POST['ScanPO'])){
    $req_fields = array('CPONo','CusPoDate','CustomerName');

    validate_fields($req_fields);

    if(empty($errors))
    {
        $_SESSION['ScanItem']=array();

        $CPONo =  remove_junk($db->escape($_POST['CPONo']));
        $QuotationNo =  remove_junk($db->escape($_POST['SoNo']));
        $CusPoDate =  remove_junk($db->escape($_POST['CusPoDate']));
        $ReferenceNo =  remove_junk($db->escape($_POST['ReferenceNo']));
        $CustomerName =  remove_junk($db->escape($_POST['CustomerName']));

        $All_CPODetails = find_by_sql("call spSelectCustomerPurchaseOrderDFromCodePendingPreparation('{$CPONo}');");

    };
};

if(isset($_POST['_ScanItem'])){
    $req_fields = array('_CusPoNo');

    validate_fields($req_fields);

    if(empty($errors))
    {
        $CPONo =  remove_junk($db->escape($_POST['_CusPoNo']));
        $Serial =  remove_junk($db->escape($_POST['_ScanItem']));

        $ScanItem = find_by_sql("call spSelectStockFromSerialOnly('{$Serial}');");

        // Check item alredy allocated
        $ScanItemValidation = find_by_sql("call spSelectAllocatedItemSerialFromSerial('{$Serial}');");

        if($ScanItemValidation !=null)
        {
            $flashMessages->warning('Sereal number already scaned!');
            return;
        }

        foreach($ScanItem as &$Item)
        {
            $ProdCode = remove_junk($Item["ProductCode"]);
            $StkCode = remove_junk($Item["StockCode"]);;
        }

        if($_SESSION['ScanItem'] != null){
            $arr_ScanItem = $_SESSION['ScanItem'];
        };

        // Get Scan product qty to validate
        $ScanProductCount = 0;
        $ScanedSerealCount = 0;
        foreach($arr_ScanItem as &$ScanArr){
            if($Serial == remove_junk($ScanArr["SerialNo"])){
                $ScanedSerealCount += 1;
            }

            if($ProdCode == remove_junk($ScanArr["ProductCode"])){
                $ScanProductCount += 1;
            }
        }


        // Get product balance qty to validate with scan Qty
        $All_CPODetails = find_by_sql("call spSelectCustomerPurchaseOrderDFromCodePendingPreparation('{$CPONo}');");
        foreach($All_CPODetails as &$Item)
        {
            if($ProdCode == remove_junk($Item["ProductCode"])){
                $ProductCodeBalanceQty = remove_junk(ucfirst($Item['PreparationBalance']));
            }
        }


        if($ScanedSerealCount > 0){
            $flashMessages->warning('Sereal number already scaned!');
            return;
        }
        elseif($ScanProductCount == $ProductCodeBalanceQty)
        {
            $flashMessages->warning('PO product quantity exceeded!');
            return;
        }
        else{
            $flashMessages->warning('');
        }



        $arr_ScanItem[]=array("SerialNo"=>$Serial,"CusPoNo"=>$CPONo,"ProductCode"=>$ProdCode);
        $_SESSION['ScanItem']=array();
        $_SESSION['ScanItem']=$arr_ScanItem;
    }
    echo remove_junk($ProdCode);
    return ;
};

 //Update scaned item to the table
if(isset($_POST['_UpdateScan'])){
    if($_SESSION['ScanItem'] != null){
        $user =  current_user();
        $arr_ScanItem = $_SESSION['ScanItem'];
        $CPONo =  remove_junk($db->escape($_POST['_CPO']));

        try{
            $db->begin();

            foreach($arr_ScanItem as &$ScanArr){
                $query="call spInsertCusPurchaseOrderSerial('{$ScanArr["SerialNo"]}','{$ScanArr["CusPoNo"]}','{$ScanArr["ProductCode"]}','{$user["username"]}');";
                $db->query($query);
            }

            $query="call spUpdateCustomerPOPreparedQty('{$CPONo}');";
            $db->query($query);

            InsertRecentActvity("Item allocated for CPO ","CPO No. ".$CPONo);

            $db->commit();



            $flashMessages->success('Customer purchase order ( '.$CPONo. ' )Serials updated .');

            echo 'ok';
            return;
        }
        catch(Exception $ex){
            $db->rollback();

            $flashMessages->error('Serials not updated'.$ex->getMessage());
        }
    }
}

?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Customer PO Preparation
        <small>CPO Scan Item</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Customer Purchase Order</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<section class="content">
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="btn-group">
                        <button type="button" name="UpdateCPOScan" class="btn btn-primary" id="btnUpdateScan">&nbsp;&nbsp;Update Scan&nbsp;&nbsp;</button>
                        <button type="button" class="btn btn-warning" onclick="window.location = 'customer_po_preparation.php'">Cancel  </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Customer Purchase Order Details</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <label>CPO No</label>
                    <input type="text" class="form-control" name="CPONo" id="txtCpo" disabled="disabled" value="<?php echo $CPONo ?>" />
                </div>
                <div class="col-md-4">
                    <label>Quotation NO</label>
                    <input type="text" class="form-control" name="QuotationNo" id="txtQuotationNo" disabled="disabled" value="<?php echo $QuotationNo ?>" />
                </div>
                <div class="col-md-4">
                    <label>CPO Date</label>
                    <input type="text" class="form-control" name="CPODate" id="txtCPODate" disabled="disabled" value="<?php echo $CusPoDate ?>" />
                </div>
                <div class="col-md-4">
                    <label>Reference No</label>
                    <input type="text" class="form-control" name="ReferenceNo" id="txtReferenceNo" disabled="disabled" value="<?php echo $ReferenceNo ?>" />
                </div>
                <div class="col-md-4">
                    <label>Customer Name</label>
                    <input type="text" class="form-control" name="CustomerName" id="txtCustomerName" disabled="disabled" value="<?php echo $CustomerName ?>" />
                </div>
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Customer Purchase Order Item Scan</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                <div class="row form-group">
                    <div class="col-md-4">
                        <label>Scan Item</label>
                        <input type="text" class="form-control" name="ScanedItem" id="txtScanItem" placeholder="Scan Item Barcode" autofocus />
                    </div>
                    <div class="col-md-4">
                        <div id="scanmessage">
                            <?php
                            include('_partial_message.php');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="tblCPODetails" class="table table-bordered table-striped datatable">
                            <thead>
                                <tr>
                                    <th>CPO No</th>
                                    <th>Product Code</th>
                                    <th>Product</th>
                                    <th>Balance To Prepare</th>
                                    <th>Current Allocation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($All_CPODetails as $CPODetails): ?>
                                <tr>
                                    <td>
                                        <?php echo remove_junk(ucfirst($CPODetails['CusPoNo'])); ?>
                                    </td>
                                    <td class="pCode">
                                        <?php echo remove_junk(ucfirst($CPODetails['ProductCode'])); ?>
                                    </td>
                                    <td>
                                        <?php echo remove_junk(ucfirst($CPODetails['ProductDesc'])); ?>
                                    </td>
                                    <td class="BalanceToPreparation">
                                        <?php echo remove_junk(ucfirst($CPODetails['PreparationBalance'])); ?>
                                    </td>
                                    <td class="CrrAllocatedQty">
                                        0
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once('layouts/footer.php'); ?>

<script>
    $(document).ready(function () {
        $("#txtScanItem").on('keypress', function (e) {
            var code = e.keycode || e.which;

            if (code == 13) {
                var pCpo = $("#txtCpo").val();
                var pScanItem = $("#txtScanItem").val();

                $('.loader').show();
                $.ajax({
                    url: 'customer_po_itemscan.php',
                    type: "POST",
                    data: { _ScanItem: 'Scan', _CusPoNo: pCpo, _ScanItem: pScanItem },
                    success: function (result) {
                        $("#txtScanItem").val("");

                        UpdateAlocatedQty(result);
                    },
                    complete: function (result) {
                        $('#scanmessage').load(location.href + " #scanmessage>*", "");
                        $('.loader').fadeOut();
                    }
                });
            }
        });
    });

    function UpdateAlocatedQty(prodCode) {
        $("table tr").each(function (index) {
            if (index !== 0) {
                $row = $(this);
                var _prodCode = $.trim($row.find(".pCode").text());
                var _CrrAllocatedQty = parseInt($.trim($row.find(".CrrAllocatedQty").text()));
                var _BalanceToPreparation = parseInt($.trim($row.find(".BalanceToPreparation").text()));

                prodCode = prodCode.replace(/(\r\n|\n|\r)/gm, "");

                if (_prodCode == $.trim(prodCode)) {
                    var qty = _CrrAllocatedQty + 1
                    $row.find(".CrrAllocatedQty").text(qty);
                };

                _CrrAllocatedQty = parseInt($.trim($row.find(".CrrAllocatedQty").text()));

                if (_CrrAllocatedQty == _BalanceToPreparation) {

                    $row.find(".BalanceToPreparation").css('background-color', '#6FDD66');
                };
            };
        });
    };

    $(document).ready(function () {
        $("#btnUpdateScan").click(function () {
            debugger;
            var cpo = $("#txtCpo").val();
            $('.loader').show();
            $.ajax({
                url: 'customer_po_itemscan.php',
                type: 'POST',
                data: { _UpdateScan: 'UpdateScan', _CPO: cpo },
                complete: function (result) {
                    $('.loader').fadeOut();
                    window.location.href = 'customer_po_preparation.php';
                }
            })
        })
    })
</script>