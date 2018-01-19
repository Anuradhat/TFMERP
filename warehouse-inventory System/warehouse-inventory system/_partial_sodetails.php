
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Serial Code</th>
            <th>Stock Code</th>
            <th>Product Description</th>
            <th>Sale Price</th>
            <th>Qty</th>
            <th>Amount</th>
        </tr>
    </thead>
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
                <?php  echo number_format(($value[5] == null ? 0 : $value[5]),2) ?>
            </td>
        </tr><?php  } ?>
    </tbody>
</table>


<script>
//Item Delete
    $(document).ready(function () {
        $(".DeleteBtn").click(function () {
            var $row = $(this).closest("tr");
            var prodcode = $row.find(".clsRowId").text().trim();
            $.ajax({
                url: "create_salesorder.php",
                type: "POST",
                data: { "_prodcode": prodcode },
                success: function (result) {
                    $('#table').html(result);
                }
            });


        });
    });


</script>