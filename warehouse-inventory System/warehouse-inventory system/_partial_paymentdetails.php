
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Invoice Number</th>
            <th>Invoice Date</th>
            <th>Gross Amount</th>
            <th>Net Amount</th>
            <th>Due Amount</th>
            <th>Payment</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Total Payment</td>
            <td><?php   $LineTotalPayment = 0; foreach($arr_item  as &$value) { $LineTotalPayment += $value[5]; } echo number_format(($LineTotalPayment == null ? 0 : $LineTotalPayment),2) ?></td>

        </tr>
        </tfoot>
            <tbody>
                <?php  foreach($arr_item  as &$value) { ?>
                <tr>
                    <td>
                        <div>
                            <button type="button" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#myModal" contenteditable="false"></button>
                        </div>
                    </td>
                    <td id="RowId" class="clsRowId">
                        <?php echo $value[0] ?>

                    </td>
                    <td>
                        <?php echo $value[1] ?>
                    </td>
                    <td>
                        <?php echo number_format(($value[2] == null ? 0 : $value[2]),2) ?>
                    </td>
                    <td class="clsInvQty">
                        <?php echo number_format(($value[3] == null ? 0 : $value[3]),2) ?>
                    </td>
                    <td>
                        <?php echo number_format(($value[4] == null ? 0 : $value[4]),2) ?>
                    </td>
                    <td>
                        <?php echo number_format(($value[5] == null ? 0 : $value[5]),2) ?>
                    </td>
                </tr><?php  } ?>
            </tbody>
</table>


<script>
//Item Delete
    $(document).ready(function () {
        $(".DeleteBtn").click(function () {
            $('.loader').show();

            var $row = $(this).closest("tr");
            var InvoiceNo = $row.find(".clsRowId").text().trim();
            $.ajax({
                url: "create_invoice.php",
                type: "POST",
                data: { "_InvoiceNo": InvoiceNo },
                success: function (result) {
                    $('#table').html(result);
                    $('.loader').fadeOut();
                }
            });


        });
    });


    $(document).ready(function () {
        $(".EditBtn").click(function () {
            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();


            $.ajax({
                url: "customer_payment.php",
                type: "POST",
                data: { AvalableToPayment: 'AvalableToPayment' },
                success: function (result) {
                   
                }
            });



            $.ajax({
                url: "customer_payment.php",
                type: "POST",
                data: '_InvoiceNo=' + RowNo.trim(),
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Invoice Payment');
                    $('.modal-body').html(modalBody);
                    $('.loader').fadeOut();
                }
            });


        });
    });


    $(document).ready(function () {
        $(".SerialBtn").click(function () {
            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();
            var InvQty = $row.find(".clsInvQty").text();
            var LocationCode = $("#LocationCode").val();

            if (InvQty == 0) {
                $('#myModal').modal('toggle');
                bootbox.alert('Invoice qty not found.');
            }
            else {
                $.ajax({
                    url: "create_invoice.php",
                    type: "POST",
                    data: { 'ProductCode': RowNo.trim(), 'InvQty': InvQty.trim(), 'LocationCode': LocationCode.trim() },
                    success: function (result) {
                        //$('#modal-container').html(result);
                        var modalBody = $('<div id="modalContent"></div>');
                        modalBody.append(result);
                        $("#myModalLabel").text('Serial Details');
                        $('.modal-body').html(modalBody);
                        $('.loader').fadeOut();
                    }
                });
            }

        });
    });


    $("#DiscountAmount").change(function () {
        $('.loader').show();

        var GrossAmount =  parseFloat($("#GrossAmount").val());
        var DiscountAmount = parseFloat($("#DiscountAmount").val());

        if (GrossAmount < DiscountAmount) {
            bootbox.alert('Discount amount cannot exceed  gross amount.');
            $("#NetAmount").val($("#GrossAmount").val());
           
            $.ajax({
                url: "create_invoice.php",
                type: "POST",
                data: { DiscountAmount: 0 },
                success: function (result) {

                }
            });

            $("#DiscountAmount").val("0.00");
            $('.loader').fadeOut();
        }
        else
        {
            $.ajax({
                url: "create_invoice.php",
                type: "POST",
                data: { DiscountAmount: DiscountAmount },
                success: function (result) {

                }
            });



            $("#NetAmount").val((GrossAmount - DiscountAmount).toFixed(2));

            $('.loader').fadeOut();
        }
    });
</script>