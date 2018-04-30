<?php
ob_start();

$page_title = 'Tax Master';
require_once('includes/load.php');
UserPageAccessControle(1,'Tax');

$all_taxrates = find_by_sql("call spSelectAllTaxRates();")
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Tax Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Tax</li>
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
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="btn-group">
                        <button type="button" name="add_tax" onclick="window.location = 'add_tax.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
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
            <h3 class="box-title">Tax Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>

        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">

                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="table" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Tax Code</th>
                                        <th>Tax Description</th>
                                        <th>Tax Rate (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_taxrates as $tax): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_tax.php">
                                                    <button type="submit" name="taxrate" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="TaxCode" value="<?php echo remove_junk($tax['TaxCode']);?>" />
                                                </form>
                                                <form method="post" action="delete_tax.php">
                                                    <button type="submit" name="taxrate" class="btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                    <input type="hidden" name="TaxCode" value="<?php echo remove_junk($tax['TaxCode']);?>" />
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($tax['TaxCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($tax['TaxDesc'])); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($tax['TaxRate'])); ?>
                                        </td>
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