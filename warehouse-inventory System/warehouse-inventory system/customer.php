<?php
$page_title = 'Customer Master';
require_once('includes/load.php');
page_require_level(1);

$all_customers = find_by_sql("call spSelectAllCustomers();")
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
        <li class="active">Customer</li>
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
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Customer Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <form method="get" action="add_customer.php">
            <button type="submit" name="add_cus" class="btn btn-primary">Add Customer</button>
        </form>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">

                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Customer Code</th>
                                        <th>Customer Name</th>
                                        <th>Telephone</th>
                                        <th>VAT</th>
                                        <th>SVAT</th>
                                        <th>Credit Period</th>
                                        <th>Sales Person</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_customers as $cus): ?>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <form method="post" action="edit_customer.php" >
                                                        <button type="submit" name="customer" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                        <input type="hidden" name="CustomerCode" value="<?php echo remove_junk($cus['CustomerCode']);?>" />
                                                    </form>
                                                    <form method="post" action="delete_customer.php">
                                                        <button type="submit" name="customer" class="btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                        <input type="hidden" name="CustomerCode" value="<?php echo remove_junk($cus['CustomerCode']);?>" />
                                                    </form>
                                                </div>
                                            </td>
                                            <td><?php echo remove_junk($cus['CustomerCode']); ?></td>
                                            <td><?php echo remove_junk(ucfirst($cus['CustomerName'])); ?></td>
                                            <td><?php echo remove_junk($cus['Tel']); ?></td>
                                            <td><?php echo remove_junk($cus['VATNo']); ?></td>
                                            <td> <?php echo remove_junk($cus['SVATNo']); ?></td>
                                            <td> <?php echo remove_junk($cus['CreditPeriod']); ?></td>
                                            <td><?php echo remove_junk(ucfirst($cus['SalesPersonCode'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </div>
    </div>

</section>

<?php include_once('layouts/footer.php'); ?>