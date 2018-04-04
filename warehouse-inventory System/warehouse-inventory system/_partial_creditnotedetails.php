
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Serial Code</th>
            <th>Product Code</th>
            <th>Product Description</th>
            <th>Sale Price</th>
            <th>Qty</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="vertical-align: middle;">
                <label>Amount:</label>
            </td>
            <td>
                <input type="text" class="form-control text-right" name="NetAmount" id="NetAmount" placeholder="Net Amount" value="<?php $TotalAmount = 0;foreach($arr_item  as &$value){ $TotalAmount += $value[3] * $value[4];} echo number_format($TotalAmount,2); ?>" required="required" readonly="readonly" disabled />
            </td>
        </tr>
    </tfoot>
    <tbody>
        <?php  foreach($arr_item  as &$value) { ?>
        <tr>
            <td>
                <div>
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
                <?php echo $value[2] ?>
            </td>
            <td >
                <?php echo number_format($value[3],2) ?>
            </td>
            <td class="clsInvQty">
                <?php  echo $value[4] ?>
            </td>
            <td>
                <?php  echo number_format($value[5],2) ?>
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
                url: "create_creditnote.php",
                type: "POST",
                data: { "_productcode": prodcode },
                success: function (result) {
                    $('#table').html(result);
                    $('.loader').fadeOut();
                }
            });


        });
    });

</script>