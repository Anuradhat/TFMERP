 </div>
<!-- /.content-wrapper -->
<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        Anything you want
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2017 <a href="#">Techno Forms (Pvt) Ltd</a>.</strong> All rights reserved.
</footer>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
        <li class="active"><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
    </ul>


    <?php
       $date    = make_date();
       $user =  current_user();
       $RecentActivity = find_by_sql("call spSelectRecentActivity('{$user["username"]}','{$date}');");    
    ?>

    <!-- Tab panes -->
    <div class="tab-content">
        <!-- Home tab content -->
        <div class="tab-pane active" id="control-sidebar-home-tab">
            <h3 class="control-sidebar-heading">Recent Activity</h3>
            <ul class="control-sidebar-menu">
                <li>
                   <?php foreach ($RecentActivity as $act): ?>
                     <a href="javascript:;">
                        <i class="menu-icon fa fa-history bg-white"></i>
                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading"><?php echo remove_junk($act['Title']); ?></h4>
                            <p><?php echo remove_junk($act['Description']); ?></p>
                        </div>
                     </a>
                    <?php endforeach; ?>
                </li>
            </ul>

        </div>
        <!-- /.tab-pane -->
    </div>
</aside>
<!-- /.control-sidebar -->
<!-- Add the sidebar's background. This div must be placed
immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
     <!-- DataTables -->
<script src="libs/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="libs/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- FastClick -->
<script src="libs/bower_components/fastclick/lib/fastclick.js"></script>
<!-- Bootstrap slider -->
<script src="libs/bower_components/bootstrap-slider/bootstrap-slider.js"></script>
<!-- Select2 -->
<script src="libs/bower_components/select2/dist/js/select2.full.min.js"></script>

<!-- AdminLTE App -->
<script src="libs/dist/js/adminlte.min.js"></script> 

<!--<div id="modal-container" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

        </div>
    </div>
</div>-->

<div class="modal fade bd-example-modal-lg" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content"></div>
    </div>
    <div class="modal-dialog">
        <div class="modal-content"></div>
    </div>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="">x </span>
                    <span class="sr-only">Close</span>

                </button>
                <h4 class="modal-title" id="myModalLabel">Loading...</h4>

            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="EditItem(this, event);">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        //$('.table').DataTable();
        $('.slider').slider();
  })
</script>

<script>
    $(function () {
        //Initialize Select2 Elements
        $('.select2').select2();

        //$("#tblBinDetails").DataTable({ destroy: true });

        $('.datatable').DataTable({
            responsive: true
        });
    });


  setInterval(function () {
      $('#tasksmenu').load('_partial_pendingtask.php');
  }, 60000);

  //setInterval(function () {
  //    $('#message').load('_partial_message.php');
  //}, 1000);
</script>

<script>
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


    $(document).on('submit', 'form', function () {

        //var buttons = $(this).find('[type="submit"]');
        //buttons.each(function (btn) {
        //    $(buttons[btn]).prop('disabled', true);
        //});
        $('.loader').show();
    });




  $(window).on('load', function () {
      $('.loader').fadeOut();
  });


  

</script>
 <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up"></i></a>

</body>
</html>
<!-- Return To Top -->
<script src="libs/bower_components/return-to-top/return-to-top.js"></script>
<script src="libs/bower_components/bootbox/bootbox.min.js"></script>

<?php if(isset($db)) { $db->db_disconnect(); } ?>