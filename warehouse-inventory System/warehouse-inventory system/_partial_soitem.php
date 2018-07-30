<?php
//global $session;
$current_user = current_user();
$login_level = find_by_groupLevel($current_user['user_level']);
$UserAccess = PageApprovelDetailsByUserName('NoNeed');
$PageName = 'Quotation Approval';
$AccessStatus = 0;

foreach($UserAccess as $UAccess){
    if($PageName == $UAccess['Page'] and $UAccess['Controller'] == 'Edit Price'){
        $AccessStatus = $UAccess["Access"];
    }
}
?>

<div class="row">
    <form method="post" action="edit_salesorder_.php">
        <!-- /.box-header -->
        <div class="col-xs-3">
            <div class="form-group">
                <label>Product Code</label>
                <input type="text" class="form-control" id="ProductCode" name="ProductCode" placeholder="Product Code" required="required" autocomplete="off" value="<?php echo $serchitem[0]; ?>" readonly="readonly" disabled="disabled" />
                <input type="hidden" name="hProductCode" id="hProductCode" value="<?php echo $serchitem[0]; ?>" />
            </div>

            <div class="form-group">
                <label>Average Cost</label>
                <input type="text" class="form-control" name="pAverageCost" id="pAverageCost" placeholder="Average Cost" readonly="readonly" disabled="disabled" value="<?php echo $serchitem[7]; ?>" />
            </div>
        </div>

        <div class="col-xs-3">
            <div class="form-group">
                <label>Product Description</label>
                <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" value="<?php echo $serchitem[1]; ?>" />
            </div>

            <div class="form-group">
                <label>Sales Percentage (%)</label>
                <input type="number" class="form-control integer" name="pSalesPercentage" id="pSalesPercentage" placeholder="Sales Percentage (%)" value="<?php echo $serchitem[8]; ?>" />
            </div>
        </div>


        <div class="col-xs-3">
            <div class="form-group">
                <label>Sale Price</label>
                <input type="text" class="form-control decimal" name="SalePrice" id="pSalePrice" placeholder="Sale Price" required="required" value="<?php echo $serchitem[2]; ?>" <?php if($AccessStatus == '1'){ echo '';} else {echo "disabled=\"disabled\"";} ?> />
            </div>

            <div class="form-group checkbox">
                <label class="form-check-label">
                    <input type="checkbox" name="ExcludeTax" id="ExcludeTax" class="form-check-input" <?php if($AccessStatus == '1'){ echo '';} else {echo "disabled=\"disabled\"";} ?> <?php if($serchitem[6] == 1): echo "checked"; endif; ?> />
                    Exclude Tax
                </label>
            </div>
        </div>

        <div class="col-xs-3">
            <div class="form-group">
                <label>Qty</label>
                <input type="number" class="form-control integer" name="Qty" id="pQty" placeholder="Qty" required="required" value="<?php echo $serchitem[3]; ?>" />
            </div>
        </div>
    </form>
</div>




<script type="text/javascript">
    function EditItem(ctrl, event) {
        event.preventDefault();

        $('.loader').show();

        var SalePrice = parseFloat($("#pSalePrice").val());
        var Qty = parseInt($("#pQty").val());
        var ProductCode = $("#hProductCode").val();
        var ExcludeTax = $("#ExcludeTax").prop('checked');
        var AverageCost = parseFloat($("#pAverageCost").val() == null || $("#pAverageCost").val() == "" ? 0 : $("#pAverageCost").val());
        var SalesPercentage = parseFloat($("#pSalesPercentage").val() == null || $("#pSalesPercentage").val() == "" ? 0 : $("#pSalesPercentage").val());

        if (SalePrice <= 0) {
            $("#pSalePrice").focus();
            bootbox.alert('You enter sale price is invalid.');
        }
        else if (Qty <= 0) {
            $("#pQty").focus();
            bootbox.alert('You enter qty is invalid.');
        }
        else {
            $.ajax({
                url: "edit_salesorder_.php",
                type: "POST",
                data: { Edit: 'Edit', ProductCode: ProductCode, Qty: Qty, SalePrice: SalePrice, ExcludeTax: ExcludeTax, AverageCost: AverageCost, SalesPercentage: SalesPercentage },
                success: function (result) {
                    $("#table").html(result);
                    $('#myModal').modal('toggle');
                },
                complete: function (result) {
                    $('.loader').fadeOut();
                }
            });
        }
    }


    $('#pSalesPercentage').bind('input', function () {

        var AverageCost = $('#pAverageCost').val();
        var SalesPercentage = $(this).val();

        if ($(this).val() < 0) {
            bootbox.alert('Sales percentage cannot be negative.');
            $(this).val('');
        }
        else if (AverageCost == "" || AverageCost == null || AverageCost == 0) {
            bootbox.alert('Invalid average cost price.');
            $(this).val('');
        }
        else {
            var value = (parseFloat((AverageCost * SalesPercentage) / 100) + parseFloat(AverageCost)).toFixed(2);
            $('#pSalePrice').val(value);
        }
    });




    //Textbox integer accept
    $(".integer").keypress(function (evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    });

    $(".decimal").keypress(function (e) {
        var keyCode = (e.which) ? e.which : e.keyCode;
        if ((keyCode >= 48 && keyCode <= 57) || (keyCode == 8))
            return true;
        else if (keyCode == 46) {
            var curVal = document.activeElement.value;
            if (curVal != null && curVal.trim().indexOf('.') == -1)
                return true;
            else
                return false;
        }
        else
            return false;
    });
</script>
