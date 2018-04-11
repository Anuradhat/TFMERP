
<div class="row">
    <form method="post" action="create_customerpo.php">
        <!-- /.box-header -->
        <div class="col-xs-3">
            <div class="form-group">
                <label>Product Code</label>
                <input type="text" class="form-control" id="ProductCode" name="StockCode" placeholder="Product Code" required="required" autocomplete="off" value="<?php echo $serchitem[0]; ?>" readonly="readonly" disabled="disabled" />
                <input type="hidden" name="hProductCode" id="hProductCode" value="<?php echo $serchitem[0]; ?>" />
            </div>
        </div>

        <div class="col-xs-3">
            <div class="form-group">
                <label>Product Description</label>
                <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" value="<?php echo $serchitem[1]; ?>" />
            </div>
        </div>


        <div class="col-xs-3">
            <div class="form-group">
                <label>Sale Price</label>
                <input type="text" class="integer form-control decimal" name="SalePrice" id="pSalePrice" placeholder="Sale Price" required="required" value="<?php echo $serchitem[2]; ?>" disabled readonly="readonly" />
            </div>
        </div>

        <div class="col-xs-3">
            <div class="form-group">
                <label>Qty</label>
                <input type="number" class="integer form-control integer" name="Qty" id="pQty" placeholder="Qty" required="required" value="<?php echo $serchitem[3]; ?>" />
            </div>
        </div>
    </form>
</div>




<script type="text/javascript">
    function EditItem(ctrl, event) {
        event.preventDefault();
        
        var SalePrice = $("#pSalePrice").val();
        var Qty = parseInt($("#pQty").val());
        var ProductCode = $("#hProductCode").val();

        if (Qty <= 0) {
            $("#pQty").focus();
            bootbox.alert('You enter qty is invalid.');
        }
        else {
            $.ajax({
                url: "create_customerpo.php",
                type: "POST",
                data: { Edit: 'Edit', ProductCode: ProductCode, Qty: Qty, SalePrice: SalePrice },
                success: function (result) {
                    $("#table").html(result);
                    $('#myModal').modal('toggle');
                    //$('#modal-container').modal('hide');
                }
            });
        }
    }


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
