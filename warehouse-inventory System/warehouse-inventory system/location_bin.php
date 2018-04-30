<?php
ob_start();

$page_title = 'Bin Master';
require_once('includes/load.php');
UserPageAccessControle(1,'Stock Bin');

$all_Location = find_by_sql("call spSelectAllLocations();");

if (isset($_POST['Location'])) {

    $LocationCode = remove_junk($db->escape($_POST['Location']));

    $all_Bins = find_by_sql("call spSelectBinFromLocationCode('{$LocationCode}');");

    foreach($all_Bins as &$value){
        
        echo '<tr>
            <td>
                <div class="form-group">
                    <form method="post" action="edit_location_bin.php">
                        <button type="submit" name="Bin" class="btn  btn-warning btn-xs glyphicon glyphicon-edit"></button>
                        <input type="hidden" name="bincode" value='.remove_junk(ucfirst($value["BinCode"])).' />
                        <input type="hidden" name="locationcode" value='.remove_junk(ucfirst($value["LocationCode"])).' />
                    </form>
                    <form method="post" action="delete_location_bin.php">
                        <button type="submit" name="Bin" class="btn btn-danger btn-xs glyphicon glyphicon-trash"></button>
                        <input type="hidden" name="bincode" value='.remove_junk(ucfirst($value["BinCode"])).' />
                        <input type="hidden" name="locationcode" value='.remove_junk(ucfirst($value["LocationCode"])).' />
                        <input type="hidden" name="defaultbin" value='.remove_junk(ucfirst($value["DefaultBin"])).' />
                    </form>
                </div>
            </td>
            <td>
                '.remove_junk(ucfirst($value["BinCode"])).'
            </td>
            <td>
                '.remove_junk($value['BinDesc']).'
            </td>
            <td>
                '.remove_junk($value['DefaultBin']).'
            </td>                                        
        </tr>';
    }

    return;
}

?>

<?php include_once('layouts/header.php'); ?>



<section class="content-header">
    <h1>
        Stock Bin Details
        <small>Create Bins</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Items
            </a>
        </li>
        <li class="active">Stock Bins</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<!-- Main content -->
<section class="content">
<!-- Your Page Content Here -->
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="btn-group">
                        <button type="button" name="add_bin" onclick="window.location = 'add_location_bin.php'" class="btn btn-primary">&nbsp;&nbsp;New&nbsp;&nbsp;</button>
                        <button type="button" class="btn btn-warning" onclick="window.location = 'home.php'">Cancel  </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div id="message" class="col-md-12"><?php include('_partial_message.php'); ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Stock Bin Details</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>

   
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">

                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Location</label>
                                <select class="form-control select2" name="LocationCode" id="LocationCode" required="required" onchange="FillBins();">
                                <option value="">Select Location</option>
                                <?php foreach($all_Location as $allLocation): ?>
                                <option value=<?php echo remove_junk($allLocation['LocationCode']); ?>><?php echo remove_junk($allLocation['LocationName']); ?>
                                </option>
                             <?php endforeach; ?>
                            </select>
                            </div>
                            </div>
                            </div>
                            <table id="tblBins" class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Bin Code</th>
                                        <th>Bin Description</th>
                                        <th>Default Bin</th>                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
function FillBins() {
        var Location = $('#LocationCode').val();

        $.ajax({
            url: "location_bin.php",
            type: "POST",
            data: { Location: Location},
            success: function (result) {
                $("#tblBins tbody").html(""); // clear before appending new list
                $("#tblBins tbody").html(result);
            }
        });

    }
</script>

<?php include_once('layouts/footer.php'); ?>