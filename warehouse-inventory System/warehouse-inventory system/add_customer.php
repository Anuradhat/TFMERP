<?php
ob_start();

$page_title = 'Customer Master - New Customer';
require_once('includes/load.php');
page_require_level(2);



if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && !$flashMessages->hasErrors() && !$flashMessages->hasWarnings()) // $session->msg == null
{
    unset($_SESSION['details']);
    unset($_SESSION['header']);
}

$default_salesrepDesig = ReadSystemConfig('DefaultSalesRepDesigCode');
$all_salesrep = find_by_sql("call spSelectEmployeeFromDesignationCode('{$default_salesrepDesig}');");

?>

<?php
if(isset($_POST['add_customer'])){
    $req_fields = array('CustomerName','CustomerAddress2','CustomerAddress3','ContactPerson', 'Tel','SalesmanCode');
    
    validate_fields($req_fields);
    
    if(empty($errors)){

        $p_CustomerName  = remove_junk($db->escape($_POST['CustomerName']));
        $p_NIC  = remove_junk($db->escape($_POST['NIC']));
        $p_CustomerAddress1 = remove_junk($db->escape($_POST['CustomerAddress1']));
        $p_CustomerAddress2 = remove_junk($db->escape($_POST['CustomerAddress2']));
        $p_CustomerAddress3 = remove_junk($db->escape($_POST['CustomerAddress3']));

        $p_Tel  = remove_junk($db->escape($_POST['Tel']));
        $p_Fax  = remove_junk($db->escape($_POST['Fax']));
        $p_Email  = remove_junk($db->escape($_POST['Email']));
        $p_ContactPerson  = remove_junk($db->escape($_POST['ContactPerson']));


        $p_DeliveryAddress1 = remove_junk($db->escape($_POST['DeliveryAddress1']));
        $p_DeliveryAddress2 = remove_junk($db->escape($_POST['DeliveryAddress2']));
        $p_DeliveryAddress3 = remove_junk($db->escape($_POST['DeliveryAddress3']));
        $p_DeliveryTo = remove_junk($db->escape($_POST['DeliveryTo']));
        
        $p_CreditPeriod  = remove_junk(string2Value($db->escape($_POST['CreditPeriod'])));
        $p_VATNo  = remove_junk($db->escape($_POST['VATNo']));
        $p_SVATNo = remove_junk($db->escape($_POST['SVATNo']));
        $p_SalesPersonCode = remove_junk($db->escape($_POST['SalesmanCode']));

        $date    = make_date();
        $user = "anush";


        $p_CustomerCode  = autoGenerateNumber('tfmCustomerM',1);

        try
        {
            $db->begin();

            $cus_count = find_by_sp("call spSelectCustomerFromCode('{$p_CustomerCode}');");

            if($cus_count)
            {
                $flashMessages->warning('This customer code exist in the system.','add_customer.php');
            }

            $query  = "call spInsertCustomer('{$p_CustomerCode}','{$p_CustomerName}','{$p_NIC}','{$p_CustomerAddress1}','{$p_CustomerAddress2}',
                   '{$p_CustomerAddress3}','{$p_DeliveryAddress1}','{$p_DeliveryAddress2}','{$p_DeliveryAddress3}','{$p_DeliveryTo}','{$p_Tel}',
                   '{$p_Fax}','{$p_Email}','{$p_ContactPerson}','{$p_VATNo}','{$p_SVATNo}',{$p_CreditPeriod},'{$p_SalesPersonCode}',
                   '{$date}','{$user}');";

            if($db->query($query)){
                $db->commit();
                $flashMessages->success('Customer code has been saved successfully.','add_customer.php');

            } else {
                $db->rollback();
                $flashMessages->warning('Sorry failed to create customer.','add_customer.php');
            }
        }
        catch(Exception $ex)
        {
            $db->rollback();
            $flashMessages->error('Sorry failed to create customer. '.$ex->getMessage(),'add_customer.php');
        } 
    } 
    else{
        $flashMessages->warning($errors,'add_customer.php');
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Customer Master
        <small>Enter New Customer Details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Cutomer</li>
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
    <form method="post" action="add_customer.php">

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_customer" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'customer.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12"><?php $flashMessages->display(); ?>
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Customer Code</label>
                            <input type="text" class="form-control" name="CustomerCode" placeholder="Code will generate after save" readonly="readonly" disabled="disabled"/>
                        </div>

                        <div class="form-group">
                            <label>NIC</label>
                            <input type="text" class="form-control" name="NIC" placeholder="National Identity Card No" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" name="CustomerName" placeholder="Customer Name" required="required" />
                        </div>

                        <div class="form-group">
                            <label>Customer Address</label>
                            <input type="text" class="form-control" name="CustomerAddress1" placeholder="Street Number" />
                            <input type="text" class="form-control" name="CustomerAddress2" placeholder="Street Name" required="required" />
                            <input type="text" class="form-control" name="CustomerAddress3" placeholder="City" required="required" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Contacts Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Telephone</label>
                            <input type="tel" class="form-control integer" name="Tel" placeholder="Customer Telephone" required="required" />
                        </div>

                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" class="form-control" name="Email" placeholder="Customer Email" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fax</label>
                            <input type="tel" class="form-control integer" name="Fax" placeholder="Customer Fax" />
                        </div>

                        <div class="form-group">
                            <label>Contact Person</label>
                            <input type="text" class="form-control" name="ContactPerson" placeholder="Contact Person" required="required" />
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Delivery Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Delivery Address</label>
                            <input type="text" class="form-control" name="DeliveryAddress1" placeholder="Street Number" />
                            <input type="text" class="form-control" name="DeliveryAddress2" placeholder="Street Name" />
                            <input type="text" class="form-control" name="DeliveryAddress3" placeholder="City" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Delivery To</label>
                            <input type="text" class="form-control" name="DeliveryTo" placeholder="Person Name" />
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Other Details</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Credit Period</label>
                            <input type="text" class="form-control integer" name="CreditPeriod" placeholder="Credit Period (days)" />
                        </div>



                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" id="chkSVATNo" name="chkSVATNo" class="form-check-input" />
                                SVAT No
                            </label>
                            <input type="text" class="form-control" id="SVATNo" name="SVATNo" placeholder="SVAT Number" disabled="disabled" required="required" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>VAT No</label>
                            <input type="text" class="form-control" name="VATNo" placeholder="VAT Number"/>
                        </div>

                        <div class="form-group">
                            <label>Sales Person</label>
                            <select class="form-control select2" style="width: 100%;" name="SalesmanCode" id="SalesmanCode" required="required">
                                <option value="">Select Salesman</option><?php  foreach ($all_salesrep as $srep): ?>
                                <option value="<?php echo $srep['EpfNumber'] ?>"><?php echo $srep['EmployeeName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>

       </form>

</section>

<?php include_once('layouts/footer.php'); ?>


<script>
    $('#chkSVATNo').change(function () {
        $("#SVATNo").prop("disabled", !$(this).is(':checked'));
    });
</script>