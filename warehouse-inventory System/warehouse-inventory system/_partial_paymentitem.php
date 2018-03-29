
<div class="row">
    <form method="post" action="create_invoice.php">
        <!-- /.box-header -->
        <div class="col-xs-3">
            <div class="form-group">
                <label>Invoice Number</label>
                <input type="text" class="form-control" id="InvoiceNo" name="InvoiceNo" placeholder="Invoice No" required="required" autocomplete="off" value="<?php echo $serchitem[0]; ?>" readonly="readonly" disabled="disabled" />
                <input type="hidden" name="hInvoiceNo" id="hInvoiceNo" value="<?php echo $serchitem[0]; ?>" />
            </div>
        </div>

        <div class="col-xs-3">
            <div class="form-group">
                <label>Due Amount</label>
                <input type="text" class="form-control" name="DueAmount" id="DueAmount" placeholder="Due Amount" required="required" readonly="readonly" disabled="disabled" value="<?php echo  number_format(($serchitem[4] == null ? 0 : $serchitem[4] ),2) ; ?>" />
                <input type="hidden" name="hDueAmount" id="hDueAmount" value="<?php echo $serchitem[4]; ?>" />
            </div>
        </div>
        

        <div class="col-xs-3">
            <div class="form-group">
                <label>Avalable To Payment</label><?php if($_SESSION['AvalableToPayment'] != null) $AvalableToPayment = $_SESSION['AvalableToPayment']; else $AvalableToPayment = 0; ?>
                <input type="text" class="integer form-control decimal" name="AvalableToPay" id="AvalableToPay" placeholder="Avalable To Payment" required="required" value="<?php echo number_format($AvalableToPayment,2); ?>" disabled readonly="readonly" />
                <input type="hidden" name="hAvalableToPay" id="hAvalableToPay" value="<?php echo $AvalableToPayment; ?>" />
            </div>
        </div>

        <div class="col-xs-3">
            <div class="form-group">
                <label>Payment</label>
                <input type="number" class="integer form-control integer" name="Payment" id="Payment" placeholder="Payment" required="required" value="<?php echo $serchitem[5]; ?>" />
            </div>
        </div>
    </form>
</div>




<script type="text/javascript">
    function EditItem(ctrl, event) {
        event.preventDefault();

        $('.loader').show();

        var AvalableToPay = parseFloat($("#hAvalableToPay").val());
        var DueAmount = $("#hDueAmount").val();
        var Payment = parseFloat($("#Payment").val());
        var InvoiceNo = $("#hInvoiceNo").val();
        



        if (Payment <= 0 || isNaN(Payment)) {
           $('.loader').fadeOut();
           $("#Payment").focus();
           bootbox.alert('Invalid payment.');
        }
        else if (Payment > AvalableToPay) {
            $('.loader').fadeOut();
            $("#Payment").focus();
            bootbox.alert('You cannot exceed available to payment.');
        }
        else if (Payment > DueAmount) {
            $('.loader').fadeOut();
            $("#Payment").focus();
            bootbox.alert('You cannot exceed due amount.');
        }
        else {
            $.ajax({
                url: "customer_payment.php",
                type: "POST",
                data: { Edit: 'Edit', InvoiceNo: InvoiceNo, Payment: Payment },
                success: function (result) {
                    $("#table").html(result);
                    $('#myModal').modal('toggle');
                    $('.loader').fadeOut();
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
