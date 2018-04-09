
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Stock Code</th>
            <th>Product Code</th>
            <th>Product Description</th>
            <th>Cost Price</th>
            <th>Qty</th>
            <th>Grn Qty</th>
            <th>Expire Date</th>
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
            <td></td>
            <td></td>
            <td>
                <b>Total:</b>
            </td>
            <td>
                <?php $TotalCost = 0;foreach($arr_item  as &$value){ $TotalCost += $value[3] * $value[5];} echo '<b>'.number_format($TotalCost,2).'</b>'; ?>
            </td>
        </tr>
    </tfoot>


    <tbody>
        <?php foreach($arr_item  as &$value) { ?>
        <tr>
            <td>
                <div>
                    <button type="button" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#myModal" <?php if($value[4] <= 0) echo "disabled" ?>></button>
                </div>
            </td>
            <td id="RowId" class="clsRowId">
                <?php echo $value[0] ?>
            </td>
            <td>
                <?php echo $value[1] ?>
            </td>
            <td>
                <?php echo $value[2] ?>
            </td>
            <td>
                <?php echo number_format(($value[3] == null ? 0 : $value[3]),2) ?>
            </td>
            <td>
                <?php echo $value[4] ?>
            </td>
            <td>
                <?php echo $value[5] ?>
            </td>
            <td>
                <?php echo  $value[6] ?>
            </td>
            <td>
                <?php echo number_format(($value[5] == null ? 0 :$value[3] * $value[5]),2) ?>
            </td>
        </tr><?php  } ?>
    </tbody>
</table>

<script>

    $(document).ready(function () {
        $(".EditBtn").click(function () {
             
            $('.loader').show();

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();


            $.ajax({
                url: "create_grn.php",
                type: "POST",
                data: '_RowNo=' + RowNo.trim(),
                success: function (result) {
                    //$('#modal-container').html(result);
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Good Received Item');
                    $('.modal-body').html(modalBody);
                    $('.loader').fadeOut();
                }
            });


        });
    });

</script>