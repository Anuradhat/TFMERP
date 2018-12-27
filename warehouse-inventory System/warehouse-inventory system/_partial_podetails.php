
<table id="table" class="table table-bordered table-striped datatable">
    <thead>
        <tr>
            <th>Action</th>
            <th>Product Code</th>
            <th>Product Description</th>
            <th>Cost Price</th>
            <th>Qty</th>
            <th>Tax Rate</th>
            <th>Tax Amount</th>
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
            <td>
                <b>Total:</b>
            </td>
            <td class="sumofammount">
                <?php $TotalCost = 0;foreach($arr_item  as &$value){ $TotalCost += $value[2] * $value[3] + $value[5];} echo '<b>'.number_format($TotalCost,2).'</b>'; ?>
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
                <input type="text" value="<?php echo ($value[2] == null ? 0 : $value[2]) ?>" id="txtCostPrice" class="CostPrice" style="width:100px;"  />                
            </td>
            <td>
                <input type="text" value="<?php echo $value[3] ?>" id="txtQty" class="Qty" style="width:100px;" />                
            </td>
            <td>
                <?php echo $value[4] ?>
                <input type="checkbox" id="chkTaxAllow" class="TaxAllow" <?php echo ($value[5] <= 0? "":"checked") ?> />
            </td>
            <td>
                <?php echo $value[5] ?>
            </td>
            <td class="ammount">
                <?php echo ($value[2] == null ? 0 :$value[2] * $value[3] + $value[5]) ?>
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

    $(document).ready(function () {
        $("#table").on('keyup', '.CostPrice', function () {

            $('#btnApprove').prop("disabled", true);

            var currentRow = $(this).closest("tr");

            var ProductCode = jQuery.trim(currentRow.find("td:eq(1)").text());

            var avgcost = parseFloat(jQuery.trim(currentRow.find("td:eq(3) #txtCostPrice").val()));
            var Qty = parseFloat(jQuery.trim(currentRow.find("td:eq(4) #txtQty").val()));
            var taxAmount = parseFloat(currentRow.find("td:eq(6)").text());
            var taxRate = parseFloat(jQuery.trim(currentRow.find("td:eq(5)").text()));
            var taxAllow = jQuery.trim(currentRow.find("td:eq(5) #chkTaxAllow").prop('checked'))


            if (taxAllow == "false") {
                taxAmount = 0;
                taxRate = 0;
            }
            else {
                taxAmount = ((avgcost * Qty) * taxRate) / 100;
            }


            currentRow.find("td:eq(6)").text((((avgcost * Qty) * taxRate) / 100).toFixed(2));

            currentRow.find("td:eq(7)").text(((avgcost * Qty) + taxAmount).toFixed(2));



            sumItemAmmount();
        })
    });

    $(document).ready(function () {
        $("#table").on('keyup', '.Qty', function () {

            $('#btnApprove').prop("disabled", true);

            var currentRow = $(this).closest("tr");

            var ProductCode = jQuery.trim(currentRow.find("td:eq(1)").text());

            var avgcost = parseFloat(jQuery.trim(currentRow.find("td:eq(3) #txtCostPrice").val()));
            var Qty = parseFloat(jQuery.trim(currentRow.find("td:eq(4) #txtQty").val()));
            var taxAmount = parseFloat(currentRow.find("td:eq(6)").text());
            var taxRate = parseFloat(jQuery.trim(currentRow.find("td:eq(5)").text()));
            var taxAllow = jQuery.trim(currentRow.find("td:eq(5) #chkTaxAllow").prop('checked'))


            if (taxAllow == "false") {
                taxAmount = 0;
                taxRate = 0;
            }
            else {
                taxAmount = ((avgcost * Qty) * taxRate) / 100;
            }


            currentRow.find("td:eq(6)").text((((avgcost * Qty) * taxRate) / 100).toFixed(2));

            currentRow.find("td:eq(7)").text(((avgcost * Qty) + taxAmount).toFixed(2));



            sumItemAmmount();
        })
    });

    $(document).ready(function () {
        $("#table").on('click', '.TaxAllow', function () {

            $('#btnApprove').prop("disabled", true);

            var currentRow = $(this).closest("tr");

            var ProductCode = jQuery.trim(currentRow.find("td:eq(1)").text());

            var avgcost = parseFloat(jQuery.trim(currentRow.find("td:eq(3) #txtCostPrice").val()));
            var Qty = parseFloat(jQuery.trim(currentRow.find("td:eq(4) #txtQty").val()));
            var taxAmount = parseFloat(currentRow.find("td:eq(6)").text());
            var taxRate = parseFloat(jQuery.trim(currentRow.find("td:eq(5)").text()));
            var taxAllow = jQuery.trim(currentRow.find("td:eq(5) #chkTaxAllow").prop('checked'))


            if (taxAllow == "false") {
                taxAmount = 0;
                taxRate = 0;
            }
            else {
                taxAmount = ((avgcost * Qty) * taxRate) / 100;
            }


            currentRow.find("td:eq(6)").text((((avgcost * Qty) * taxRate) / 100).toFixed(2));

            currentRow.find("td:eq(7)").text(((avgcost * Qty) + taxAmount).toFixed(2));



            sumItemAmmount();
        })
    });

    $(document).ready(function () {
        $("#btnUpdateChanges").click(function () {
            $("#table tr").each(function () {
                //var $row = $(row);
                $this = $(this);
                var ProductCode = jQuery.trim($this.find('td:eq(1)').text());
                var avgcost = parseFloat($this.find("td:eq(3) #txtCostPrice").val());
                var Qty = parseFloat($this.find("td:eq(4) #txtQty").val());
                var taxAmount = parseFloat($this.find("td:eq(6)").text());
                var TaxRate = parseFloat($this.find("td:eq(5)").text());

                if (ProductCode.length) {
                    addDetailsToArr(ProductCode, avgcost, taxAmount, Qty, TaxRate);
                }
            })
        })
    })

    function sumItemAmmount() {
        var sum = 0;
        $(".ammount").each(function () {
            var value = $(this).text();
            if (!isNaN(value) && value.length != 0) {
                sum += parseFloat(value);
            }
        })
        $(".sumofammount").text(sum.toFixed(2));
    };

    function addDetailsToArr(pProductCode, pAverageCost, pTaxAmmount, pQty, pTax) {
        $('.loader').show();

        //var SalePrice = pSalePrice;
        var Qty = parseFloat(pQty == null || pQty == "" ? 0 : pQty);
        var ProductCode = pProductCode;
        var Tax = parseFloat(pTax == null || pTax == "" ? 0 : pTax);;
        var AverageCost = parseFloat(pAverageCost == null || pAverageCost == "" ? 0 : pAverageCost);
        var TaxAmmount = parseFloat(pTaxAmmount == null || pTaxAmmount == "" ? 0 : pTaxAmmount);
        

        if (AverageCost <= 0) {
            //$("#pSalePrice").focus();
            bootbox.alert('You enter sale price is invalid.');
        }
        else if (Qty <= 0) {
            //$("#pQty").focus();
            bootbox.alert('You enter qty is invalid.');
        }
        else {
            setTimeout(function () {
                $.ajax({
                    async: false,
                    url: "edit_po_.php",
                    type: "POST",
                    data: { Edit2: 'Edit', ProductCode: ProductCode, Qty: Qty, AverageCost: AverageCost, TaxAmmount: TaxAmmount, Tax: Tax },
                    success: function (result) {
                        $("#table").html("");
                        $("#table").html(result);
                        //$('#myModal').modal('toggle');
                    },
                    complete: function () {
                        $('#btnApprove').prop("disabled", false);
                        $('.loader').fadeOut();
                    }
                });
            }, 5);

        }
    }


</script>