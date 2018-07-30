<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Quotation';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Quotation Create');

$default_flow = ReadSystemConfig('DefaultSOWorkFlow');
$default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');

$current_user = current_user();

//$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_workflows = find_by_sql("call spSelectAllWorkFlow();");
$all_locations = find_by_sql("call spSelectAllLocations();");
$all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");
$all_Customers = find_by_sql("call spSelectCustomerFromSalesmanCode('{$current_user["EmployeeCode"]}');");

$arr_item = array();

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}


if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php

if(isset($_POST['create_salesorder'])){

    if($_POST['create_salesorder'] == "save")
    {
        $req_fields = array('CustomerCode','WorkFlowCode','SalesmanCode','ValidThru');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_CustomerCode  = remove_junk($db->escape($_POST['CustomerCode']));
            $p_WorkFlowCode  = remove_junk($db->escape($_POST['WorkFlowCode']));
            $p_SalesmanCode  = remove_junk($db->escape($_POST['SalesmanCode']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));
            $p_ValidThru  = remove_junk($db->escape($_POST['ValidThru']));
            $date    = make_date();
            $user =  current_user();

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {
                //save quotation
                try
                {

                    $p_SOCode  = autoGenerateNumber('tfmSalesOrderHT',1);



                    $So_count = find_by_sp("call spSelectSalesOrderHFromCode('{$p_SOCode}');");

                    if($So_count)
                    {
                        $flashMessages->warning('This quotation number exist in the system.','create_salesorder.php');
                    }

                    $db->begin();

                    //Insert quotation header details
                    $query  = "call spInsertSalesOrderH('{$p_SOCode}','','{$p_CustomerCode}','{$p_SalesmanCode}','{$date}','{$p_WorkFlowCode}','{$p_Remarks}',{$p_ValidThru},'{$date}','{$user["username"]}');";
                    $db->query($query);

                    //Insert quotation item details
                    $TotalAmount = 0;
                    foreach($arr_item as $row => $value)
                    {
                        $TotalAmount += $value[4];
                        $query  = "call spInsertSalesOrderD('{$p_SOCode}','{$value[0]}','{$value[1]}',0,{$value[2]},{$value[3]},{$value[5]},{$value[4]},{$value[6]},{$value[7]},{$value[8]});";
                        $db->query($query);
                    }

                    InsertRecentActvity("Quotation","Reference No. ".$p_SOCode);

                    $db->commit();

                    //Send Mail
                    $WorkFlowDetForMail = find_by_sp("call spSelectWorkFlowLevel1DetailsForMail('{$p_WorkFlowCode}');");
                    $Customer = find_by_sp("call spSelectCustomerFromCode('{$p_CustomerCode}');");

                    $Subject = 'You have to Approve Quotation';

                    $htmlContent = '
                    <html>
                    <head></head>
                    <body>
                        <p>Hi '.$WorkFlowDetForMail['EmployeeName'].',<p>
                        <h1>'.$Subject.'</h1>
                        <table cellspacing="0" style="border: 2px dashed #008000; width: 400px; height: 300px;">
                            <tr>
                                <th align="left">Quotation No: </th><td>'.$p_SOCode.'</td>
                            </tr>
                            <tr style="background-color: #e0e0e0;">
                                <th align="left">Quotation Date: </th><td>'.$date.'</td>
                            </tr>
                            <tr >
                                <th align="left">Customer: </th><td>'.$Customer['CustomerName'].'</td>
                            </tr>
                            <tr style="background-color: #e0e0e0;">
                                <th align="left">Quotation Amount:</th><td>'.number_format($TotalAmount,2).'</td>
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



                    $flashMessages->success('Quotation has been saved successfully,\n   Your Quotation No: '.$p_SOCode,'create_salesorder.php');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to create quotation. '.$ex->getMessage(),'create_salesorder.php');

                }

            }
            else
            {
                $flashMessages->warning('Quotation item(s) not found!','create_salesorder.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'create_salesorder.php');
        }

    }
}

if (isset($_POST['_stockcode'])) {
    $stockcode = remove_junk($db->escape($_POST['_stockcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$stockcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_sodetails.php');
}

if (isset($_POST['SalesmanCodeSelection'])) {
    $SalesmanCode = remove_junk($db->escape($_POST['SalesmanCodeSelection']));

    $Customer = find_by_sql("call spSelectCustomerFromSalesmanCode('{$SalesmanCode}');");
    echo "<option value=''>Select Customer</option>";

    foreach($Customer as &$value){
        echo "<option value ={$value["CustomerCode"]}>{$value["CustomerName"]}</option>";
    }
    return;
}

if (isset($_POST['SalePriceValidate'])) {
    $SalesPrice = remove_junk($db->escape($_POST['SalePriceValidate']));
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));

    $product = find_by_sp("call spSelectProductFromCode('{$ProductCode}');");

    //Read system settings
    $Sales_Profit = ReadSystemConfig('SalesMarginPercentage');

    $AvgCostPrice = $product['AvgCostPrice'];


}

if (isset($_POST['_productcode'])) {
    $productcode = remove_junk($db->escape($_POST['_productcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$productcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_sodetails.php');
}

if (isset($_POST['Add'])) {
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $ProductDesc = remove_junk($db->escape($_POST['ProductDesc']));
    $AverageCost = remove_junk($db->escape($_POST['AverageCost']));
    $SalesPercentage = remove_junk($db->escape($_POST['SalesPercentage']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $Qty = remove_junk($db->escape($_POST['Qty']));

    

    $arr_item = $_SESSION['details'];

    if($SalePrice == "" || $SalePrice < 0)
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
            $TaxAmount = round((($ItemAmount * $ToatlTax)/100));
            $ToatlAmount = $TaxAmount + $ItemAmount;


            $arr_item[] = array($ProductCode,$ProductDesc,$SalePrice,$Qty,$ToatlAmount,$TaxAmount,0,$AverageCost,$SalesPercentage);
            $_SESSION['details'] = $arr_item;
        }
    }
    return include('_partial_sodetails.php');
}


if (isset($_POST['CustomerCode'])) {

    $CustomerCode = remove_junk($db->escape($_POST['CustomerCode']));


    $default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');
    $all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");
    $Customer =    find_by_sp("call spSelectCustomerFromCode('{$CustomerCode}');");

    echo "<option>Select Salesman</option>";
    foreach($all_salesrep as &$value){
        $Selected = $value["EpfNumber"] == $Customer["SalesPersonCode"] ? "selected":"";
        echo "<option value ={$value["EpfNumber"]}  {$Selected} >{$value["EmployeeName"]}</option>";
    }
    return;
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Create Quotation
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
    <form method="post" action="create_salesorder.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="create_salesorder" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
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
                                <label>Quotation No</label>
                                <input type="text" class="form-control" name="SoNo" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Approvals Flow</label>
                            <select class="form-control select2" style="width: 100%;" name="WorkFlowCode" id="WorkFlowCode" required="required">
                                <option value="">Select Approvals Work-Flow</option><?php  foreach ($all_workflows as $wflow): ?>
                                <option value="<?php echo $wflow['WorkFlowCode'] ?>" <?php if($wflow['WorkFlowCode'] === $default_flow): echo "selected"; endif; ?>><?php echo $wflow['Description'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.."></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Salesman</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesmanCode" id="SalesmanCode" required="required"onchange="FillCustomer();">
                                <option value="">Select Salesman</option><?php  foreach ($all_salesrep as $srep): ?>
                                <option value="<?php echo $srep['EpfNumber'] ?>" <?php if($srep['EpfNumber'] === $current_user["EmployeeCode"]): echo "selected"; endif; ?>><?php echo $srep['EmployeeName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Valid Period</label>
                            <input type="text" class="form-control pull-right integer" autocomplete="off" name="ValidThru" id="ValidThru" placeholder="Days" required="required"  onkeyup='if(!validnum(this.value)) this.value="";'/>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Customer</label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required">
                                <option value="">Select Customer</option><?php  foreach ($all_Customers as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>"><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="SoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <!-- /.box-header -->
        <form method="post" action="create_salesorder.php">
            <input type="hidden" value="create_salesorder" name="create_salesorder" />

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product Code</label>
                            <input type="text" class="form-control" name="ProductCode" id="ProductCode" placeholder="Product Code" required="required" autocomplete="off" />
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
                            <input type="text" class="form-control decimal" name="SalePrice" id="SalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Sale Price" required="required" onchange="ValidateSalePrice();" disabled />
                        </div>

                        <div class="form-group">
                            <label>Sales Percentage (%)</label>
                            <input type="number" class="form-control integer" name="SalesPercentage" id="SalesPercentage" placeholder="Sales Percentage (%)" />
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control integer" name="pQty" id="Qty" placeholder="Qty" required="required" />
                        </div>

                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-info" id="item" onclick="AddItem(this, event);" value="item">&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;</button>
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
                        <?php include('_partial_sodetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">
    function AddItem(ctrl, event) {
        event.preventDefault();

        var ProductCode = $('#ProductCode').val();
        var ProductDesc = $('#ProductDesc').val();
        var SalePrice = $('#SalePrice').val();
        var AverageCost =     $('#AverageCost').val() == null || $('#AverageCost').val() == "" ? 0 : $('#AverageCost').val();
        var SalesPercentage = $('#SalesPercentage').val() == null || $('#SalesPercentage').val() == "" ? 0:$('#SalesPercentage').val();
        var Qty = $('#Qty').val();
       

        if ($('#ProductCode').val() == "") {
            bootbox.alert('Please select correct product.');
            $("#ProductCode").focus();
        }
        else if ($('#SalePrice').val() == "" || $('#SalePrice').val() < 0) {
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
                url: 'create_salesorder.php',
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
                    $('#CostPrice').val('');
                    $('#Qty').val('');

                    $('#AverageCost').val('');
                    $('#SIH').val('');
                    $('#SalesPercentage').val('');

                    $('.loader').fadeOut();
                    $('#ProductCode').focus();
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
                $('#SalePrice').val("0.00");
                $('#AverageCost').val(map[item].avgcost);
                $('#SIH').val(map[item].sih);

                $('#SalePrice').focus();
                return map[item].id;
            }
        });
    });



    function FillCustomer() {
        $('.loader').show();

        var SalesmanCode = $('#SalesmanCode').val();
        $.ajax({
            url: "create_salesorder.php",
            type: "POST",
            data: { SalesmanCodeSelection: SalesmanCode },
            success: function (result) {
                $("#CustomerCode").html(""); // clear before appending new list
                $("#CustomerCode").html(result);
                $('.loader').fadeOut();
            }
        });
    }

    function validnum(a) {
        if (a > 30)
            $("#ValidThru").css('background-color', 'orange');
        else if (a > 90) {
            $("#ValidThru").css('background-color', '');
            $("#ValidThru").css('background-color', 'red');
        }
        else if (a < 30)
            $("#ValidThru").css('background-color', '');

        return ((a >= 0) && (a <= 365));
    }


    function ValidateSalePrice()
    {
        var ProductCode = $('#ProductCode').val();
        var SalePrice = $('#SalePrice').val();

        if (ProductCode != "") {
            $('.loader').show();

            $.ajax({
                type: "POST",
                url: "create_salesorder.php", // Name of the php files
                data: { "SalePriceValidate": SalePrice, "ProductCode": ProductCode },
                success: function (result) {
                    if (result == "red")
                        $("#SalePrice").css('background-color', 'red');
                    else
                  $("#SalePrice").css('background-color', '');

                    $('.loader').fadeOut();
                }
            });
        }
    }


    $('#SalesPercentage').bind('input', function () {

        var AverageCost = $('#AverageCost').val();
        var SalesPercentage = $(this).val();

        if ($(this).val() < 0) {
            bootbox.alert('Sales percentage cannot be negative.');
            $(this).val('');
        }
        else if (AverageCost == "" || AverageCost == null || AverageCost == 0)
        {
            bootbox.alert('Invalid average cost price.');
            $(this).val('');
        }
        else
        {
            var value = (parseFloat((AverageCost * SalesPercentage) / 100) + parseFloat(AverageCost)).toFixed(2);
            $('#SalePrice').val(value);
        }
    });


  //  function FillSalesRep() {
  //      $('.loader').show();

  //      var CustomerCode = $('#CustomerCode').val();

  //      $.ajax({
  //          type: "POST",
  //          url: "create_salesorder.php", // Name of the php files
  //          data: { "CustomerCode": CustomerCode },
  //          success: function (result) {
  //              $("#SalesmanCode").html("");
  //              $("#SalesmanCode").html(result);
  //              $('.loader').fadeOut();
  //          }
  //      });
  //}

  //$('#SerialCode').keypress(function (e) {
  //    var SerialCode = $('#SerialCode').val();
  //    var LocationCode = $('#LocationCode').val();
  //    if (e.which == 13) {

  //        if (LocationCode == "") {
  //            bootbox.alert('Please select stock location.');
  //        }
  //        else if (SerialCode == "") {
  //            bootbox.alert('Please enter serial code.');
  //        }
  //        else
  //        {
  //            var StockCode = "";
  //            var SerialNo = "";
  //            var ProductDesc = "";
  //            var CostPrice = 0.00;
  //            var SalePrice = 0.00;
  //            var SIH = 0;

  //            $.ajax({
  //                url: 'autocomplete.php',
  //                type: 'POST',
  //                data: { SerialCode: SerialCode, LocationCode: LocationCode },
  //                dataType: 'json',
  //                success: function (data) {
  //                    jQuery(data).each(function (i, item) {
  //                        StockCode = item.StockCode;
  //                        SerialNo = item.SerialNo;
  //                        ProductDesc = item.ProductDesc;
  //                        CostPrice = item.CostPrice;
  //                        SalePrice = item.SalePrice;
  //                        SIH = item.SIH;
  //                    });
  //                },
  //                complete: function (data) {
  //                    if (StockCode == "") {
  //                        bootbox.alert('Invalid serial code.');

  //                        $('#StockCode').val('');
  //                        $('#ProductDesc').val('');
  //                        $('#SalePrice').val(0.00);
  //                        $('#CostPrice').val(0.00);
  //                        $('#SerialCode').focus();
  //                    }
  //                    else
  //                    {
  //                        $('#StockCode').val(StockCode);
  //                        $('#ProductDesc').val(ProductDesc);
  //                        $('#CostPrice').val(parseFloat(CostPrice).toFixed(2));
  //                        $('#SalePrice').val(parseFloat(SalePrice).toFixed(2));

  //                        $('#SalePrice').focus();
  //                    }
  //                }
  //            });

  //        }
  //  }
    //});

    $('#ValidThru').keypress(function (e) {
       // alert(this.value);
  });


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

