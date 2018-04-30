<?php
ob_start();

$page_title = 'Supplier Master';
require_once('includes/load.php');

UserPageAccessControle(1,'Supplier');

$all_suppliers = find_by_sql("call spSelectAllSuppliers();");
?>

<?php include_once('layouts/header.php');?>
<section class="content-header">
    <h1>
        Supplier Master
        <small>Create and edit supplier details</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Supplier</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<section class="content">

    <!-- Your Page Content Here -->
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="btn-group">
                        <button type="button" name="add_supplier" onclick="window.location = 'add_supplier.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
                        <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php echo display_msg($msg); ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Supplier Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>

        <div class="box-body">
            <div id="1stRow" class="row form-group">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="box">
                        <div class="box-body">
                            <table class="table table-condensed table-hover table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Tel</th>
                                        <th>Fax</th>
                                        <th>Email</th>
                                        <th>VAT No</th>
                                        <th>Credit Period</th>
                                        <th>Currency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_suppliers as $sup): ?>
                                    <tr>
                                        <td>
                                            <form method="post" action="edit_supplier.php">
                                                <button type="submit" name="supplier" class="btn  btn-warning btn-xs glyphicon glyphicon-edit" title="Edit Supplier"></button>
                                                <input type="hidden" name="SupplierCode" value="<?php echo remove_junk($sup['SupplierCode']);?>" />
                                            </form>
                                            <form method="post" action="delete_supplier.php">
                                                <button type="submit" name="supplier" class="btn btn-danger btn-xs glyphicon glyphicon-trash" title="Delete Supplier"></button>
                                                <input type="hidden" name="SupplierCode" value="<?php echo remove_junk($sup['SupplierCode']);?>" />
                                            </form>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['SupplierCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['SupplierName']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['SupplierTel']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['SupplierFax']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['SupplierEmail']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['SupplierVatNo']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['SupplierCreditPeriod']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($sup['CurrencyDescription']); ?>
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
    </div>
</section>
<?php include_once('layouts/footer.php');?>


