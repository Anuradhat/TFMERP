
<form method="post" action="create_grn.php">
    <!-- /.box-header -->
    <input type="hidden" name="Edit" value="Edit" />

    <div class="box-body">
        <div class="col-xs-4">
            <div class="form-group">
                <label>Stock Code</label>
                <input type="text" class="form-control" id="StockCode" name="StockCode" placeholder="Stock Code" required="required" autocomplete="off" value="<?php echo $serchitem[0]; ?>" readonly="readonly" disabled="disabled" />
                <input type="hidden" name="hStockCode" id="hStockCode" value="<?php echo $serchitem[0]; ?>" />
            </div>

            <div class="form-group">
                <label>Balance Qty</label>
                <input type="text" class="integer form-control integer" name="Qty" id="pQty" placeholder="Qty" disabled="disabled" readonly="readonly" required="required" value="<?php echo $serchitem[4]; ?>" />
            </div>
        </div>

        <div class="col-xs-4">
            <div class="form-group">
                <label>Product Code</label>
                <input type="text" class="form-control" id="ProductCode" name="StockCode" placeholder="Product Code" required="required" autocomplete="off" value="<?php echo $serchitem[1]; ?>" readonly="readonly" disabled="disabled" />
                <input type="hidden" name="hProductCode" id="hProductCode" value="<?php echo $serchitem[1]; ?>" />
            </div>

            <div class="form-group">
                <label>Expire Date</label>
                <input type="text" class="form-control pull-right" autocomplete="off" name="ExpireDate" id="datepicker" placeholder="Expire Date" />
            </div>


        </div>

        <div class="col-xs-4">
            <div class="form-group">
                <label>Product Description</label>
                <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" value="<?php echo $serchitem[2]; ?>" />
            </div>


            <div class="form-group">
                <label>Grn Qty</label>
                <input type="number" class="integer form-control integer" name="GrnQty" id="pGrnQty" placeholder="Grn Qty" required="required" value="<?php echo $serchitem[4]; ?>" />
            </div>

       
        </div>
        <!-- /.box-body -->
    </div>
</form>
<!-- /.box -->




<script type="text/javascript">
    function EditItem(ctrl, event) {
        event.preventDefault();

        var Qty = parseInt($("#pQty").val());
        var GrnQty = $("#pGrnQty").val();
        var ProductCode = $("#hProductCode").val();

        if (GrnQty <= 0) {
            $("#pGrnQty").focus();
            bootbox.alert('GRN qty is invalid.');
        }
        else if (Qty < GrnQty) {
            $("#pGrnQty").focus();
            bootbox.alert('You cannot exceed original PO qty.');
        }
        else {
            $.ajax({
                url: "create_grn.php",
                type: "POST",
                data: $("form").serialize(),
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

    //Initialize Date picker
    $('#datepicker').datepicker({
        format: 'yyyy/mm/dd',
        startDate: '-1d',
        autoclose: true
    })
</script>
