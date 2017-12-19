<?php
 require_once('includes/load.php');
?>

<a href="#" class="dropdown-toggle" data-toggle="dropdown">
    <i class="fa fa-flag-o"></i>
    <?php $pendingJobs = check_pending_approvels(); if(count($pendingJobs) > 0){ ?>
    <span class="label label-danger">
        <?php
        echo count($pendingJobs);
        ?>
    </span>
<?php } ?>
</a>
<ul class="dropdown-menu">
    <li class="header">
        You have <?php   echo count($pendingJobs); ?>
 task(s)
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
        <?php if(count($pendingJobs) > 0){ ?>
        <a href="approval_task.php?TransactionCode=all">View all tasks</a>
        <?php } ?>
    </li>
</ul>