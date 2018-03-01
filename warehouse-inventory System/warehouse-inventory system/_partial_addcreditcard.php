<!-- Card Js -->
<link rel="stylesheet" href="libs/bower_components/cardjs/card-js.min.css" />
<!-- Card js -->
<script src="libs/bower_components/cardjs/card-js.min.js"></script>

<script>
    var dict = []; // create an empty array
</script>

 <div class="box-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card-js form-group">
                     <!--<input type="text" class="form-control integer" id="card-number" placeholder="Card Number (XXXX-XXXX-XXXX-XXXX)" />-->
                    <input class="card-number form-control" name="my-custom-form-field__card-number" placeholder="Enter your card number"
                        id="CardNumber" autocomplete="off" required />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <input type="text" class="form-control decimal text-right" id="Value" placeholder="Payment (Rs)" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <button type="button" class="btn btn-success add-row" value="Add">&nbsp;Add&nbsp;&nbsp;</button>
                </div>
            </div>
        </div>
    </div>

    <?php  foreach($arr_card  as &$value) { ?>
        <script> dict.push({ key: '<?php echo $value['key']?>', value: '<?php echo $value['value']?>' }); </script>
    <?php  } ?>

    <table id="tblCard" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Action</th>
                <th>Card Number</th>
                <th>Payment (Rs)</th>
            </tr>
        </thead>
        <tfoot>
        </tfoot>
        <tbody><?php  foreach($arr_card  as &$value) { ?>

            <tr>
                <td>
                    <div>
                        <button type="button" class="btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn" id="btnDelete"></button>
                    </div>
                </td>



                <td id="RowId" class="clsRowId"><?php echo $value["key"] ?></td>
                <td><?php echo number_format($value["value"],2) ?></td>
            </tr> <?php  } ?>
        </tbody>
    </table>

    <script>
        //Item Delete

       // var dict = []; // create an empty array

        $(document).ready(function () {
            $(".DeleteBtn").click(function () {
                var $row = $(this).closest("tr");
                var cardcode = $row.find(".clsRowId").text().trim();

                var index = 0;

                for (var i = 0; i < dict.length ; i++) {
                    if (dict[i].key === cardcode) {
                        index = i;
                        break;
                    }
                }

                dict.splice(index, 1);

                $(this).parents("tr").first().remove();
            });
        });


        $(document).ready(function () {
            $(".add-row").click(function () {
                var CardNumber = $("#CardNumber").val();
                var CardValue = $("#Value").val();

                if (CardNumber == "")
                {
                    bootbox.alert('Please enter card number (at least first 4 digit in the card).');
                }
                else if (CardValue <= 0) {
                    bootbox.alert('Please enter amount.');
                }
                else {
                    var markup = "<tr><td><button type='button' class='btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn' id='btnDelete'></button></td><td class='clsRowId'>" + CardNumber + "</td><td>" + parseFloat(CardValue).toFixed(2) + "</td></tr>";
                    $("#tblCard tbody").append(markup);

                    dict.push({ key: CardNumber, value: CardValue });


                    $("#CardNumber").val('');
                    $("#Value").val('');
                }
            });
        });

        //Textbox integer accept
        $(".integer").keypress(function (evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        });

        //Textbox decimal accept
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
        // $('#table').DataTable();


    function EditItem(ctrl, event)
    {
        $('.loader').show();

        $.ajax({
            url: "autocomplete.php",
            type: "POST",
            data: { "arr": dict.length == 0 ? null : dict },
            success: function (data) {
                var val = (data == null || data === "" ? 0.00 : data);
                $('#CardPayment').val(parseFloat(val).toFixed(2));
                CalculateCreditDue();
                $('#myModal').modal('toggle');
                $('.loader').fadeOut();
            }
        });
    }

    function CalculateCreditDue() {
        $('.loader').show();

        var NetAmount = $("#hNetAmount").val() == "" ? 0 : $("#hNetAmount").val();
        var CashValue = $("#CashPayment").val() == "" ? 0 : $("#CashPayment").val();
        var CardValue = $("#CardPayment").val() == "" ? 0 : $("#CardPayment").val();
        var ChequeValue = $("#ChequePayment").val() == "" ? 0 : $("#ChequePayment").val();
        var TransferValue = $("#BankTransferPayment").val() == "" ? 0 : $("#BankTransferPayment").val();

        var Credit = (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue))) < 0 ? 0 : (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue)));
        $("#Credit").val((Credit).toFixed(2));

        $('.loader').fadeOut();
    }
</script>
