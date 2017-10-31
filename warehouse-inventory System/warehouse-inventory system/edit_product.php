<?php
$page_title = 'Product Master - Edit Product';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);


$all_departments = find_by_sql("call spSelectAllDepartments();");
$all_Category = find_by_sql("call spSelectAllCategory();");
//$all_Subcategory = find_by_sql("call spSelectAllSubcategory();");
$all_Supplier = find_by_sql("call spSelectAllSuppliers();");
$all_Taxs = find_by_sql("call spSelectAllTaxRates();");

?>


<?php
if(isset($_POST['product'])){

    $p_productcode = remove_junk($db->escape($_POST['ProductCode']));


    if(!$p_productcode){
        $session->msg("d","Missing product identification.");
        redirect('product.php');
    }
    else
    {
        $product = find_by_sp("call spSelectProductFromCode('{$p_productcode}');");
      
        $all_Subcategory = find_by_sql("call spSelectSubCategoryFromCategory('{$product['CategoryCode']}');");
        $Product_taxs =  find_by_sql("call spSelectProductTaxFromProductCode('{$p_productcode}');");

        if(!$product){
            $session->msg("d","Missing product details.");
            redirect('product.php');
        }
    }
}
?>


<?php
if(isset($_POST['edit_product'])){
    $req_fields = array('hProductCode','ProductDesc','SupplierCode','CostPrice','SalePrice','SalesComPer','ReorderLevel');

    validate_fields($req_fields);

    if(empty($errors)){
        $p_ProductCode = remove_junk($db->escape($_POST['hProductCode']));
        $p_ProductDesc  = remove_junk($db->escape($_POST['ProductDesc']));
        $p_OtherDesc  = remove_junk($db->escape($_POST['OtherDesc']));
        $p_SupplierCode  = remove_junk($db->escape($_POST['SupplierCode']));
        $p_CostPrice  =  remove_junk(string2Value($db->escape($_POST['CostPrice'])));
        $p_SalePrice  = remove_junk(string2Value($db->escape($_POST['SalePrice'])));
        $p_WholeSalePrice  = remove_junk(string2Value($db->escape($_POST['WholeSalePrice'])));
        $p_DiscountAmount  = remove_junk(string2Value($db->escape($_POST['DiscountAmount'])));
        $p_SalesComPer  = remove_junk(string2Value($db->escape($_POST['SalesComPer'])));
        $p_DiscountPer  = remove_junk(string2Value($db->escape($_POST['DiscountPer'])));
        $p_ReorderLevel  = remove_junk(string2Value($db->escape($_POST['ReorderLevel'])));
        $p_Warranty  = remove_junk(string2Boolean($db->escape($_POST['Warranty'])));
        $p_Tax  =    $db->escape_array($_POST['Taxs']);

        $p_Warranty = string2Boolean($p_Warranty);
        $Tax_Selected = string2Boolean(count($p_Tax) > 0);
        $date    = make_date();
        $user = "anush";

  
        try
        {
            $db->begin();
            //$db->query("start transaction");

            $query  = "call spUpdateProduct('{$p_ProductCode}','{$p_ProductDesc}','{$p_OtherDesc}','{$p_SupplierCode}',{$p_CostPrice},
                           {$p_SalePrice},{$p_WholeSalePrice},{$p_DiscountAmount},{$p_SalesComPer},{$p_DiscountPer},{$p_ReorderLevel},
                           {$p_Warranty},{$Tax_Selected},'{$date}','{$user}');";

            $db->query($query);


            $query  = "call spDeleteProductTax('{$p_ProductCode}');";

            $db->query($query);


            foreach ($p_Tax as &$value) 
            {
                $query  = "call spInsertProductTax('{$p_ProductCode}','{$value}','{$date}','{$user}');";
                $db->query($query);
            }


            $db->commit();


            $session->msg('s',"Product updated ");
            redirect('product.php', false);

        }
        catch(Exception $ex)
        {
            $db->rollback();

            $session->msg('d',' Sorry failed to updated!');
            redirect('edit_product.php', false);
        }


    } else{
        $session->msg("d", $errors);
        redirect('edit_product.php',false);
    }
}

?>



<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Product Master
        <small>Update Product Details</small>
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
    <form method="post" action="edit_product.php">
        <div class="row">
            <div class="col-md-12"><?php echo display_msg($msg); ?>
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
                            <label>Product Code</label>
                            <input type="text" class="form-control" name="ProductCode" placeholder="Product Code" required="required" value="<?php echo remove_junk($product['ProductCode']);?>" readonly="readonly" disabled="disabled" />
                            <input type="hidden" name="hProductCode" value="<?php echo remove_junk($product['ProductCode']);?>" />
                        </div>

                        <div class="form-group">
                            <label>Other Description</label>
                            <input type="text" class="form-control" name="OtherDesc" placeholder="Other Description" value="<?php echo remove_junk($product['OtherDesc']);?>" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Product Description</label>
                            <input type="text" class="form-control" name="ProductDesc" placeholder="Product Description" required="required" value="<?php echo remove_junk($product['ProductDesc']);?>"/>
                        </div>

                        <div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2" style="width: 100%;" name="SupplierCode"required="required" >
                                <option value="">Select Supplier</option>
                                 <?php  foreach ($all_Supplier as $supp): ?>
                                <option value="<?php echo $supp['SupplierCode'] ?>" <?php if($supp['SupplierCode'] === $product['SupplierCode']): echo "selected"; endif; ?>  ><?php echo $supp['SupplierName'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Product Level Details</h3>

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
                            <label>Department</label>
                            <select class="form-control select2" style="width: 100%;" name="DepartmentCode" required="required" readonly="readonly" disabled="disabled">
                                <option value="">Select Department</option><?php  foreach ($all_departments as $dep): ?>
                                <option value="<?php echo $dep['DepartmentCode'] ?>"  <?php if($dep['DepartmentCode'] === $product['DepartmentCode']): echo "selected"; endif; ?>    ><?php echo $dep['DepartmentDesc'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Sub Category</label>
                            <select class="form-control select2" style="width: 100%;" name="SubcategoryCode" required="required" id="ddlSubCategory" readonly="readonly" disabled="disabled">
                                <option value="">Select Sub Category</option>
                                <?php  foreach ($all_Subcategory as $scat): ?>
                                <option value="<?php echo $scat['SubcategoryCode'] ?>"  <?php if($scat['SubcategoryCode'] === $product['SubcategoryCode']): echo "selected"; endif; ?>    ><?php echo $scat['SubcategoryDesc'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <label>Category</label>
                        <select class="form-control select2" style="width: 100%;" name="CategoryCode" required="required" id="ddlCategory" readonly="readonly" disabled="disabled">
                            <option value="">Select Category</option><?php  foreach ($all_Category as $cat): ?>
                            <option value="<?php echo $cat['CategoryCode'] ?>"  <?php if($cat['CategoryCode'] === $product['CategoryCode']): echo "selected"; endif; ?> ><?php echo $cat['CategoryDesc'] ?>
                            </option><?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Pricing Information</h3>

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
                            <label>Cost Price</label>
                            <input type="text" class="form-control" name="CostPrice" placeholder="Cost Price" required="required" value="<?php echo remove_junk($product['CostPrice']);?>"/>
                        </div>

                        <div class="form-group">
                            <label>Whole Sale Price</label>
                            <input type="text" class="form-control" name="WholeSalePrice" placeholder="Whole Sale Price" value="<?php echo remove_junk($product['WholeSalePrice']);?>"/>
                        </div>

                        <div class="form-group">
                            <label>Discount Percentage</label>
                            <input type="text" class="form-control" name="DiscountPer" placeholder="Discount Percentage (%)" value="<?php echo remove_junk($product['DiscountPer']);?>"/>
                        </div>
                        
                        <div class="form-group">
                            <label>Item Tax(s)</label>
                            <select class="form-control select2" name="Taxs[]" multiple="multiple" data-placeholder="Select Tax(s)" style="width: 100%;">
                                <option value="">Select Tax(s)</option><?php  foreach ($all_Taxs as $tax): ?>
                                <option value="<?php echo $tax['TaxCode'] ?>"  <?php if(EqualValue($tax['TaxCode'],$Product_taxs)): echo "selected"; endif; ?>><?php echo $tax['TaxDesc'] ?>
                                </option><?php endforeach; ?>

                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sale Price</label>
                            <input type="text" class="form-control" name="SalePrice" placeholder="Sale Price" required="required" value="<?php echo remove_junk($product['SalePrice']);?>"/>
                        </div>

                        <div class="form-group">
                            <label>Discount Amount</label>
                            <input type="text" class="form-control" name="DiscountAmount" placeholder="Discount Amount" value="<?php echo remove_junk($product['DiscountAmount']);?>"/>
                        </div>

                        <div class="form-group">
                            <label>Sales Commission</label>
                            <input type="text" class="form-control" name="SalesComPer" placeholder="Sales Commission (%)" required="required" value="<?php echo remove_junk($product['SalesComPer']);?>"/>
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
                            <label>Re-order Level</label>
                            <input type="number" class="form-control" name="ReorderLevel" placeholder="Reorder Level" required="required" value="<?php echo remove_junk($product['ReorderLevel']);?>"/>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group checkbox">
                            <label class="form-check-label"> Warranty </label><br>
                                <input type="checkbox" name="Warranty" class="form-check-input"  <?php if(remove_junk($product['Warranty'] === "1")): echo "checked"; endif; ?> >
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <button type="submit" name="edit_product" class="btn btn-success btn-lg">Save  </button>
       </form>
</section>

<?php include_once('layouts/footer.php'); ?>


<script>
        $('#ddlCategory').change(function () {
            value = $(this).val();
            $.ajax({
                type: "POST",
                url: "product_BL.php", // Name of the php files
                data: { "_category": value },
                success: function (result) {
                    $("#ddlSubCategory").html(result); // clear before append
                }
            });
        });
</script>