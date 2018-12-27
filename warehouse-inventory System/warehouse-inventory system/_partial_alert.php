<?php
 require_once('includes/load.php');
?>

<a href="#" class="dropdown-toggle" data-toggle="dropdown">
    <i class="fa fa-envelope-o"></i>
    <?php $alerts = check_alerts_UserGroup(); $PendingInvoicealerts = check_alerts_UserGroupPendingInvoice();
          if(count($alerts) + count($PendingInvoicealerts) > 0){ ?>
    <span class="label label-success">
        <?php
              echo count($alerts) + count($PendingInvoicealerts);
        ?>
    </span>
<?php } ?>
</a>
<ul class="dropdown-menu">
    <li class="header">
        You have <?php   echo count($alerts); ?> task(s)
    </li>
    <li>
        <!-- inner menu: contains the actual data -->
        <ul class="menu">

            <!-- purchase order approve tasks --><?php
            //$alerts = check_alerts_UserGroup();
            if(count($alerts) > 0){
            ?>
            <li>
                <!-- PO Task item -->
                <a href="#">
                    <i class="glyphicon glyphicon-check"></i>&nbsp;Prepare <?php echo count($alerts); ?> customer purchase order(s)
                </a>
            </li><?php } ?>
            <!-- end task item -->

            <!-- Pending Invoice Alert -->
            <?php
            //$alerts = check_alerts_UserGroup();
            if(count($PendingInvoicealerts) > 0){
            ?>
            <li>
                <!-- PO Task item -->
                <a href="#">
                    <i class="glyphicon glyphicon-check"></i>&nbsp;Prepare <?php echo count($PendingInvoicealerts); ?> Pending Invoice(s)
                </a>
            </li>
            <?php } ?>
            <!-- end task item -->

        </ul>
    </li>
    <li class="footer">
        <?php if(count($alerts) > 0){ ?>
        <!--<a href="approval_task.php?TransactionCode=all">View all tasks</a>-->
        <?php } ?>
    </li>
</ul>

<script>
    $(document).ready(function () {
        setInterval(UpdateAlert, 300000);
    })

    function UpdateAlert() {
        $.ajax({
            url: '_partial_alert.php',
            type: 'POST',
            data: { UpdateAlert: 'Alert', },
            success: function (data) {
                
                $('.messages-menu').load('_partial_alert.php');
            },
            complete: function (data) {
                
            }
        });
    };
</script>