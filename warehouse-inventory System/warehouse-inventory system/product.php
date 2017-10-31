<?php
$page_title = 'Customer Master';
require_once('includes/load.php');
page_require_level(1);

$all_products = find_by_sql("call spSelectAllProducts();")
?>


<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Product Master
        <small>Optional description</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Master
            </a>
        </li>
        <li class="active">Product</li>
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
            <h3 class="box-title">Product Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <form method="get" action="add_product.php">
            <button type="submit" name="add_prod" class="btn btn-primary">Add Product</button>
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
                                        <th>Product Code</th>
                                        <th>Product Desc.</th>
                                        <th>Department</th>
                                        <th>Category</th>
                                        <th>Subcategory</th>
                                        <th>Supplier</th>
                                        <th>Cost Price</th>
                                        <th>Sales Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_products as $prod): ?>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <form method="post" action="edit_product.php">
                                                    <button type="submit" name="product" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                                                    <input type="hidden" name="ProductCode" value="<?php echo remove_junk($prod['ProductCode']);?>" />
                                                </form>
                                                <form method="post" action="delete_product.php">
                                                    <button type="submit" name="product" class="btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                                                    <input type="hidden" name="ProductCode" value="<?php echo remove_junk($prod['ProductCode']);?>" />
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($prod['ProductCode']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($prod['ProductDesc'])); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($prod['DepartmentDesc']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($prod['CategoryDesc']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($prod['SubcategoryDesc']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk($prod['SupplierName']); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($prod['CostPrice'])); ?>
                                        </td>
                                        <td>
                                            <?php echo remove_junk(ucfirst($prod['SalePrice'])); ?>
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