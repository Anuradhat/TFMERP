<?php
ob_start();

$page_title = 'Product Master - New Product';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(2);

//$all_departments = find_by_sql("call spSelectAllDepartments();");
$all_Category = find_by_sql("call spSelectAllCategory();");
$all_Subcategory = find_by_sql("call spSelectAllSubcategory();");
//$all_Supplier = find_by_sql("call spSelectAllSuppliers();");
$all_Taxs = find_by_sql("call spSelectAllTaxRates();");
?>


<?php
if(isset($_POST['add_product'])){
    $req_fields = array('ProductDesc','CategoryCode','SubcategoryCode','SalesComPer','ReorderLevel');

    validate_fields($req_fields);

    if(empty($errors)){
        //$p_ProductCode = remove_junk($db->escape($_POST['ProductCode']));
        $p_ProductDesc  = remove_junk($db->escape($_POST['ProductDesc']));
        $p_OtherDesc  = remove_junk($db->escape($_POST['OtherDesc']));
        $p_DepartmentCode  = "GEN";//remove_junk($db->escape($_POST['DepartmentCode']));
        $p_CategoryCode  = remove_junk($db->escape($_POST['CategoryCode']));
        $p_SubcategoryCode  = remove_junk($db->escape($_POST['SubcategoryCode']));
        $p_SupplierCode  = remove_junk($db->escape($_POST['SupplierCode']));
        $p_CostPrice  = 0;        //remove_junk(string2Value($db->escape($_POST['CostPrice'])));
        $p_SalePrice  = 0;       //remove_junk(string2Value($db->escape($_POST['SalePrice'])));
        $p_WholeSalePrice  = 0; //remove_junk(string2Value($db->escape($_POST['WholeSalePrice'])));
        $p_AvgCostPrice = 0;
        $p_DiscountAmount  = 0;//remove_junk(string2Value($db->escape($_POST['DiscountAmount'])));
        $p_SalesComPer  = remove_junk(string2Value($db->escape($_POST['SalesComPer'])));
        $p_DiscountPer  = 0; //remove_junk(string2Value($db->escape($_POST['DiscountPer'])));
        $p_SalesPer = remove_junk(string2Value($db->escape($_POST['SalesPer'])));
        $p_ReorderLevel  = remove_junk(string2Value($db->escape($_POST['ReorderLevel'])));
        $p_Warranty  = remove_junk(string2Boolean($db->escape($_POST['Warranty'])));
        $p_Tax  =    $db->escape_array($_POST['Taxs']);

        $p_Warranty = string2Boolean($p_Warranty);
        $Tax_Selected = string2Boolean(count($p_Tax) > 0);
        $date    = make_date();
        $user =  current_user();

        $p_ProductCode  = autoGenerateNumber('tfmProductM',1);

        $prod_code = $p_SubcategoryCode.$p_ProductCode;

        $prod_count = find_by_sp("call spSelectProductFromCode('{$prod_code}');");

        if($prod_count)
        {
            $session->msg("d", "This product code exist in the system.");
            redirect('add_product.php',false);
        }


        try
        {
             $db->begin();
            //$db->query("start transaction");$p_AvgCostPrice

           $query  = "call spInsertProduct('{$prod_code}','{$p_ProductDesc}','{$p_OtherDesc}','{$p_DepartmentCode}','{$p_CategoryCode}','{$p_SubcategoryCode}',
                   '{$p_SupplierCode}',{$p_CostPrice},{$p_SalePrice},{$p_WholeSalePrice},{$p_AvgCostPrice},{$p_DiscountAmount},{$p_SalesComPer},{$p_DiscountPer},{$p_ReorderLevel},
                    {$p_Warranty},{$Tax_Selected},{$p_SalesPer},'{$date}','{$user["username"]}');";

           $db->query($query);


           foreach ($p_Tax as &$value) 
           {
               $query  = "call spInsertProductTax('{$prod_code}','{$value}','{$date}','{$user["username"]}');";
              $db->query($query);
           }

             $db->commit();

            $session->msg('s',"Product added ");
            redirect('add_product.php', false);

        }
        catch(Exception $ex)
        {
           $db->rollback();

           $session->msg('d',' Sorry failed to added!');
           redirect('product.php', false);
        }


    
        //if($db->query($query)){
        //    $session->msg('s',"Product added ");
        //    redirect('add_product.php', false);
        //} else {
        //    $session->msg('d',' Sorry failed to added!');
        //    redirect('product.php', false);
        //}

    } else{
        $session->msg("d", $errors);
        redirect('add_product.php',false);
    }
}

?>







<?php include_once('layouts/header.php'); ?>
<section class="content-header">
    <h1>
        Product Master
        <small>Enter New Product Details</small>
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
    <form method="post" action="add_product.php">
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="btn-group">
                            <button type="submit" name="add_product" class="btn btn-primary">&nbsp;Save&nbsp;&nbsp;</button>
                            <button type="reset" class="btn btn-success">&nbsp;Reset&nbsp;&nbsp;</button>
                            <button type="button" class="btn btn-warning" onclick="window.location = 'product.php'">Cancel  </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12"><?php echo display_msg($msg); ?>
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
                        <!--<div class="form-group">
                            <label>Department</label>
                            <select class="form-control select2" style="width: 100%;" name="DepartmentCode" required="required">
                                <option value="">Select Department</option><?php  //foreach ($all_departments as $dep): ?>
                                <option value="<?php //echo $dep['DepartmentCode'] ?>"><?php //echo $dep['DepartmentDesc'] ?>
                                </option><?php //endforeach; ?>
                            </select>
                        </div>-->
                        <div class="form-group">
                            <label>Category</label>
                            <select class="form-control select2" style="width: 100%;" name="CategoryCode" required="required" id="ddlCategory">
                                <option value="">Select Category</option><?php  foreach ($all_Category as $cat): ?>
                                <option value="<?php echo $cat['CategoryCode'] ?>"><?php echo $cat['CategoryDesc'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">

                        <div class="form-group">
                            <label>Sub Category</label>
                            <select class="form-control select2" style="width: 100%;" name="SubcategoryCode" required="required" id="ddlSubCategory">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Product Infromation</h3>

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
                            <input type="text" class="form-control" name="ProductCode" placeholder="Code will generate after save" required="required" readonly="readonly" disabled="disabled"/>
                        </div>

                        <div class="form-group">
                            <label>Other Description</label>
                            <input type="text" class="form-control" name="OtherDesc" placeholder="Other Description" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Product Description</label>
                            <input type="text" class="form-control" name="ProductDesc" placeholder="Product Description" required="required" />
                        </div>

                        <!--<div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2" style="width: 100%;" name="SupplierCode" required="required">
                                <option value="">Select Supplier</option><?php  //foreach ($all_Supplier as $supp): ?>
                                <option value="<?php //echo $supp['SupplierCode'] ?>"><?php //echo $supp['SupplierName'] ?>
                                </option><?php //endforeach; ?>
                            </select>
                        </div>-->
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
                        <!--<div class="form-group">
                            <label>Cost Price</label>
                            <input type="text" class="form-control" name="CostPrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Cost Price" required="required" />
                        </div>-->

                        <!--<div class="form-group">
                            <label>Whole Sale Price</label>
                            <input type="text" class="form-control" name="WholeSalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Whole Sale Price" />
                        </div>-->

                        <!--<div class="form-group">
                            <label>Discount Percentage (<output class="inline" for="fader" id="discrate">0</output>%)</label>
                            <input type="range" class="form-control" data-slider-id="blue" min="0" max="100" value="0" step="1" data-slider-tooltip="show" name="DiscountPer" placeholder="Discount Percentage (%)" oninput="outputDiscountRateUpdate(value)" />
                        </div>-->

                        <div class="form-group">
                          <label>Sales Percentage (<output class="inline" for="fader" id="discrate">0</output>%)</label>
                          <input type="range" class="form-control" data-slider-id="blue" min="0" max="100" value="0" step="1" data-slider-tooltip="show" name="SalesPer" placeholder="Sales Percentage (%)" oninput="outputDiscountRateUpdate(value)" />
                        </div>


                        <div class="form-group">
                            <label>Item Tax(s)</label>
                            <select class="form-control select2" name="Taxs[]" multiple="multiple" data-placeholder="Select Tax(s)" style="width: 100%;">
                                <option value="">Select Tax(s)</option><?php  foreach ($all_Taxs as $tax): ?>
                                <option value="<?php echo $tax['TaxCode'] ?>"><?php echo $tax['TaxDesc'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!--<div class="form-group">
                            <label>Sale Price</label>
                            <input type="text" class="form-control" name="SalePrice" pattern="([0-9]+\.)?[0-9]+" placeholder="Sale Price" required="required"/>
                        </div>-->

                        <!--<div class="form-group">
                            <label>Discount Amount</label>
                            <input type="text" class="form-control" name="DiscountAmount" pattern="([0-9]+\.)?[0-9]+" placeholder="Discount Amount" />
                        </div>-->

                        <div class="form-group">
                            <label>Sales Commission (<output class="inline" for="fader" id="salesrate">0</output>%)</label>
                            <input type="range" class="form-control" data-slider-id="blue" min="0" max="100" value="0" step="1" data-slider-tooltip="show" name="SalesComPer" placeholder="Sales Commission (%)" oninput="outputSalesRateUpdate(value)" />
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
                            <input type="number" class="form-control" name="ReorderLevel" placeholder="Reorder Level" required="required"/>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group checkbox">
                            <label class="form-check-label">
                                <input type="checkbox" name="Warranty" class="form-check-input">
                                Warranty
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
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


<script>
    function outputDiscountRateUpdate(vol) {
        document.querySelector('#discrate').value = vol;
    }

    function outputSalesRateUpdate(vol) {
        document.querySelector('#salesrate').value = vol;
    }
</script>