
<form method="post" action="create_transfernote.php">
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
                <label>Trn. Qty</label>
                <input type="number" class="integer form-control integer" name="TrnQty" id="pTrnQty" placeholder="Trn Qty" required="required" value="<?php echo $serchitem[5]; ?>" />
            </div>
        </div>

        <div class="col-xs-4">
            <div class="form-group">
                <label>Product Description</label>
                <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" value="<?php echo $serchitem[2]; ?>" />
            </div>


        </div>

        <div class="col-xs-4">
            <div class="form-group">
                <label>SIH</label>
                <input type="text" class="integer form-control integer" name="SIH" id="pSIH" placeholder="SIH" disabled="disabled" readonly="readonly" required="required" value="<?php echo $serchitem[5]; ?>" />
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</form>
<!-- /.box -->




<script type="text/javascript">
    function EditItem(ctrl, event) {
        event.preventDefault();
        
        $('.loader').show();

        var SIH = parseInt($("#pSIH").val());
        var TrnQty = $("#pTrnQty").val();
        var ProductCode = $("#hProductCode").val();

        if (TrnQty <= 0) {
            $("#pTrnQty").focus();
            $('.loader').fadeOut();
            bootbox.alert('Transfer qty is invalid.');
        }
        else if (SIH < TrnQty) {
            $("#pTrnQty").focus();
            $('.loader').fadeOut();
            bootbox.alert('You cannot exceed stock in hand qty.');
        }
        else {
            $.ajax({
                url: "create_transfernote.php",
                type: "POST",
                data: $("form").serialize(),
                success: function (result) {
                    $("#tblBinDetails").html(result);
                    $('#myModal').modal('toggle');
                    //$('#modal-container').modal('hide');
                },
                complete: function (result) {
                    $('.loader').fadeOut();
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
</script>
