<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Update Quotation';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Quotation Update');

$default_flow = ReadSystemConfig('DefaultSOWorkFlow');
$default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');

$current_user = current_user();

//$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_workflows = find_by_sql("call spSelectAllWorkFlow();");
$all_locations = find_by_sql("call spSelectAllLocations();");
$all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");
$Customer = find_by_sql("call spSelectCustomerFromSalesmanCode('{$current_user["EmployeeCode"]}');");


if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$arr_item = array();

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php

if(isset($_POST['edit_salesorder'])){

    if($_POST['edit_salesorder'] == "save")
    {
        $req_fields = array('CustomerCode','SalesOrderCode','WorkFlowCode','SalesmanCode','ValidThru');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_CustomerCode  = remove_junk($db->escape($_POST['CustomerCode']));
            $p_SalesOrderCode  = remove_junk($db->escape($_POST['SalesOrderCode']));
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
                //update quotation order 
                try
                {
                    $So_count = find_by_sp("call spSelectSalesOrderHFromCode('{$p_SalesOrderCode}');");

                    if(!$So_count)
                    {
                        $flashMessages->warning('This quotation number not exist in the system.','edit_salesorder.php');

                    }

                    $db->begin();

                    //Update sales quotation header details
                    $query  = "call spUpdateSalesOrderH('{$p_SalesOrderCode}','{$p_SalesmanCode}','{$p_WorkFlowCode}','{$p_Remarks}',{$p_ValidThru},'{$date}','{$user["username"]}');";
                    $db->query($query);

                    //Delete sales order details
                    $query  = "call spDeleteSalesOrderDFromCode('{$p_SalesOrderCode}');";
                    $db->query($query);

                    //Insert quotation item details
                    foreach($arr_item as $row => $value)
                    {
                        $query  = "call spInsertSalesOrderD('{$p_SalesOrderCode}','{$value[0]}','{$value[1]}',0,{$value[2]},{$value[3]},{$value[4]});";
                        $db->query($query);
                    }

                    InsertRecentActvity("Quotation updated","Reference No. ".$p_SalesOrderCode);

                    $db->commit();
                    
                    $flashMessages->success('Quotation has been successfully updated.','edit_salesorder.php');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to update quotation. '.$ex->getMessage(),'edit_salesorder.php');
                }

            }
            else
            {
                $flashMessages->warning('Quotation item(s) not found!','edit_salesorder.php');

            }
        }
        else
        {
            $flashMessages->warning($errors,'edit_salesorder.php');
        }

    }
}

if (isset($_POST['CustomerChanged'])) {
    $arr_item = array();

    $_SESSION['details'] = null;

    return include('_partial_sodetails.php'); 
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
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $Qty = remove_junk($db->escape($_POST['Qty']));

    
    $arr_item = $_SESSION['details'];
    
    if($ProductCode == "")
    {
        $flashMessages->warning('Product code is not found!');
    }
    else if($SalePrice == "" || $SalePrice < 0)
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
            $arr_item[] = array($ProductCode,$ProductDesc,$SalePrice,$Qty,$Qty * $SalePrice); 
            $_SESSION['details'] = $arr_item;     
        }
    }
    return include('_partial_sodetails.php'); 
}



if (isset($_POST['_RowNo'])) {
    $ProductCode = remove_junk($db->escape($_POST['_RowNo']));
    $serchitem = ArraySearch($arr_item,$ProductCode);

    return include('_partial_poitem.php'); 
}


//if (isset($_POST['CustomerCode'])) {

//    $CustomerCode = remove_junk($db->escape($_POST['CustomerCode']));


//    $default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');
//    $all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");
//    $Customer =    find_by_sp("call spSelectCustomerFromCode('{$CustomerCode}');");

//    echo "<option value=''>Select Salesman</option>";
//    foreach($all_salesrep as &$value){
//        $Selected = $value["EpfNumber"] == $Customer["SalesPersonCode"] ? "selected":"";
//        echo "<option value ={$value["EpfNumber"]}  {$Selected} >{$value["EmployeeName"]}</option>";
//    }
//    return;
//}

if (isset($_POST['SalesmanCodeSelection'])) {
    $SalesmanCode = remove_junk($db->escape($_POST['SalesmanCodeSelection']));
    
    $Customer = null;

    $Customer = find_by_sql("call spSelectCustomerFromSalesmanCode('{$SalesmanCode}');");
    echo "<option value=''>Select Customer</option>";

    foreach($Customer as &$value){
        echo "<option value ={$value["CustomerCode"]}>{$value["CustomerName"]}</option>";
    } 
    return;
}

if (isset($_POST['Customer'])) {
    $_SESSION['details']  = null;

    $CustomerCode = remove_junk($db->escape($_POST['Customer']));

    $all_SO = find_by_sql("call spSelectAllSalesOrderFromCustomerCode('{$CustomerCode}');");
    
    echo "<option value=''>Select Quotation</option>";
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
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["SellingPrice"],$value["Qty"],$value["Amount"]);
    }
    $_SESSION['details'] = $arr_item; 

    return include('_partial_sodetails.php');
}
?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Update Quotation
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
    <form method="post" action="edit_salesorder.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_salesorder" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
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
                            <label>Salesman</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesmanCode" id="SalesmanCode" required="required" onchange="FillCustomer();">
                                <option value="">Select Salesman</option><?php  foreach ($all_salesrep as $srep): ?>
                                <option value="<?php echo $srep['EpfNumber'] ?>" <?php if($srep['EpfNumber'] === $current_user["EmployeeCode"]): echo "selected"; endif; ?>><?php echo $srep['EmployeeName'] ?>
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

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.."></textarea>
                        </div> 

                    </div>

                    <div class="col-md-4">

                        <div class="form-group">
                            <label>Customer</label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required" onchange="FillSO();">
                                <option value="">Select Customer</option><?php  foreach ($Customer as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>"><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Valid Period</label>
                            <input type="text" class="form-control pull-right integer" autocomplete="off" name="ValidThru" id="ValidThru" placeholder="Days" required="required" />
                        </div>
       
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Quotation</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesOrderCode" id="SalesOrderCode" required="required" onchange="FillSODetails();">
                                <option value="">Select Quotation</option>
                            </select>
                        </div>



                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="SoDate" id="SoDate" placeholder="Date" readonly="readonly" disabled="disabled" value="" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <!-- /.box-header -->
        <form method="post" action="edit_salesorder.php">
            <input type="hidden" value="edit_salesorder"name="edit_salesorder" />

            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product Code</label>
                            <input type="text" class="form-control" name="ProductCode" id="ProductCode" placeholder="Product Code" required="required" autocomplete="off" />
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
                            <label>Sale Price</label>
                            <input type="text" class="form-control decimal" name="SalePrice" id="SalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Sale Price" required="required" disabled />
                        </div>
                    </div>

                    <div class="col-md-3">                   
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control integer" name="pQty" id="Qty" placeholder="Qty" required="required" />
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
        //event.preventDefault();
        var ProductCode = $('#ProductCode').val();
        var ProductDesc = $('#ProductDesc').val();
        var CostPrice = $('#CostPrice').val()
        var SalePrice = $('#SalePrice').val();
        var Qty = $('#Qty').val();


        if ($('#ProductCode').val() == "") {
            $("#ProductCode").focus();
            bootbox.alert('Please select correct product.');
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
                url: 'edit_salesorder.php',
                type: "POST",
                data: { Add: 'Add', ProductCode: ProductCode, ProductDesc: ProductDesc, SalePrice: SalePrice, Qty: Qty },
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
                            var sih = parseFloat(item.sih);
                            map[name] = { id: id, name: name, cprice: cprice, sprice: sprice,sih: sih};
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

