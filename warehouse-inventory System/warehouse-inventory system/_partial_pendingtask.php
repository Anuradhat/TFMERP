<?php
 require_once('includes/load.php');
?>

<a href="#" class="dropdown-toggle" data-toggle="dropdown">
    <i class="fa fa-flag-o"></i>
    <span class="label label-danger">
        <?php
        $pendingJobs = check_pending_approvels();
        echo count($pendingJobs);
        ?>
    </span>
</a>
<ul class="dropdown-menu">
    <li class="header">
        You have <?php   echo count($pendingJobs); ?> task(s)
    </li>
    <li>
        <!-- inner menu: contains the actual data -->
        <ul class="menu">

            <!-- purchase order approve tasks -->
            <?php
            $pendingPoJobs = check_pending_approvels('001');
            if(count($pendingPoJobs) > 0){
            ?>
            <li>
                <!-- PO Task item -->
                <a href="approval_task.php?TransactionCode=001">
                    <i class="glyphicon glyphicon-check"></i>&nbsp;Need approval for <?php echo count($pendingPoJobs); ?> purchase order(s)
                </a>
            </li>
            <?php } ?>
            <!-- end task item -->



        </ul>
    </li>
    <li class="footer">
        <a href="approval_task.php?TransactionCode=all">View all tasks</a>
    </li>
</ul>