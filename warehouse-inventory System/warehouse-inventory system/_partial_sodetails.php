
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Product Code</th>
            <th>Product Description</th>
            <th>Average Cost</th>
            <th>Sales(%)</th>
            <th>Sale Price</th>
            <th>Qty</th>
            <th>Tax Amount</th>
            <th>Amount</th>
            <th>Tax Rate</th>
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
            <td class="sumofammount">
                <?php $TotalAmount = 0;foreach($arr_item  as &$value){ $TotalAmount += $value[4];} echo '<b>'.number_format($TotalAmount,2).'</b>'; ?>
            </td>
            <td>

            </td>
        </tr>
    </tfoot>
    <tbody>
        <?php  foreach($arr_item  as &$value) { ?>
        <tr>
            <td>
                <div>
                    <button type="button" class="EditBtn btn btn-warning btn-xs glyphicon glyphicon-edit" data-toggle="modal" data-target="#myModal" contenteditable="false"></button>
                    <button type="button" class="btn btn-danger btn-xs glyphicon glyphicon-trash DeleteBtn" id="btnDelete"></button>
                </div>
            </td>
            <td id="RowId" class="clsRowId">
                <?php echo $value[0] ?>
            </td>
            <td>
                <?php echo $value[1] ?>
            </td>
            <td id="avgCost" class="avgCost">
                <?php  echo ($value[7] == null ? 0 : $value[7]) ?><!--Average Cost -->
            </td>
            <td >
                <input type="text" value="<?php echo $value[8] == null ? 0 : $value[8] ?>" id="saplePer" class="saplePer" style="width:100px;"/>
                <!--Sales(%) -->
            </td>
            <td>
                <input type="text" value="<?php echo ($value[2] == null ? 0 : $value[2]) ?>" id="saplesPrice" class="saplesPrice" style="width:100px;" />                
            </td>
            <td>
                <?php echo $value[3] ?>
            </td>
            <td>
                <input type="text" value="<?php  echo ($value[5] == null ? 0 : $value[5]) ?>" id="txAmount" class="txAmount" style="width:100px;" disabled="disabled" />
                
            </td>
            <td class="ammount">
                <?php  echo ($value[4] == null ? 0 : $value[4]) ?>
            </td>
            <td class="TaxRate">
                <?php  echo ($value[9] == null ? 0 : $value[9]) ?>
                <input type="checkbox" id="chkTaxAllow" class="TaxAllow" <?php echo ($value[5] <= 0? "":"checked") ?> />
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
                data: { "_productcode": prodcode },
                success: function (result) {
                    $('#table').html(result);
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
                url: "edit_salesorder_.php",
                type: "POST",
                data: '_RowNo=' + RowNo.trim(),
                success: function (result) {
                    var modalBody = $('<div id="modalContent"></div>');
                    modalBody.append(result);
                    $("#myModalLabel").text('Quotation Item');
                    $('.modal-body').html(modalBody);
                },
                complete: function (result) {
                    $('.loader').fadeOut();
                }
            });


        });
    });

    $(document).ready(function () {
        $("#table").on('keyup', '.saplePer', function () {

            $('#btnApprove').prop("disabled", true);

            var currentRow = $(this).closest("tr");

            var ProductCode = jQuery.trim(currentRow.find("td:eq(1)").text());
            
            var avgcost = parseFloat(jQuery.trim(currentRow.find("td:eq(3)").text()));
            var salesPer = parseFloat(currentRow.find("td:eq(4) #saplePer").val());
            var Qty = parseFloat(jQuery.trim(currentRow.find("td:eq(6)").text()));
            var taxAmount = parseFloat(currentRow.find("td:eq(7) #txAmount").val());
            var taxRate = parseFloat(jQuery.trim(currentRow.find("td:eq(9)").text()));
            var taxAllow = jQuery.trim(currentRow.find("td:eq(9) #chkTaxAllow").prop('checked'))
            var SalesPrice = avgcost + ((avgcost / 100) * salesPer);

            if (taxAllow == "false") {
                taxAmount = 0;
                taxRate = 0;
            }

            if (salesPer > 0) {
                currentRow.find("td:eq(5) #saplesPrice").val(SalesPrice);
            }
            else {
                currentRow.find("td:eq(5) #saplesPrice").val(0);
            }
            
            if (salesPer > 0) {
                currentRow.find("td:eq(8)").text(((avgcost + ((avgcost / 100) * salesPer)) * Qty) + taxAmount);
            }
            else {
                currentRow.find("td:eq(8)").text(0);
            }
            

            if (salesPer > 0) {
                currentRow.find("td:eq(7) #txAmount").val((((SalesPrice * Qty) * taxRate) / 100).toFixed(2));
            }
            else {
                currentRow.find("td:eq(7) #txAmount").val(0);
            }
            

            sumItemAmmount();

            //addDetailsToArr(ProductCode, SalesPrice, avgcost, salesPer, Qty, taxAmount);
        })
    })

    $(document).ready(function () {
        $("#table").on('keyup', '.saplesPrice', function () {

            $('#btnApprove').prop("disabled", true);

            var currentRow = $(this).closest("tr");

            var avgcost = parseFloat(jQuery.trim(currentRow.find("td:eq(3)").text()));
            var saplesPrice = parseFloat(currentRow.find("td:eq(5) #saplesPrice").val());
            var saplePer = parseFloat(currentRow.find("td:eq(4) #saplePer").val());
            var Qty = parseFloat(jQuery.trim(currentRow.find("td:eq(6)").text()));
            
            var taxRate = parseFloat(jQuery.trim(currentRow.find("td:eq(9)").text()));
            var taxAllow = jQuery.trim(currentRow.find("td:eq(9) #chkTaxAllow").prop('checked'))

            if (taxAllow == "false") {
                taxAmount = 0;
                taxRate = 0;
            }


            if (saplesPrice > 0) {
                currentRow.find("td:eq(7) #txAmount").val((((saplesPrice * Qty) * taxRate) / 100).toFixed(2));
            }
            else {
                currentRow.find("td:eq(7) #txAmount").val(0);
            }

            
            var taxAmount = parseFloat(currentRow.find("td:eq(7) #txAmount").val());

            if (avgcost > 0) {
                currentRow.find("td:eq(4) #saplePer").val((((saplesPrice - avgcost) / avgcost) * 100).toFixed(2));
            }
            else {
                currentRow.find("td:eq(4) #saplePer").val(0);
            }
            

            if (saplesPrice > 0) {
                currentRow.find("td:eq(8)").text(((saplesPrice * Qty) + taxAmount).toFixed(2));
            }
            else {
                currentRow.find("td:eq(8)").text(0);
            }

            

            sumItemAmmount();
        })
    })

    $(document).ready(function () {
        $("#table").on('keyup', '.txAmount', function () {
            var currentRow = $(this).closest("tr");

            var avgcost = parseFloat(currentRow.find("td:eq(3)").text());
            var saplesPrice = parseFloat(currentRow.find("td:eq(5) #saplesPrice").val());
            var saplePer = parseFloat(currentRow.find("td:eq(4) #saplePer").val());
            var Qty = currentRow.find("td:eq(6)").text();
            var taxAmount = parseFloat(currentRow.find("td:eq(7) #txAmount").val());

            if (avgcost > 0) {
                currentRow.find("td:eq(4) #saplePer").val((((saplesPrice - avgcost) / avgcost) * 100).toFixed(2));
            }
            else {
                currentRow.find("td:eq(4) #saplePer").val(0);
            }

            
            currentRow.find("td:eq(8)").text(((saplesPrice * Qty) + taxAmount).toFixed(2));

            sumItemAmmount();
        })
    })

    $(document).ready(function () {
        $("#table").on('click', '.TaxAllow', function () {

            $('#btnApprove').prop("disabled", true);

            var currentRow = $(this).closest("tr");

            var avgcost = parseFloat(jQuery.trim(currentRow.find("td:eq(3)").text()));
            var saplesPrice = parseFloat(currentRow.find("td:eq(5) #saplesPrice").val());
            var saplePer = parseFloat(currentRow.find("td:eq(4) #saplePer").val());
            var Qty = parseFloat(jQuery.trim(currentRow.find("td:eq(6)").text()));

            var taxRate = parseFloat(jQuery.trim(currentRow.find("td:eq(9)").text()));
            var taxAllow = jQuery.trim(currentRow.find("td:eq(9) #chkTaxAllow").prop('checked'))

            if (taxAllow == "false") {
                taxAmount = 0;
                taxRate = 0;
            }

            currentRow.find("td:eq(7) #txAmount").val((((saplesPrice * Qty) * taxRate) / 100).toFixed(2));
            var taxAmount = parseFloat(currentRow.find("td:eq(7) #txAmount").val());

            if (avgcost > 0) {
                currentRow.find("td:eq(4) #saplePer").val((((saplesPrice - avgcost) / avgcost) * 100).toFixed(2));
            }
            else {
                currentRow.find("td:eq(4) #saplePer").val(0);
            }

            currentRow.find("td:eq(8)").text(((saplesPrice * Qty) + taxAmount).toFixed(2));

            sumItemAmmount();
        })
    })

    $(document).ready(function () {
        $("#btnUpdateChanges").click(function () {
            $("#table tr").each(function () {
                //var $row = $(row);
                $this = $(this);
                var ProductCode = jQuery.trim($this.find('td:eq(1)').text());
                var avgcost = parseFloat($this.find("td:eq(3)").text());
                var salesPer = parseFloat($this.find("td:eq(4) #saplePer").val());
                var Qty = parseFloat($this.find("td:eq(6)").text());
                var taxAmount = parseFloat($this.find("td:eq(7) #txAmount").val());
                var SalesPrice = parseFloat($this.find("td:eq(5) #saplesPrice").val());

                if (ProductCode.length) {
                    addDetailsToArr(ProductCode, SalesPrice, avgcost, salesPer, Qty, taxAmount);
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
    }

    function addDetailsToArr(pProductCode, pSalePrice, pAverageCost, pSalesPercentage, pQty, pTax) {
        //$('button').prop("disabled", true);
        $('.loader').show();

        var SalePrice = pSalePrice;
        var Qty = pQty;
        var ProductCode = pProductCode;
        var Tax = pTax;
        var AverageCost = parseFloat(pAverageCost == null || pAverageCost == "" ? 0 : pAverageCost);
        var SalesPercentage = parseFloat(pSalesPercentage == null || pSalesPercentage == "" ? 0 : pSalesPercentage);
        var ExcludeTax = 'false';

        if (Tax == 0) {
            ExcludeTax = 'true';
        }
        else {
            ExcludeTax = 'false';
        }

        if (SalePrice <= 0) {
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
                    url: "edit_salesorder_.php",
                    type: "POST",
                    data: { Edit2: 'Edit', ProductCode: ProductCode, Qty: Qty, SalePrice: SalePrice, ExcludeTax: ExcludeTax, AverageCost: AverageCost, SalesPercentage: SalesPercentage, Tax: Tax },
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