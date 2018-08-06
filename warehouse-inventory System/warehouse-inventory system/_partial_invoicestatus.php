<?php
//global $session;
//$current_user = current_user();
//$login_level = find_by_groupLevel($current_user['user_level']);
//$UserAccess = PageApprovelDetailsByUserName('NoNeed');
$PageName = 'Invoice Status';
$AccessStatus = 0;

//foreach($UserAccess as $UAccess){
//    if($PageName == $UAccess['Page'] and $UAccess['Controller'] == 'Edit Price'){
//        $AccessStatus = $UAccess["Access"];
//    }
//}
?>

<div class="row">
    <form method="post" action="InvoiceStatus.php">
        <!-- /.box-header -->
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Invoice No</label>
                            <input id="txtInvoiceNo" type="text" class="form-control pull-right" name="nameInvoiceNo" required="required" disabled value="<?php  echo $InvoiceNo; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Current Status</label>
                            <input id="txtInvoiceNo" type="text" class="form-control pull-right" name="nameInvoiceStaturs" required="required" disabled value="<?php  echo $InvoiceStatus; ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Invoice Status</label>
                            <select class="form-control select2" style="width: 100%;" name="nameInvoiceStatusCode" id="InvoiceStatusCodee" required="required" >
                                <option value="">Select Invoice Status</option><?php  foreach ($all_InvoicesStatusM as $invoiceStatus): ?>
                                <option value="<?php echo $invoiceStatus['InvoiceStatusCode'] ?>"> <?php echo $invoiceStatus['InvoiceStatusDescription'] ?>
                                </option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    </form>
</div>




<script type="text/javascript">
    function EditItem(ctrl, event) {
        event.preventDefault();
        
        $('.loader').show();

        var txtInvoiceNo = $("#txtInvoiceNo").val();
        var InvoiceStatusCodee = $("#InvoiceStatusCodee").val();
        var InvoiceStatusDescriptio = $("#InvoiceStatusCodee option:selected").text();

           $.ajax({
                url: "InvoiceStatus.php",
                type: "POST",
                data: { InvNo: txtInvoiceNo, InvSts: InvoiceStatusCodee},
                success: function (result) {
                    $("#InvSts").html(InvoiceStatusDescriptio);
                    $('#myModal').modal('toggle');
                },
                complete: function (result) {
                    $('.loader').fadeOut();
                }
            });

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
