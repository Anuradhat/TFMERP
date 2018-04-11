
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Product Code</th>
            <th>Product Description</th>
            <th>Sale Price</th>
            <th>Qty</th>
            <th>Tax Amount</th>
            <th>Amount</th>
            <th>Serial</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td style="vertical-align: middle;">Gross Amount:</td>
            <td style="vertical-align: middle;">
                <input type="text" class="form-control text-right" name="GrossAmount" id="GrossAmount" placeholder="Gross Amount" value="<?php $TotalAmount = 0;foreach($arr_item  as &$value){ $TotalAmount += $value[5];} echo number_format((float)$TotalAmount, 2, '.', ''); ?>" required="required" readonly="readonly" disabled /></td>
            <td></td>
            <td style="vertical-align: middle;">Discount:</td>
            <td>
                <input type="text" class="form-control text-right decimal" name="DiscountAmount" id="DiscountAmount" placeholder="Discount Amount" autocomplete="off" value="<?php  if($_SESSION['DiscountAmount'] == null) echo "0.00"; else echo $_SESSION['DiscountAmount'];  ?>" /></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="vertical-align: middle;">
                <label>Net Amount:</label>
            </td>
            <td>
                <input type="text" class="form-control text-right" name="NetAmount" id="NetAmount" placeholder="Net Amount" value="<?php $TotalAmount = 0;foreach($arr_item  as &$value){ $TotalAmount += $value[5];} echo number_format((float)$TotalAmount - ($_SESSION['DiscountAmount'] == null ? 0:$_SESSION['DiscountAmount']), 2, '.', ''); ?>" required="required" readonly="readonly" disabled />
            </td>
            <td></td>
        </tr>
    </tfoot>
    <tbody>
        <?php  foreach($arr_item  as &$value) {
                   $Location =  $_SESSION['LocationCode'];  $SIH = SelectStockSIHFormProduct($value[0],$Location)   ?>
        <tr <?php if($SIH < $value[4]) echo "class=text-danger"; ?>>
            <td>
                <div>
                    <button type="button" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#myModal" contenteditable="false"
                        <?php if($SIH == 0) echo "disabled";?>></button>
                    <button type="button" class="btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn" id="btnDelete"></button>
                </div>
            </td>
            <td id="RowId" class="clsRowId">
                <?php echo $value[0] ?>
            </td>
            <td>
                <?php echo $value[1] ?>
            </td>
            <td >
                <?php echo number_format(($value[3] == null ? 0 : $value[3]),2) ?>
            </td>
            <td class="clsInvQty">
                <?php echo $value[4] ?>
            </td>
            <td>
                <?php echo number_format(($value[7] == null ? 0 : $value[7]),2) ?>
            </td>
            <td >
                <?php  echo number_format(($value[5] == null ? 0 : $value[5]),2) ?>
            </td>
            <td>
                <div>
                    <button type="button" class="SerialBtn btn btn-primary btn-xs glyphicon glyphicon-list-alt" data-toggle="modal" data-target="#myModal" <?php if($SIH == 0) echo "disabled";   ?>></button>
                </div>
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
            var prodcode = $row.find(".clsRowId").text().trim();
            $.ajax({
                url: "create_invoice.php",
                type: "POST",
                data: { "_productcode": prodcode },
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
                url: "create_invoice.php",
                type: "POST",
                data: '_RowNo=' + RowNo.trim(),
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Invoice Item');
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