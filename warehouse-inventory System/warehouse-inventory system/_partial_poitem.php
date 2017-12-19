            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Purchase Order Item Detail</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-8">
                        <div class="box">
                            <form method="post" action="create_po.php">
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <div class="col-xs-4">
                                        <div class="form-group">
                                            <label>Product Code</label>
                                            <input type="text" class="form-control" id="ProductCode" name="ProductCode" placeholder="Product Code" required="required" autocomplete="off" value="<?php echo $serchitem[0]; ?>" readonly="readonly" disabled="disabled" />
                                            <input type="hidden" name="hProductCode" id="hProductCode" value="<?php echo $serchitem[0]; ?>" />
                                        </div>

                                        <div class="form-group">
                                            <label>Qty</label>
                                            <input type="number" class="integer form-control integer" name="Qty" id="pQty" placeholder="Qty" required="required" value="<?php echo $serchitem[3]; ?>" />
                                        </div>
                                    </div>

                                    <div class="col-xs-4">
                                        <div class="form-group">
                                            <label>Product Description</label>
                                            <input type="text" class="form-control" name="ProductDesc" id="ProductDesc" placeholder="Product Description" required="required" readonly="readonly" disabled="disabled" value="<?php echo $serchitem[1]; ?>"/>
                                        </div>

                                        <div class="form-group">
                                            <label>&nbsp;</label><br>
                                            <button type="submit" class="btn btn-info" onclick="EditItem(this, event);">Submit</button>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                            </form>
                            <!-- /.box -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default modal-close-btn" data-dismiss="modal">Close</button>
            </div>


<script type="text/javascript">
    function EditItem(ctrl, event) {
        event.preventDefault();

        var Qty = parseInt($("#pQty").val());
        var ProductCode = $("#hProductCode").val();

        if (Qty <= 0) 
        {
            $("#pQty").focus();
            bootbox.alert('You enter qty is invalid.');
        }
        else {
            $.ajax({
                url: "create_po.php",
                type: "POST",
                data: {Edit:'Edit',ProductCode:ProductCode,Qty:Qty},
                success: function (result) {
                    $("#table").html(result);
                    $('#modal-container').modal('hide');
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
