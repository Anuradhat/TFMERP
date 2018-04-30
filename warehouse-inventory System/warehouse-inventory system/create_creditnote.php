<?php
ob_start();

session_set_cookie_params(0);
session_start();

$page_title = 'Create Credit Note';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
UserPageAccessControle(1,'Credit Note Create');

$default_flow = ReadSystemConfig('DefaultCUSPOWorkFlow');


$all_Customers = find_by_sql("call spSelectAllCustomers();");
$all_workflows = find_by_sql("call spSelectAllWorkFlow();");


if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings())
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$arr_item = array();

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
?>

<?php

if(isset($_POST['create_creditnote'])){

    if($_POST['create_creditnote'] == "save")
    {
        $req_fields = array('hInvoiceNo');

        validate_fields($req_fields);

        if(empty($errors))
        {
            $p_InvoiceNo  = remove_junk($db->escape($_POST['hInvoiceNo']));
            $date    = make_date();
            $user =  current_user();

            //Get all sessions values
            $arr_item= $_SESSION['details'];

            //check details values
            if(count($arr_item)>0)
            {
                //Create credit note 
                try
                {
                    $p_CreditNoteNo  = autoGenerateNumber('tfmCreditNoteHT',1);

                    $p_CreditNoteCount = find_by_sp("call spSelectCreditNoteHFromCode('{$p_CreditNoteNo}');");

                    if($p_CreditNoteCount)
                    {
                        $flashMessages->warning('This credit note exist in the system.','create_creditnote.php');
                    }


                    //******* Check serial ***************************************
                    foreach($arr_item as $row => $value)
                    {
                        if (!CheckSerialAbalableToReturn($p_InvoiceNo,$value[0]))
                        {
                            $flashMessages->warning('This serial not available to return.Refrence: '.$value[0],'create_creditnote.php');
                            break;
                        }
                    }

                    $TotalAmount = 0;
                    foreach($arr_item  as &$value)
                    { 
                        $TotalAmount += $value[3] * $value[4];
                    }

                    $InvoiceHed = find_by_sql("call spSelectInvoiceHFromCode('{$p_InvoiceNo}');");

                    $db->begin();

                    //Insert credit note header details
                    $query  = "call spInsertCreditNoteH('{$p_CreditNoteNo}','{$InvoiceHed['spSelectInvoiceHFromCode']}','{$p_InvoiceNo}',{$TotalAmount},'{$date}','{$p_Remarks}','{$date}','{$user["username"]}');";
                    $db->query($query);


                    //Insert credit note details
                    foreach($arr_item as $row => $value)
                    {
                        $query  = "call spInsertCreditNoteD('{$p_CreditNoteNo}','{$value[0]}','{$value[1]}','{$value[2]}',{$value[3]},{$value[4]},{$value[5]});";
                        $db->query($query);


                        //Update Invoice reverse qty
                        $query  = "call spUpdateInvoiceReverseQty('{$p_InvoiceNo}','{$value[1]}',1);";
                        $db->query($query);

                        $SerialDetails = find_by_sp("call spSelectGRNSerialDetailsFromSerialCode('{$value[0]}');");
                        $StockDetails = find_by_sp("call spSelectStock('{$SerialDetails['StockCode']}','{$SerialDetails['LocationCode']}','{$SerialDetails['BinCode']}');");

                        //Update Stock
                        $query  = "call spUpdateStock('{$SerialDetails['StockCode']}','{$SerialDetails['LocationCode']}','{$SerialDetails['BinCode']}',1,'{$date}');";
                        $db->query($query);

                        //Update as sales return flag in GRNSerial
                        $query  = "call spUpdateSaleReturnFlagGRNSerialFromSerialCode('{$value[0]}');";
                        $db->query($query);

                        //Update as return flag in InvoiceSerial
                        $query  = "call spUpdateSalesReturnFlagFromCode('{$value[0]}');";
                        $db->query($query);

                        //Insert stock movement
                        $query  = "call spStockMovement('{$SerialDetails['StockCode']}','{$SerialDetails['LocationCode']}','{$SerialDetails['BinCode']}',
                                       '{$StockDetails['ProductCode']}','{$value[0]}','{$p_CreditNoteNo}','{$StockDetails['SupplierCode']}','008',0,{$value[3]},0,0,0,-1,'{$StockDetails['ExpireDate']}','{$date}','{$user["username"]}');";
                        $db->query($query);


                    }
                    

                    $db->commit();
                    
                    $flashMessages->success('Credit note has been saved successfully,\n   Your credit note No: '.$p_CreditNoteNo,'create_creditnote.php');

                }
                catch(Exception $ex)
                {
                    $db->rollback();

                    $flashMessages->error('Sorry failed to create credit note. '.$ex->getMessage(),'create_creditnote.php');
                }

            }
            else
            {
                $flashMessages->warning('Credit note item(s) not found!','create_creditnote.php');
            }
        }
        else
        {
            $flashMessages->warning($errors,'create_creditnote.php');
        }

    }
}

if (isset($_POST['_productcode'])) {
    $productcode = remove_junk($db->escape($_POST['_productcode']));
    $arr_item = $_SESSION['details'];
    $arr_item = RemoveValueFromListOfArray( $arr_item,$productcode);
    $_SESSION['details'] = $arr_item;

    return include('_partial_creditnotedetails.php');  
}



if (isset($_POST['Add'])) {
    $SerialCode = remove_junk($db->escape($_POST['SerialCode']));
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));
    $Description = remove_junk($db->escape($_POST['Description']));
    $SalePrice = remove_junk($db->escape($_POST['SalePrice']));


    $arr_item = $_SESSION['details'];

    if($SerialCode == "" || $ProductCode == "")
    {
        $flashMessages->warning('Product code is not found!');
    }
    else if($SalePrice == "" || $SalePrice <= 0)
    {
        $flashMessages->warning('Invalid sales price.');
    }
    else
    {
         if(!ExistInArray($arr_item,$SerialCode))
         {
             $arr_item[] = array($SerialCode,$ProductCode,$Description,$SalePrice,1,$SalePrice);
             $_SESSION['details'] = $arr_item;
         }
         else
         {
             $flashMessages->warning('This item exist in the list.');
         }
    }

    return include('_partial_creditnotedetails.php');
}


if (isset($_POST['InvoiceNo']) && isset($_POST['ProductCode'])) {

    $InvoiceNo = remove_junk($db->escape($_POST['InvoiceNo']));
    $ProductCode = remove_junk($db->escape($_POST['ProductCode']));

    $SerialDet = find_by_sql("call spSelectSerialNoFromProductCode('{$InvoiceNo}','{$ProductCode}');");

    echo "<option value=''>Select Serial</option>";
    foreach($SerialDet as &$value){
        echo "<option value ={$value["SerialNo"]}>{$value["SerialNo"]}</option>";
    }
    return;
}


if (isset($_POST['InvoiceNo'])) {

    $InvoiceNo = remove_junk($db->escape($_POST['InvoiceNo']));
    $InvoiceDet = find_by_sql("call spSelectInvoiceDFromCode('{$InvoiceNo}');");

    echo "<option value=''>Select Invoiced Product</option>";
    foreach($InvoiceDet as &$value){
        echo "<option value ={$value["ProductCode"]}>{$value["Description"]}</option>";
    }
    return;
}


?>

<?php include_once('layouts/header.php'); ?>

<section class="content-header">
    <h1>
        Create Credit Note
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Transaction
            </a>
        </li>
        <li class="active">Credit Note</li>
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
    <form method="post" action="create_creditnote.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="create_creditnote" class="btn btn-primary" value="save">&nbsp;Save&nbsp;&nbsp;</button>
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
                                <label>Invoice No</label>
                                <input type="text" class="form-control" name="InvoiceNo" id="InvoiceNo" placeholder="Invoice Number" autocomplete="off" required/>
                                <input type="hidden" name="hInvoiceNo" id="hInvoiceNo" />
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
                            <select class="form-control select2" style="width: 100%;" name="CustomerCode" id="CustomerCode" required="required" disabled>
                                <option value="">Select Customer</option><?php  foreach ($all_Customers as $cus): ?>
                                <option value="<?php echo $cus['CustomerCode'] ?>"><?php echo $cus['CustomerName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label>Invoice Date</label>
                                <input type="text" class="form-control" name="InvDate" id="InvDate" placeholder="Invoice Date" readonly="readonly" disabled="disabled" value="<?php echo make_date(); ?>" />
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </form>


    <div class="box box-default">
        <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <!--<div class="form-group">
                            <label>Serial Code</label>
                            <input type="text" class="form-control" name="SerialCode" id="SerialCode" placeholder="Serial Code" autocomplete="off" />
                            <input type="hidden" name="StockCode" id="StockCode" />
                        </div>-->

                        <div class="form-group">
                            <label>Invoice Product</label>
                            <select class="form-control select2" style="width: 100%;" name="ProductCode" id="ProductCode" required="required" onchange="FillSerial()">
                                <option value="">Select Invoiced Product</option>
                            </select>
                        </div>

                    </div>

                    <div class="col-md-3">
 
                        <div class="form-group">
                            <label>Serial Code</label>
                            <select class="form-control select2" style="width: 100%;" name="SerialCode" id="SerialCode" required="required">
                                <option value="">Select Serial</option>
                            </select>
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="form-group pull-right">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-info" name="create_po" onclick="AddItem(this, event);" value="item">&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;</button>
                        </div>
                    </div>
                </div>
            </div>
  </div>




    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Credit Note Item(s)</h3>

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
                        <?php include('_partial_creditnotedetails.php'); ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>


</section>

<script type="text/javascript">

    function AddItem(ctrl, event)
    {
        event.preventDefault();
        $('.loader').show();

        var InvoiceNo = $("#hInvoiceNo").val();
        var SerialCode = $('#SerialCode').val();

        if (InvoiceNo == "") {
            bootbox.alert('Please enter the invoice number.');
            $('.loader').fadeOut();
        }
        else if (SerialCode == "") {
            bootbox.alert('Please enter serial code.');
            $('.loader').fadeOut();
        }
        else {
            var ProductCode = "";
            var Description = "";
            var SalePrice = 0.00;

            $.ajax({
                url: 'autocomplete.php',
                type: 'POST',
                data: { CreditNoteInvoiceNo: InvoiceNo, SerialCode: SerialCode },
                dataType: 'json',
                success: function (data) {
                    jQuery(data).each(function (i, item) {
                        SerialNo = item.SerialNo;
                        ProductCode = item.ProductCode;
                        Description = item.Description;
                        SalePrice = item.SalePrice;
                    });
                },
                complete: function (data) {

                    if (ProductCode == "") {
                        bootbox.alert('Invalid serial code.');

                        $('#SerialCode').val('');
                        $('#SerialCode').focus();

                        $('.loader').fadeOut();
                    }
                    else {
                        $('.loader').show();

                        $.ajax({
                            url: 'create_creditnote.php',
                            type: "POST",
                            data: { Add: 'Add', SerialCode: SerialCode, ProductCode: ProductCode, Description: Description, SalePrice: SalePrice },
                            success: function (result) {
                                $("#table").html(result);
                                $('#message').load('_partial_message.php');
                            },
                            complete: function (result) {
                                $('#SerialCode').select2().val('').trigger('change.select2');

                                $('.loader').fadeOut();
                                $('#SerialCode').focus();
                            }
                        });

                    }
                }
            });

        }
    }



    $(document).ready(function () {
        $('#InvoiceNo').typeahead({
            hint: true,
            highlight: true,
            minLength: 2,
            source: function (request, response) {
                $('.loader').show();
                $.ajax({
                    url: "autocomplete.php",
                    data: 'InvoiceNo=' + request,
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
                        items = [];
                        map = {};
                        $.each(data, function (i, item) {
                            var id = item.value;
                            var name = item.text;
                            var LocationCode = item.LocationCode;
                            var InvDate = item.InvDate;
                            var CustomerCode = item.CustomerCode;

                            map[name] = {id: id, name: name, LocationCode: LocationCode, InvDate: InvDate,CustomerCode: CustomerCode};
                            items.push(name);
                        });
                        response(items);
                        $(".dropdown-menu").css("height", "auto");

                        $('.loader').fadeOut();
                    }
                });
            },
            updater: function (item) {
              
                var InvoiceNo = map[item].id;

                $('.loader').show();

                $.ajax({
                    url: "create_creditnote.php",
                    type: "POST",
                    data: { InvoiceNo: InvoiceNo },
                    success: function (result) {
                        $("#ProductCode").html(""); // clear before appending new list
                        $("#ProductCode").html(result);
                        $('.loader').fadeOut();
                    }
                });


                $("#hInvoiceNo").val(map[item].id);
                $("#InvDate").val(map[item].InvDate);
                $("#CustomerCode").select2().val(map[item].CustomerCode).trigger('change.select2');

                return map[item].id;
            }
        });
    });


  //$("#SerialCode").on('keyup', function (e) {
        

  //      var InvoiceNo = $("#hInvoiceNo").val();
  //      var SerialCode = $('#SerialCode').val();
       
  //      if (e.keyCode == 13) {
  //           $('.loader').show();

  //          if (InvoiceNo == "") {
  //              bootbox.alert('Please enter the invoice number.');
  //              $('.loader').fadeOut();
  //          }
  //          else if (SerialCode == "") {
  //              bootbox.alert('Please enter serial code.');
  //              $('.loader').fadeOut();
  //          }
  //          else {
  //              var ProductCode = "";
  //              var Description = "";
  //              var SalePrice = 0.00;

  //              $.ajax({
  //                  url: 'autocomplete.php',
  //                  type: 'POST',
  //                  data: { CreditNoteInvoiceNo: InvoiceNo, SerialCode: SerialCode },
  //                  dataType: 'json',
  //                  success: function (data) {
  //                      jQuery(data).each(function (i, item) {
  //                         SerialNo = item.SerialNo;
  //                         ProductCode = item.ProductCode;
  //                         Description = item.Description;
  //                         SalePrice = item.SalePrice;
  //                      });
  //                  },
  //                  complete: function (data) {
                      
  //                      if (ProductCode == "") {
  //                          bootbox.alert('Invalid serial code.');

  //                          $('#SerialCode').val('');
  //                          $('#SerialCode').focus();

  //                          $('.loader').fadeOut();
  //                      }
  //                      else {
  //                          $('.loader').show();

  //                          $.ajax({
  //                              url: 'create_creditnote.php',
  //                              type: "POST",
  //                              data: { Add: 'Add', SerialCode: SerialCode, ProductCode: ProductCode, Description: Description, SalePrice: SalePrice },
  //                              success: function (result) {
  //                                  $("#table").html(result);
  //                                  $('#message').load('_partial_message.php');
  //                              },
  //                              complete: function (result) {
  //                                  $('#SerialCode').val('');

  //                                  $('.loader').fadeOut();
  //                                  $('#SerialCode').focus();
  //                              }
  //                          });

  //                      }
  //                  }
  //              });

  //          }
  //      }
  //  });


  function FillSerial()
  {
      var ProductCode = $("#ProductCode").val();
      var InvoiceNo = $("#hInvoiceNo").val();
      $('.loader').show();

      $.ajax({
          url: "create_creditnote.php",
          type: "POST",
          data: { InvoiceNo: InvoiceNo, ProductCode: ProductCode },
          success: function (result) {
              $("#SerialCode").html(""); // clear before appending new list
              $("#SerialCode").html(result);
              $('.loader').fadeOut();
          }
      });


  }
</script>

<?php include_once('layouts/footer.php'); ?>

