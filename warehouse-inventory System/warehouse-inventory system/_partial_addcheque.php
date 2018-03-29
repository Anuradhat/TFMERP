<?php
require_once('includes/load.php');
$all_banks = find_by_sql("call spSelectAllBanks();");
?>


<script>
    var dict = []; // create an empty array
</script>

 <div class="box-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <input type="text" class="form-control integer" id="chequenumber" placeholder="Cheque Number" autocomplete="off" />
                </div>

                <div class="form-group">
                    <input type="text" class="form-control decimal text-right" id="Value" placeholder="Payment (Rs)" autocomplete="off" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <select class="form-control select2" style="width: 100%;" name="BankCode" id="BankCode" required="required">
                        <option value="">Select Bank</option><?php  foreach ($all_banks as $bank): ?>
                        <option value="<?php echo $bank['BankCode'] ?>"><?php echo $bank['BankName'] ?>
                        </option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <input type="text" class="form-control" autocomplete="off" name="ChequeDate" id="datepicker" placeholder="Cheque Date" />
                </div>

                <div class="form-group pull-right">
                    <button type="button" class="btn btn-success add-row" value="Add">&nbsp;Add&nbsp;&nbsp;</button>
                </div>
            </div>
        </div>
    </div>

    <?php  foreach($arr_cheque  as &$value) { ?>
       <script> dict.push({ key: '<?php echo $value['key']?>',bank: '<?php echo $value['bank']?>',date: '<?php echo $value['date']?>',value: '<?php echo $value['value']?>' }); </script>
    <?php  } ?>

    <table id="tblCheque" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Action</th>
                <th>Cheque Number</th>
                <th>Bank</th>
                <th>Cheque Date</th>
                <th>Payment (Rs)</th>
            </tr>
        </thead>
        <tfoot>
        </tfoot>
        <tbody><?php  foreach($arr_cheque  as &$value) { ?>
            <tr>
                <td>
                    <div>
                        <button type="button" class="btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn" id="btnDelete"></button>
                    </div>
                </td>       
                <td id="RowId" class="clsRowId"><?php echo $value["key"] ?></td>
                <td><?php echo $value["bank"] ?></td>
                <td><?php echo $value["date"] ?></td>
                <td><?php echo number_format($value["value"],2) ?>
                </td>
            </tr> <?php  } ?>
        </tbody>
    </table>

    <script>
        //Item Delete

       // var dict = []; // create an empty array

        $(document).ready(function () {
            $(".DeleteBtn").click(function () {
                var $row = $(this).closest("tr");
                var chequecode = $row.find(".clsRowId").text().trim();

                var index = 0;

                for (var i = 0; i < dict.length ; i++) {
                    if (dict[i].key === chequecode) {
                        index = i;
                        break;
                    }
                }

                dict.splice(index, 1);

                ///if (dict.length == 0)
                //    dict = [0];

                $(this).parents("tr").first().remove();
            });
        });


        $(document).ready(function () {
            $(".add-row").click(function () {
                var ChequeNumber = $("#chequenumber").val();
                var BankCode = $("#BankCode").val();
                var ChequeDate = $("#datepicker").val();
                var ChequeValue = $("#Value").val();

                if (ChequeNumber == "")
                {
                    bootbox.alert('Please enter cheque number.');
                }
                else if (BankCode == "")
                {
                    bootbox.alert('Please select bank.');
                }
                else if (ChequeValue <= 0) {
                    bootbox.alert('Please enter amount.');
                }
                else {
                    var markup = "<tr><td><button type='button' class='btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn' id='btnDelete'></button></td><td class='clsRowId'>" + ChequeNumber + "</td><td>" + BankCode + "</td><td>" + ChequeDate + "</td><td>" + parseFloat(ChequeValue).toFixed(2) + "</td></tr>";
                    $("#tblCheque tbody").append(markup);

                    dict.push({ key: ChequeNumber,bank: BankCode,date: ChequeDate, value: ChequeValue });


                    $("#chequenumber").val('');
                    $('#BankCode').val('').trigger('change');
                    $("#datepicker").val('');
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


    function EditItem(ctrl, event)
    {
        $('.loader').show();

        $.ajax({
            url: "autocomplete.php",
            type: "POST",
            data: { "chequearr": dict.length == 0 ? null : dict },
            success: function (data) {
                var val = (data == null || data === "" ? 0.00 : data);
  
                $('#ChequePayment').val(parseFloat(val).toFixed(2));
                CalculateCreditDue();
                $('#myModal').modal('toggle');
                $('.loader').fadeOut();
            }
        });
    }


    //Initialize Date picker
    $('#datepicker').datepicker({
        format: 'yyyy/mm/dd',
        startDate: '-1d',
        autoclose: true
    })


    function CalculateCreditDue() {
        $('.loader').show();

        var NetAmount = $("#hNetAmount").val() == "" ? 0 : $("#hNetAmount").val();
        var CashValue = $("#CashPayment").val() == "" ? 0 : $("#CashPayment").val();
        var CardValue = $("#CardPayment").val() == "" ? 0 : $("#CardPayment").val();
        var ChequeValue = $("#ChequePayment").val() == "" ? 0 : $("#ChequePayment").val();
        var TransferValue = $("#BankTransferPayment").val() == "" ? 0 : $("#BankTransferPayment").val();

        var Credit = (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue))) < 0 ? 0 : (parseFloat(NetAmount) - (parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue)));
        $("#Credit").val((Credit).toFixed(2));

        var TotalPayment = parseFloat(CashValue) + parseFloat(CardValue) + parseFloat(ChequeValue) + parseFloat(TransferValue);
        $("#TotalPayment").val((TotalPayment).toFixed(2));

        $('.loader').fadeOut();
    }
</script>
