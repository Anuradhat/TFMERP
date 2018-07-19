<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Update Customer Purchase Order';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Customer PO Update');

$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_workflows = find_by_sql("call spSelectAllWorkFlow();");



$CustomerPO  = $_SESSION['CustomerPO'];

$CusPurchaseOrderH = find_by_sp("call spSelectCustomerPurchaseOrderHFromCode('{$CustomerPO}');");
$Customer = find_by_sql("call spSelectCustomerFromCode('{$CusPurchaseOrderH['CustomerCode']}');");



if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$arr_item = array();

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php

if(isset($_POST['edit_customerpo_'])){

    if($_POST['edit_customerpo_'] == "save")
    {
        $req_fields = array();

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_CustomerPoCode  = remove_junk($db->escape($_SESSION['CustomerPO']));

            $date    = make_date();
            $user =  current_user();

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {
                //Create customer purchase order 
                try
                {

                    $CusPo_count = find_by_sp("call spSelectCustomerPurchaseOrderHFromCode('{$p_CustomerPoCode}');");

                    if(!$CusPo_count)
                    {
                        $flashMessages->warning('This customer purchase order not exist in the system.','edit_customerpo_.php?TransactionCode=005');
                    }

                    $db->begin();

                                

                    //Update customer purchase order details
                    foreach($arr_item as $row => $value)
                    {
                        $query  = "call spUpdateCustomerPOFromCode('{$p_CustomerPoCode}','{$value[0]}',{$value[2]},{$value[3]},{$value[4]},{$value[5]},'{$date}','{$user["username"]}');";
                        $db->query($query);
                    }

                    InsertRecentActvity("Customer purchase order updated","Reference No. ".$p_CustomerPoCode); 

                    $db->commit();
                    
                    $flashMessages->success('Customer purchase order has been successfully updated.','approval_task.php');
                    

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to update customer purchase order. '.$ex->getMessage(),'edit_customerpo_.php');

                }

            }
            else
            {
                $flashMessages->warning('Customer purchase order item(s) not found!','edit_customerpo_.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'edit_customerpo_.php');
        }

    }
}

if (isset($_POST['_productcode'])) {
    $productcode = remove_junk($db->escape($_POST['_productcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$productcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_cuspodetails.php');  
}




if (isset($_POST['Add'])) {
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $ProductDesc = remove_junk($db->escape($_POST['ProductDesc']));
    $CostPrice = remove_junk($db->escape($_POST['CostPrice']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));
    $Qty = remove_junk($db->escape($_POST['Qty']));

    
    $arr_item = $_SESSION['details'];
    
    if($ProductCode == "")
    {
        $flashMessages->warning('Product code is not found!');
    }
    else if($SalePrice == "" || $SalePrice <=0)
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



            $arr_item[] = array($ProductCode,$ProductDesc,$SalePrice,$Qty,$ToatlAmount,$TaxAmount); 
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

if (isset($_POST['Customer'])) {
    $_SESSION['details']  = null;

    $CustomerCode = remove_junk($db->escape($_POST['Customer']));

    $all_CPO = find_by_sql("call spSelectCustomerPurchaseOrderFromCustomerCode('{$CustomerCode}');");
    
    echo "<option value=''>Select Customer PO</option>";
    foreach($all_CPO as &$value){
        echo "<option value ={$value["CusPoNo"]}>{$value["CusPoNo"]}</option>";
    }

    return;
}


if (isset($_SESSION['redirect'])) {
    $CPO_Details = find_by_sql("call spSelectCustomerPurchaseOrderDFromCode('{$CustomerPO}');");

    foreach($CPO_Details as &$value){
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["SellingPrice"],$value["Qty"],$value["Amount"],$value["TaxAmount"]);
    }

    $_SESSION['details'] = $arr_item;   
    unset($_SESSION['redirect']);
}


if (isset($_POST['FillTable']) &&  isset($_POST['CustomerPoCode'])) {
    $_SESSION['details']  = null;

    $CustomerPoCode = remove_junk($db->escape($_POST['CustomerPoCode']));

    $CPO_Details = find_by_sql("call spSelectCustomerPurchaseOrderDFromCode('{$CustomerPoCode}');");
    
    foreach($CPO_Details as &$value){
        $arr_item[]  = array($value["ProductCode"],$value["ProductDesc"],$value["SellingPrice"],$value["Qty"],$value["Amount"],$value["TaxAmount"]);
    }
    $_SESSION['details'] = $arr_item; 

    return include('_partial_cuspodetails.php');
}






if (isset($_POST['_RowNo'])) {
    $ProductCode = remove_junk($db->escape($_POST['_RowNo']));
    $serchitem = ArraySearch($arr_item,$ProductCode);

    return include('_partial_cuspoitem.php'); 
}

//if (isset($_POST['Edit'])) {
//    $StockCode = remove_junk($db->escape($_POST['StockCode']));
//    $Qty = remove_junk($db->escape($_POST['Qty']));

//    $arr_item = $_SESSION['details'];

//    //Change Qty
//    $arr_item = ChangValueFromListOfArray( $arr_item,$StockCode,4,$Qty);

//    $_SESSION['details'] = $arr_item;

//    return include('_partial_cuspodetails.php');  
//}
?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Update Customer Purchase Order
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
    <form method="post" action="edit_customerpo_.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="edit_customerpo_" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
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
                            <label>Customer <span class="text-danger">(Credit Amount:&nbsp;<output class="inline" for="fader" id="creditamount">0</output>)</span></label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required" onchange="FillCPO();" disabled>
                                <option value="">Select Customer</option><?php  foreach ($Customer as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>"<?php if($cus['CustomerCode'] === $CusPurchaseOrderH['CustomerCode']): echo "selected"; endif; ?>><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Customer PO No (Reference No)</label>
                            <input type="text" class="form-control" name="ReferencePoNo" id="ReferencePoNo" placeholder="Customer PO"   value="<?php echo $CusPurchaseOrderH['ReferenceNo'] ?>"  disabled="disabled"/>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Customrt Purchase Order</label>
                            <select class="form-control select2" style="width: 100%;" name="CustomerPoCode" id="CustomerPoCode" required="required" onchange="FillCPODetails();" disabled>
                                <option value="<?php echo $CusPurchaseOrderH['CusPoNo']; ?>" selected><?php echo $CusPurchaseOrderH['CusPoNo']; ?></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Approvals Flow</label>
                            <select class="form-control select2" style="width: 100%;" name="WorkFlowCode" id="WorkFlowCode" required="required" disabled>
                                <option value="">Select Approvals Work-Flow</option><?php  foreach ($all_workflows as $wflow): ?>
                                <option value="<?php echo $wflow['WorkFlowCode'] ?>" <?php if($wflow['WorkFlowCode'] === $CusPurchaseOrderH['WorkFlowCode']): echo "selected"; endif; ?>><?php echo $wflow['Description'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" class="form-control" name="CusPoDate" id="CusPoDate" placeholder="Date" readonly="readonly"  value="<?php echo $CusPurchaseOrderH['CusPoDate'] ?>"   disabled="disabled" />
                            </div>

                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="Remarks" id="Remarks" class="form-control" placeholder="Enter remarks here.." disabled="disabled" value="<?php echo $CusPurchaseOrderH['Remarks'] ?>"></textarea>
                            </div>
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
                            <label>Product Code</label>
                            <input type="text" class="form-control" name="ProductCode" id="ProductCode" placeholder="Product Code" required="required" autocomplete="off" disabled="disabled"/>
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
                            <input type="text" class="form-control decimal" name="SalePrice" id="SalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Sale Price" required="required" disabled="disabled"/>
                        </div> 

                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control integer" name="pQty" id="Qty" placeholder="Qty" required="required" disabled="disabled"/>
                        </div>
                     
                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-info" id="item"  onclick="AddItem(this, event);" value="item" disabled>&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success" disabled>&nbsp;Reset&nbsp;</button>
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
        var ProductCode = $('#ProductCode').val();
        var ProductDesc = $('#ProductDesc').val();
        var CostPrice = $('#CostPrice').val()
        var SalePrice = $('#SalePrice').val();
        var Qty = $('#Qty').val();

  
        if ($('#ProductCode').val() == "") {
            $("#ProductCode").focus();
            bootbox.alert('Please select correct product code.');
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
                url: 'edit_customerpo.php',
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
                    data: { productcode: request },
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
                //$('#CostPrice').val(map[item].cprice);
                $('#SalePrice').val(map[item].sprice);

                $('#SalePrice').focus();
                return map[item].id;
            }
        });
    });



    function FillCPO() {
      $('.loader').show();
      var Customer = $('#CustomerCode').val();

      var CustomerCode = "";
      var CustomerName = "";
      var Credit = 0;


      $.ajax({
          url: 'autocomplete.php',
          type: 'POST',
          data: { Customer: Customer },
          dataType: 'json',
          success: function (data) {
              jQuery(data).each(function (i, item) {
                  CustomerCode = item.CustomerCode;
                  CustomerName = item.CustomerName;
                  Credit = item.Credit;
              });
          },
          complete: function (data) {
              if (CustomerCode == "") {
                  document.querySelector('#creditamount').value = 0.00;
              }
              else {
                  document.querySelector('#creditamount').value = Credit;
              }
          }
      });




      $.ajax({
          url: "edit_customerpo.php",
          type: "POST",
          data: { Customer: Customer },
          success: function (result) {
              $("#CustomerPoCode").html(""); // clear before appending new list
              $("#CustomerPoCode").html(result);         
          }
      });


    $.ajax({
        url: "edit_customerpo.php",
        type: "POST",
        data: { CustomerChanged: 'OK'},
        success: function (result) {
            $("#table").html(result);
            $('#message').load('_partial_message.php');
            $('.loader').fadeOut();
        }
    });
  }


    function FillCPODetails() {
        $('.loader').show();

        var CustomerPoCode = $('#CustomerPoCode').val();

        if (CustomerPoCode == "")
      {
            $('#ReferencePoNo').val('');
            $('#WorkFlowCode').val('').trigger('change');
            $('#CusPoDate').val('');
            $('#Remarks').val('');
      }

    //Fill header details
      $.ajax({
          url: "autocomplete.php",
          type: "POST",
          data: { CustomerPoCode: CustomerPoCode },
          dataType: 'json',
          success: function (data) {
              //Fill header details
              jQuery(data).each(function (i, item) {
                  $('#ReferencePoNo').val(item.ReferenceNo).trigger('change');
                  $('#WorkFlowCode').val(item.WorkFlowCode).trigger('change');
                  $('#CusPoDate').val(item.CusPoDate).trigger('change');
                  $('#Remarks').val(item.Remarks).trigger('change');
            });

          }
      });

 
      $.ajax({
          url: "edit_customerpo.php",
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
          url: "edit_customerpo.php", // Name of the php files
          data: { FillTable: 'OK',CustomerPoCode: CustomerPoCode },
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

