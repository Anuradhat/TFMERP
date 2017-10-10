<?php
$page_title = 'Add Customer';
require_once('includes/load.php');
page_require_level(2);
?>

<?php
if(isset($_POST['add_customer'])){
    $req_fields = array('CustomerCode','CustomerName','DeliveryAddress2','DeliveryAddress3','ContactPerson', 'Tel');
    validate_fields($req_fields);
    if(empty($errors)){
        $p_CustomerCode  = remove_junk($db->escape($_POST['CustomerCode']));
        $p_CustomerName  = remove_junk($db->escape($_POST['CustomerName']));
        $p_DeliveryAddress1 = remove_junk($db->escape($_POST['DeliveryAddress1']));
        $p_DeliveryAddress2 = remove_junk($db->escape($_POST['DeliveryAddress2']));
        $p_DeliveryAddress3 = remove_junk($db->escape($_POST['DeliveryAddress3']));
        $p_Tel  = remove_junk($db->escape($_POST['Tel']));
        $p_Fax  = remove_junk($db->escape($_POST['Fax']));
        $p_Email  = remove_junk($db->escape($_POST['Email']));
        $p_ContactPerson  = remove_junk($db->escape($_POST['ContactPerson']));
        $p_VATNo  = remove_junk($db->escape($_POST['VATNo']));
        $p_SVATNo = remove_junk($db->escape($_POST['SVATNo']));
        $p_CreditPeriod  = remove_junk($db->escape($_POST['CreditPeriod']));
        $p_SalesPersonCode = remove_junk($db->escape($_POST['SalesPersonCode']));

        $date    = make_date();
        $user = "anush";

        //$query  = "INSERT INTO tfmCustomerM (";
        //$query .=" CustomerCode,CustomerName,DeliveryAddress1,DeliveryAddress2,DeliveryAddress3,Tel,Fax,Email,ContactPerson,VATNo,SVATNo,CreditPeriod,SalesPersonCode";
        //$query .=") VALUES (";
        //$query .=" '{$p_CustomerCode}', '{$p_CustomerName}', '{$p_DeliveryAddress1}', '{$p_DeliveryAddress2}', '{$p_DeliveryAddress3}', '{$p_Tel}', '{$p_Fax}',";
        //$query .=" '{$p_Email}','{$p_ContactPerson}', '{$p_VATNo}', '{$p_SVATNo}',{$p_CreditPeriod},'{$p_SalesPersonCode}'";
        //$query .=");";


        $query  = "call spInsertCustomer('{$p_CustomerCode}','{$p_CustomerName}','{$p_DeliveryAddress1}','{$p_DeliveryAddress2}',
                   '{$p_DeliveryAddress3}','{$p_Tel}','{$p_Fax}','{$p_Email}','{$p_ContactPerson}','{$p_VATNo}','{$p_SVATNo}',
                   {$p_CreditPeriod},'{$p_SalesPersonCode}','{$date}','{$user}');";

        if($db->query($query)){
            $session->msg('s',"Customer added ");
            redirect('add_customer.php', false);
        } else {
            $session->msg('d',' Sorry failed to added!');
            redirect('customer.php', false);
        }

    } else{
        $session->msg("d", $errors);
        redirect('add_customer.php',false);
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
    <form method="post" action="add_customer.php">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Update Customer Details</h3>

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
                            <input type="text" class="form-control" name="CustomerCode" placeholder="Description"  required="required" />
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" name="DeliveryAddress1" placeholder="Street Number" />
                            <input type="text" class="form-control" name="DeliveryAddress2" placeholder="Street Name" required="required" />
                            <input type="text" class="form-control" name="DeliveryAddress3" placeholder="City" required="required" />

                        </div>

                        <div class="form-group">
                            <label>Fax</label>
                            <input type="tel" class="form-control" name="Fax" placeholder="Customer Fax" />
                        </div>

                        <div class="form-group">
                            <label>Contact Person</label>
                            <input type="text" class="form-control" name="ContactPerson" placeholder="Contact Person" required="required" />
                        </div>

                        <div class="form-group">
                            <label>SVAT No</label>
                            <input type="text" class="form-control" name="SVATNo" placeholder="SVAT Number" />
                        </div>

                        <div class="form-group">
                            <label>Sales Person</label>
                            <select class="form-control" name="SalesPersonCode">
                             <option value="">Select Sales Person</option>
                             </select>
                        </div>
                        
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" name="CustomerName" placeholder="Customer Name" required="required" />
                        </div>

                        <div class="form-group">
                            <label>Telephone</label>
                            <input type="tel" class="form-control" name="Tel" placeholder="Customer Telephone" required="required"/>
                        </div>

                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" class="form-control" name="Email" placeholder="Customer Fax" />
                        </div>

                        <div class="form-group">
                            <label>VAT No</label>
                            <input type="text" class="form-control" name="VATNo" placeholder="VAT Number" />
                        </div>

                        <div class="form-group">
                            <label>Credit Period</label>
                            <input type="text" class="form-control" name="CreditPeriod" placeholder="Credit Period (days)" />
                        </div>
                    </div>
     
                </div>
            </div>
        </div>
        <button type="submit" name="add_customer" class="btn btn-success btn-lg">Save  </button>
       </form>

        <div class="form-group"></div>

</section>

<?php include_once('layouts/footer.php'); ?>