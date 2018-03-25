

    <thead>
        <tr>
            <th>Action</th>
            <th>Stock Code</th>
            <th>Product Description</th>
            <th>Cost Price</th>
            <th>Expire Date</th>
            <th>SIH</th>
            <th>Trn. Qty</th>
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
            <td>
                <b>Total Trn. Qty:</b>
            </td>
            <td>
                <?php $TotalTrnQty = 0;foreach($arr_item  as &$value){ $TotalTrnQty += $value[6];} echo '<b>'.$TotalTrnQty.'</b>'; ?>
            </td>
        </tr>
    </tfoot>


    <tbody>
        <?php foreach($arr_item  as &$value) { ?>
        <tr>
            <td>
                <div>
                    <!--<button type="button" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#modal-container"></button>-->
                    <button type="button" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#myModal"></button>
                    <button type="button" class="btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn" id="btnDelete"></button>
                </div>
            </td>
            <td id="RowId" class="clsRowId">
                <?php echo $value[0] ?>
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
            <td class="clsTrnQty">
                <?php echo  $value[6] ?>
            </td>
            <td>
                <div>
                    <button type="button" class="SerialBtn btn btn-primary btn-xs glyphicon glyphicon-list-alt" data-toggle="modal" data-target="#myModal"></button>
                </div>
            </td>
        </tr><?php  } ?>
    </tbody>


<script>

    //Item Delete
    $(document).ready(function () {
        $(".DeleteBtn").click(function () {
            var $row = $(this).closest("tr");
            var stockcode = $row.find(".clsRowId").text().trim();
            $.ajax({
                url: "create_transfernote.php",
                type: "POST",
                data: { "_stockcode": stockcode },
                success: function (result) {
                    $('#table').html(result);
                }
            });


        });
    });



    $(document).ready(function () {
        $(".EditBtn").click(function () {

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();


            $.ajax({
                url: "create_transfernote.php",
                type: "POST",
                data: 'StockCode=' + RowNo.trim(),
                success: function (result) {
                    //$('#modal-container').html(result);
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Transfer Note Item');
                    $('.modal-body').html(modalBody);
                }
            });


        });
    });


    $(document).ready(function () {
        $(".SerialBtn").click(function () {

            var $row = $(this).closest("tr");
            var RowNo = $row.find(".clsRowId").text();
            var TrnQty = $row.find(".clsTrnQty").text();
            var LocationCode = $("#FromLocationCode").val();
            var BinCode = $("#FromBinCode").val();


            if (TrnQty == 0) {
                $('#myModal').modal('toggle');
                bootbox.alert('Transfer qty not found.');
            }
            else {
                $.ajax({
                    url: "create_transfernote.php",
                    type: "POST",
                    data: { 'StockCode': RowNo.trim(), 'TrnQty': TrnQty.trim(), 'LocationCode': LocationCode.trim(), 'BinCode': BinCode.trim() },
                    success: function (result) {
                        //$('#modal-container').html(result);
                        var modalBody = $('<div id="modalContent"></div>');
                        modalBody.append(result);
                        $("#myModalLabel").text('Serial Details');
                        $('.modal-body').html(modalBody);
                    }
                });
            }

        });
    });

</script>