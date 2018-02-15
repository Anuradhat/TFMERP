<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Customer Purchase Order';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

$default_flow = ReadSystemConfig('DefaultCUSPOWorkFlow');


$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_workflows = find_by_sql("call spSelectAllWorkFlow();");
$all_locations = find_by_sql("call spSelectAllLocations();");

$arr_item = array();

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php

if(isset($_POST['create_customerpo'])){

    if($_POST['create_customerpo'] == "save")
    {
        $req_fields = array('CustomerCode','SalesOrderCode','WorkFlowCode');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_CustomerCode  = remove_junk($db->escape($_POST['CustomerCode']));
            $p_SalesOrderCode  = remove_junk($db->escape($_POST['SalesOrderCode']));
            $p_WorkFlowCode  = remove_junk($db->escape($_POST['WorkFlowCode']));
            $p_Remarks  = remove_junk($db->escape($_POST['Remarks']));
            $date    = make_date();
            $user = "anush";

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {
                //Create customer purchase order 
                try
                {
                    $p_CusPoCode  = autoGenerateNumber('tfmCusPurchaseOrderHT',1);

                    $db->begin();

                    $CusPo_count = find_by_sp("call spSelectCustomerPurchaseOrderHFromCode('{$p_CusPoCode}');");

                    if($CusPo_count)
                    {
                        $session->msg("d", "This customer purchase order exist in the system.");
                        redirect('create_customerpo.php',false);
                    }

                    //Insert customer purchase order header details
                    $query  = "call spInsertCusPurchaseOrderH('{$p_CusPoCode}','{$p_SalesOrderCode}','{$p_CustomerCode}','{$date}','{$p_WorkFlowCode}','{$p_Remarks}','{$date}','{$user}');";
                    $db->query($query);


                    //Insert customer purchase order details
                    foreach($arr_item as $row => $value)
                    {
                        $query  = "call spInsertCusPurchaseOrderD('{$p_CusPoCode}','{$value[0]}','{$value[1]}',{$value[2]},{$value[3]},{$value[4]},{$value[5]});";
                        $db->query($query);
                    }

                    $db->commit();
                    
                    unset($_SESSION['details']);

                    $session->msg('s',"Customer purchase order has been saved successfully,\n   Your customer purchase order No: ".$p_CusPoCode);
                    redirect('create_customerpo.php', false);

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $session->msg('d',' Sorry failed to added!');
                    redirect('create_customerpo.php', false);
                }

            }
            else
            {
                $session->msg("w",' Customer purchase order item(s) not found!');
                redirect('create_customerpo.php',false);
            }
        }
        else
        {
            $session->msg("d", $errors);
            redirect('create_customerpo.php',false);
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




if (isset($_POST['Add'])) {
    $LocationCode = remove_junk($db->escape($_POST['LocationCode']));
    $StockCode = remove_junk($db->escape($_POST['StockCode']));
    $ProductDesc = remove_junk($db->escape($_POST['ProductDesc']));
    $CostPrice = remove_junk($db->escape($_POST['CostPrice']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $Qty = remove_junk($db->escape($_POST['Qty']));

    
    $arr_item = $_SESSION['details'];
    
    if($LocationCode == "" || $StockCode == "")
    {
        $session->msg('d',"Location or stock code is not found!");
    }
    else if($SalePrice == "")
    {
        $session->msg('d',"Invalid sales price.");
    }
    else if($Qty <= 0)
    {
        $session->msg('d',"Invalid item qty.");
    }
    else
    {

        if(ExistInArray($arr_item,$StockCode))
        {
            $session->msg('d',"This item exist in the list.");
        }
        else
        {
            $arr_item[] = array($StockCode,$ProductDesc,$CostPrice,$SalePrice,$Qty,$Qty * $SalePrice); 
            $_SESSION['details'] = $arr_item;     
        }
    }
    return include('_partial_cuspodetails.php'); 
}

if (isset($_POST['CustomerChanged'])) {
    $arr_item = array();

    $_SESSION['details'] = null;

    return include('_partial_cuspodetails.php'); 
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

    $all_SO = find_by_sql("call spSelectApprovedSalesOrderFromCustomerCode('{$CustomerCode}');");
    
    echo "<option value=''>Select Sales Order</option>";
    foreach($all_SO as &$value){
        echo "<option value ={$value["SoNo"]}>{$value["SoNo"]}</option>";
    }

    return;
}


if (isset($_POST['SalesOrderCode'])) {
    $_SESSION['details']  = null;

    $SalesOrderCode = remove_junk($db->escape($_POST['SalesOrderCode']));

    $SO_Details = find_by_sql("call spSelectSalesOrderDFromCode('{$SalesOrderCode}');");
    
    foreach($SO_Details as &$value){
        $arr_item[]  = array($value["StockCode"],$value["ProductDesc"],$value["CostPrice"],$value["SellingPrice"],$value["Qty"],$value["Amount"]);
    }
    $_SESSION['details'] = $arr_item; 

    return include('_partial_cuspodetails.php');
}


if (isset($_POST['_RowNo'])) {
    $StockCode = remove_junk($db->escape($_POST['_RowNo']));
    $serchitem = ArraySearch($arr_item,$StockCode);

    return include('_partial_cuspoitem.php'); 
}

if (isset($_POST['Edit'])) {
    $StockCode = remove_junk($db->escape($_POST['StockCode']));
    $Qty = remove_junk($db->escape($_POST['Qty']));

    $arr_item = $_SESSION['details'];

    //Change Qty
    $arr_item = ChangValueFromListOfArray( $arr_item,$StockCode,4,$Qty);

    $_SESSION['details'] = $arr_item;

    return include('_partial_cuspodetails.php');  
}
?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Create Customer Purchase Order
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

<!-- Main content -->
<section class="content">
    <!-- Your Page Content Here -->
    <form method="post" action="create_customerpo.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="create_customerpo" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
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
                                <label>Customer PO No</label>
                                <input type="text" class="form-control" name="CusPoNo" placeholder="Code will generate after save" readonly="readonly" disabled="disabled" />
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="SoDate" id="SoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.."></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Customer</label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required" onchange="FillSO();">
                                <option value="">Select Customer</option><?php  foreach ($all_Customers as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>"><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control select2" style="width: 100%;" name="LocationCode" id="LocationCode" disabled>
                                <option value="">Select Location</option><?php  foreach ($all_locations as $loc): ?>
                                <option value="<?php echo $loc['LocationCode'] ?>"><?php echo $loc['LocationName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Sales Order</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesOrderCode" id="SalesOrderCode" required="required" onchange="FillSODetails();">
                                <option value="">Select Sales Order</option>
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
                            <label>Stock Code</label>
                            <input type="text" class="form-control" name="StockCode" id="StockCode" placeholder="Stock Code" required="required" autocomplete="off" />
                        </div>   
                        
                        <div class="form-group">
                         <label>Qty</label>
                          <input type="number" class="form-control integer" name="pQty" id="Qty" placeholder="Qty" required="required" />
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
                            <input type="text" class="form-control decimal" name="CostPrice" id="CostPrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Cost Price" required="required" disabled readonly="readonly" />
                        </div> 

                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sale Price</label>
                            <input type="text" class="form-control decimal" name="SalePrice" id="SalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Sale Price" required="required" />
                        </div>
                                      
                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-info" id="item"  onclick="AddItem(this, event);" value="item">&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
  </div>




    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Customer Purchase Order Item(s)</h3>

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
                        <?php include('_partial_cuspodetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">
    function AddItem(ctrl, event) {
        //event.preventDefault();
        var LocationCode = $('#LocationCode').val();
        var StockCode = $('#StockCode').val();
        var ProductDesc = $('#ProductDesc').val();
        var CostPrice = $('#CostPrice').val()
        var SalePrice = $('#SalePrice').val();
        var Qty = $('#Qty').val();

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
        else if ($('#Qty').val() <= 0) {
            bootbox.alert('Please enter valid item qty.');
            $("#Qty").focus();
        }
        else {
            $('.loader').show();

            $.ajax({
                url: 'create_customerpo.php',
                type: "POST",
                data: { Add: 'Add', LocationCode: LocationCode, StockCode: StockCode, ProductDesc: ProductDesc,CostPrice: CostPrice,SalePrice: SalePrice, Qty: Qty },
                success: function (result) {
                    $("#table").html(result);
                    $('#message').load('_partial_message.php');
                },
                complete: function (result)
                {
                    $('#StockCode').val('');
                    $('#ProductDesc').val('');
                    $('#SalePrice').val('');
                    $('#CostPrice').val('');
                    $('#Qty').val('');

                    $('.loader').fadeOut();
                    $('#StockCode').focus();
                }
            });
        }
    }
  
    $(document).ready(function () {
        $('#StockCode').typeahead({
            hint: true,
            highlight: true,
            minLength: 3,
            source: function (request, response) {
                var LocationCode = $('#LocationCode').val();
                $.ajax({
                    url: "autocomplete.php",
                    data: { stockcode: request, LocationCode: LocationCode},
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
                            var sih = parseFloat(item.sih);
                            map[name] = { id: id, name: name, cprice: cprice, sprice: sprice,sih: sih};
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
                $('#SalePrice').val(map[item].sprice);

                $('#SalePrice').focus();
                return map[item].id;
            }
        });
    });



    function FillSO() {
      $('.loader').show();

      var Customer = $('#CustomerCode').val();

      $.ajax({
          url: "create_customerpo.php",
          type: "POST",
          data: { Customer: Customer },
          success: function (result) {
              $("#SalesOrderCode").html(""); // clear before appending new list
              $("#SalesOrderCode").html(result);
          }
      });


    $.ajax({
        url: "create_customerpo.php",
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
          $('#LocationCode').val('').trigger('change');
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
                $('#LocationCode').val(item.LocationCode).trigger('change');
            });

          }
      });

 
      $.ajax({
          url: "create_customerpo.php",
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
          url: "create_customerpo.php", // Name of the php files
          data: { SalesOrderCode: SalesOrderCode },
          success: function (result) {
              $("#table").html(result);
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

