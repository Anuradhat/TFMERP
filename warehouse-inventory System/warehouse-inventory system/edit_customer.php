<?php
$page_title = 'Customer Master - Edit Customer';
require_once('includes/load.php');
page_require_level(2);
?>

<?php
if(isset($_POST['customer'])){
   $p_cuscode = remove_junk($db->escape($_POST['CustomerCode']));

   if(!$p_cuscode){
        $session->msg("d","Missing customer identification.");
        redirect('customer.php');
    }
   else
   {
       $customer = find_by_sp("call spSelectCustomerFromCode('{$p_cuscode}');");

       if(!$customer){
        $session->msg("d","Missing customer details.");
        redirect('customer.php');
       }
   }
}
?>

<?php
if(isset($_POST['edit_customer'])){
    $req_fields = array('hCustomerCode','CustomerName','NIC','CustomerAddress2','CustomerAddress3','ContactPerson', 'Tel');
    
    validate_fields($req_fields);
    
    if(empty($errors)){
        $p_CustomerCode  = remove_junk($db->escape($_POST['hCustomerCode']));
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
        
        $p_CreditPeriod  = remove_junk($db->escape($_POST['CreditPeriod']));
        $p_VATNo  = remove_junk($db->escape($_POST['VATNo']));
        $p_SVATNo = remove_junk($db->escape($_POST['SVATNo']));
        $p_SalesPersonCode = remove_junk($db->escape($_POST['SalesPersonCode']));

        $date    = make_date();
        $user = "anush";

        $query  = "call spUpdateCustomer('{$p_CustomerCode}','{$p_CustomerName}','{$p_NIC}','{$p_CustomerAddress1}','{$p_CustomerAddress2}',
                   '{$p_CustomerAddress3}','{$p_DeliveryAddress1}','{$p_DeliveryAddress2}','{$p_DeliveryAddress3}','{$p_DeliveryTo}','{$p_Tel}',
                   '{$p_Fax}','{$p_Email}','{$p_ContactPerson}','{$p_VATNo}','{$p_SVATNo}',{$p_CreditPeriod},'{$p_SalesPersonCode}',
                   '{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Customer updated");
            redirect('customer.php', false);
        } else {
            $session->msg('d',' Sorry failed to updated!');
            //redirect('customer.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('edit_customer.php',false);
    }
}

?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Customer Master
        <small>Optional description</small>
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
    <form method="post" action="edit_customer.php">
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
                            <input type="text" class="form-control" name="CustomerCode" placeholder="Customer Code" required="required" value="<?php echo remove_junk($customer['CustomerCode']);?>" readonly="readonly" disabled="disabled"/>
                             <input type="hidden" name="hCustomerCode" value="<?php echo remove_junk($customer['CustomerCode']);?>" />
                         </div>

                        <div class="form-group">
                            <label>NIC</label>
                            <input type="text" class="form-control" name="NIC" placeholder="National Identity Card No" value="<?php echo remove_junk($customer['Nic']); ?>"/>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" name="CustomerName" placeholder="Customer Name" required="required" value="<?php echo remove_junk($customer['CustomerName']); ?>" />
                        </div>

                        <div class="form-group">
                            <label>Customer Address</label>
                            <input type="text" class="form-control" name="CustomerAddress1" placeholder="Street Number" value="<?php echo remove_junk($customer['CustomerAddress1']); ?>" />
                            <input type="text" class="form-control" name="CustomerAddress2" placeholder="Street Name" required="required" value="<?php echo remove_junk($customer['CustomerAddress2']); ?>" />
                            <input type="text" class="form-control" name="CustomerAddress3" placeholder="City" required="required" value="<?php echo remove_junk($customer['CustomerAddress3']); ?>" />
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
                            <input type="tel" class="form-control" name="Tel" placeholder="Customer Telephone" required="required" value="<?php echo remove_junk($customer['Tel']); ?>"/>
                        </div>

                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" class="form-control" name="Email" placeholder="Customer Email" value="<?php echo remove_junk($customer['Email']); ?>"/>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fax</label>
                            <input type="tel" class="form-control" name="Fax" placeholder="Customer Fax" value="<?php echo remove_junk($customer['Fax']); ?>"/>
                        </div>

                        <div class="form-group">
                            <label>Contact Person</label>
                            <input type="text" class="form-control" name="ContactPerson" placeholder="Contact Person" required="required" value="<?php echo remove_junk($customer['ContactPerson']); ?>"/>
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
                            <input type="text" class="form-control" name="DeliveryAddress1" placeholder="Street Number" value="<?php echo remove_junk($customer['DeliveryAddress1']); ?>"/>
                            <input type="text" class="form-control" name="DeliveryAddress2" placeholder="Street Name" value="<?php echo remove_junk($customer['DeliveryAddress2']); ?>"/>
                            <input type="text" class="form-control" name="DeliveryAddress3" placeholder="City" value="<?php echo remove_junk($customer['DeliveryAddress3']); ?>"/>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Delivery To</label>
                            <input type="text" class="form-control" name="DeliveryTo" placeholder="Person Name" value="<?php echo remove_junk($customer['DeliveryTo']); ?>"/>
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
                            <input type="number" class="form-control" name="CreditPeriod" placeholder="Credit Period (days)" value="<?php echo remove_junk($customer['CreditPeriod']); ?>"/>
                        </div>

                        <div class="form-group">
                            <label>SVAT No</label>
                            <input type="text" class="form-control" name="SVATNo" placeholder="SVAT Number" value="<?php echo remove_junk($customer['SVATNo']); ?>"/>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>VAT No</label>
                            <input type="text" class="form-control" name="VATNo" placeholder="VAT Number" value="<?php echo remove_junk($customer['VATNo']); ?>"/>
                        </div>

                        <div class="form-group">
                            <label>Sales Person</label>
                            <select class="form-control" name="SalesPersonCode">
                                <option value="">Select Sales Person</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <button type="submit" name="edit_customer" class="btn btn-success btn-lg">Save  </button>
       </form>

        <div class="form-group"></div>

</section>

<?php include_once('layouts/footer.php'); ?>