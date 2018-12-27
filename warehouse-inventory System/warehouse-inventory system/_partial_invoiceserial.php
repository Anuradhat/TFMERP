<?php
$arr_serial = array();
$arr_item = array();
$ProductCode = $_SESSION['ProductCode'];

if($_SESSION['details'] != null) $arr_item = $_SESSION['details'];
$arr_serial =  ArraySearch($arr_item,$ProductCode)[6];

?>

<form method="post" action="create_invoice.php">
    <!-- /.box-header -->
    <input type="hidden" name="Edit" value="Edit" />

    <div class="box-body">
        <table id="tblInvoiceSerial" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Serial Number</th>
                </tr>
            </thead>
            <tbody>
                <?php for($count = 1;$count <= $_SESSION['InvQty'];$count++) { ?>
                <tr>
                    <td class="clsProductCode">
                        <input type="text" class="form-control col-xs-3 input-sm" name="ProductCode" value="<?php echo $_SESSION['ProductCode'] ?>" required="required" readonly="readonly" disabled />
                    </td>
                    <td class="clsSerialId">
                        <input type="text" class="form-control col-xs-3 input-sm txtSerail" name="SerialCode" value="<?php if($arr_serial[$count-1] != null)  echo $arr_serial[$count-1] ?>" placeholder="Serial Code" required="required" readonly="readonly" autocomplete="off" onkeyup="TextBoxKeyUp(this);" />
                    </td>
                    <td class="clsLocationCode" style="display:none">
                        <input type="text" class="form-control col-xs-3 input-sm" name="LocationCode" value="<?php echo $_SESSION['LocationCode'] ?>" placeholder="Location Code" disabled />
                    </td>
                </tr><?php  } ?>
            </tbody>
        </table>
    </div>
</form>
<!-- /.box -->

<script type="text/javascript">
    var arr = new Array();
    var AllSerailsAreValid = true;
    var ProductCode = "";
    var LocationCode = "";


    function EditItem(ctrl, event) {
        event.preventDefault();
        $('.loader').show();

        var i = 0;
        
        var AllSerailsAreFilled = true;
        var completeAll = false;
        var completecount = 0;

        AllSerailsAreValid = true;
        arr = [];


        $("#tblInvoiceSerial tr").each(function () {
            $('td', this).each(function () {

                if ($(this).attr("class") == "clsProductCode") {
                    ProductCode = $(this).find(":input").val().trim();
                }


                if ($(this).attr("class") == "clsSerialId") {
                    var value = $(this).find(":input").val().trim();
                    arr.push(value);
                }

                if ($(this).attr("class") == "clsLocationCode") {
                    LocationCode = $(this).find(":input").val().trim();
                }
            });
        });


        for (i = 0; i < arr.length; i++) {
            var value = arr[i];
            
            if (value == "") {
                AllSerailsAreFilled = false;
                $('.loader').fadeOut();
                bootbox.alert('Some serial details are missing.');
                break;
            }
        }

        if (AllSerailsAreFilled && arrHasDupes(arr))
        {
            AllSerailsAreFilled = false;
            $('.loader').fadeOut();
            bootbox.alert('Duplicate serial numbers found!');
        }

        if (AllSerailsAreFilled == true) {
            for (i = 0; i < arr.length; i++) {
                var value = arr[i];
                $.ajax({
                    url: 'autocomplete.php',
                    type: 'POST',
                    data: { V3: 'V3', ProductCode: ProductCode, SerialNo: value, LocationCode: LocationCode },
                    success: function (data) {
                        if (data.trim() == "false")
                        {
                            AllSerailsAreValid = false;
                        }
                    },
                    complete: function (data) {
                        completecount++;
                        if (completecount == arr.length)
                            competeAjax();
                    }
                });
            }
        }

    }


    function competeAjax()
    {
        if (!AllSerailsAreValid) {
            $('.loader').fadeOut();
            bootbox.alert('Some serials detail(s) are invalid.');
        }
        else {
            //bootbox.alert('Sucesss.');
            //var JSONArray = JSON.stringify(arr);

            $.ajax({
                url: 'create_invoice.php',
                type: 'POST',
                data: { ProductCode: ProductCode, 'arr': arr },
                success: function (data) {
                    $('#myModal').modal('toggle');
                    $('.loader').fadeOut();
                }
            });
        }
    }

    function arrHasDupes(A) {                         
        var i, j, n;
        n = A.length;
        // to ensure the fewest possible comparisons
        for (i = 0; i < n; i++) {                        
            for (j = i + 1; j < n; j++) {         
                if (A[i] == A[j]) return true;
            }
        }
        return false;
    }


    $(document).ready(function () {
        $('input:text:first').focus();
        $('input:text').bind("keydown", function (e) {
            var n = $("input:text").length;
            if (e.which == 13) { //Enter key
                e.preventDefault(); //Skip default behavior of the enter key
                var nextIndex = $('input:text').index(this) + 1;
                if (nextIndex < n)
                    $('input:text')[nextIndex].focus();
                else {
                    $('input:text')[nextIndex - 1].blur();
                    $('#btnSubmit').click();
                }
            }
        });
    });

    var $quan = $('.txtSerail');
    $('.txtSerail').keyup(function (e) {
        if (e.keyCode == 13) {
            var ind = $quan.index(this);
            $quan.eq(ind + 1).focus();
            setTimeout(function () { $quan.eq(ind + 1).select(); }, 100);
        }
    });
         
</script>