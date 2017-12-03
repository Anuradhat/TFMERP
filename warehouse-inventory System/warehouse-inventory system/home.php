<?php
  $page_title = 'Home Page';
  require_once('includes/load.php');

  unset($_SESSION['header']);
  unset($_SESSION['details']);
  unset($_SESSION['PrnNos']);
  unset($_SESSION['PONos']);

  if (!$session->isUserLoggedIn(true)) { redirect('index.php', false);}
?>
<?php include_once('layouts/header.php'); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Page Header
        <small>Optional descriptions</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Level
            </a>
        </li>
        <li class="active">Here</li>
    </ol>
</section>

<!-- Main content -->
<section class="content container-fluid">

    <!--------------------------
        | Your Page Content Here |
        -------------------------->

</section>
<?php include_once('layouts/footer.php'); ?>
