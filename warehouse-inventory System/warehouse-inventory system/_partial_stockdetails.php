
<table id="table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Action</th>
            <th>Stock Code</th>
            <th>Serial Code</th>
            <th>Product Description</th>
            <th>Cost Price</th>
            <th>Sale Price</th>
            <th>Expire Date</th>
            <th>SIH</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($arr_item  as &$value) { ?>
        <tr>
            <td>
                <div>
                    <button type="button" class="SerialBtn btn btn-primary btn-xs glyphicon glyphicon-list-alt" data-toggle="modal" data-target="#myModal"></button>
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
        </tr><?php  } ?>
    </tbody>
</table>

