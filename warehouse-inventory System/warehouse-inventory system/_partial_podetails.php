
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Product Code</th>
            <th>Product Description</th>
            <th>Cost Price</th>
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
            <td>
                <b>Total:</b>
            </td>
            <td>
                <?php $TotalCost = 0;foreach($arr_item  as &$value){ $TotalCost += $value[2] * $value[3];} echo '<b>'.number_format($TotalCost,2).'</b>'; ?>
            </td>
        </tr>
    </tfoot>
    <tbody>
        <?php  foreach($arr_item  as &$value) { ?>
        <tr>
            <td>
                <div>
                    <button type="button" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#myModal" contenteditable="false"></button>
                     
                    <button type="button" class="btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn" id="btnDelete" <?php   if(remove_junk($arr_header[4]) == "1") echo "disabled" ?>></button>
                
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
            <td>
                <?php echo $value[3] ?>
            </td>
            <td>
                <?php echo number_format(($value[2] == null ? 0 :$value[2] * $value[3]),2) ?>
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
                url: "create_po.php",
                type: "POST",
                data: { "_prodcode": prodcode },
                success: function (result) {
                    $('#table').html(result);
                },
                complete: function (result) {
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
                url: "create_po.php",
                type: "POST",
                data: '_RowNo=' + RowNo.trim(),
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Purchase Order Item');
                    $('.modal-body').html(modalBody);
                },
                complete: function (result) {
                    $('.loader').fadeOut();
                }
            });


        });
    });


</script>