<?php 
ob_start();

session_set_cookie_params(0);
session_start();

require_once('includes/load.php');

$user = current_user(); 
?>

<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> <?php echo $page_title; ?> - TFM ERP</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="/libs/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/libs/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="libs/bower_components/Ionicons/css/ionicons.min.css">
    <!-- bootstrap slider -->
    <link rel="stylesheet" href="libs/bower_components/bootstrap-slider/slider.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="libs/bower_components/select2/dist/css/select2.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="libs/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <!-- Return To Top -->
    <link rel="stylesheet" href="libs/bower_components/return-to-top/return-to-top.css">

    <!-- Theme style -->
    <!--<link rel="stylesheet" href="libs/dist/css/AdminLTE.min.css">-->
    <link href="/libs/dist/css/AdminLTE.min.css" rel="stylesheet" />
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect. -->
    <link rel="stylesheet" href="/libs/dist/css/skins/skin-blue.min.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->


    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            border-style: solid;
            border-color: black;
            background: url(../libs/images/pageload.gif) center no-repeat rgba(4, 4, 4, 0.41);
        }
    </style>


    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->


    <!-- Google Font -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic"> 
    
    <!-- REQUIRED JS SCRIPTS -->
    <!-- jQuery 3 -->
    <script src="libs/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="libs/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Typehead -->
    <script src="libs/bower_components/typeahead/bootstrap3-typeahead.min.js"></script>
    <!-- bootstrap datepicker -->
    <script src="libs/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="hold-transition skin-blue sidebar-mini">
    <div class="loader"></div>
    <div class="wrapper">
        <?php  if ($session->isUserLoggedIn(true)): ?>
        <!-- Main Header -->
        <header class="main-header">
            <!-- Logo -->
            <a href="index2.html" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini"><b>TFM ERP</b></span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg"><b>TFM ERP</b></span>
            </a>
            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <!-- Navbar Right Menu -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- Messages: style can be found in dropdown.less-->
                        <li class="dropdown messages-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-envelope-o"></i>
                                <span class="label label-success">1</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">You have 1 message(s)</li>
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu">
                                        <li>
                                            <!-- start message -->
                                            <a href="#">
                                                <div class="pull-left">
                                                    <img src="../libs/images/user.png" class="img-circle" alt="User Image">
                                                </div>
                                                <h4>
                                                    Support Team
                                                    <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                                </h4>
                                                <p>This event under construction</p>
                                            </a>
                                        </li>
                                        <!-- end message -->
 
                                    </ul>
                                </li>
                                <li class="footer"><a href="#">See All Messages</a></li>
                            </ul>
                        </li>

                        <!-- Tasks Menu -->
                        <!-- Tasks: style can be found in dropdown.less -->
                        <li class="dropdown tasks-menu" id="tasksmenu">
                            <?php  include('_partial_pendingtask.php');  ?>
                        </li>
                        <!-- User Account Menu -->
                        <li class="dropdown user user-menu">
                            <!-- Menu Toggle Button -->
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <!-- The user image in the navbar-->
                                <img src="libs/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
                                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                <span class="hidden-xs">Anuradha Thennakoon</span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- The user image in the menu -->
                                <li class="user-header">
                                    <img src="libs/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                                    <p>
                                        Anuradha Thennakoon - Web Developer
                                        <small>Member since Nov. 2012</small>
                                    </p>
                                </li>
                                <!-- Menu Body -->
                               
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="#" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="#" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <!-- Control Sidebar Toggle Button -->
                        <li>
                            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="libs/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p>Anuradha Thennakoon</p>
                        <!-- Status -->
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <!-- search form (Optional) -->
                <form action="#" method="get" class="sidebar-form">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search...">
                        <span class="input-group-btn">
                            <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </form>
                <!-- /.search form -->
                <!-- Sidebar Menu -->
                <ul class="sidebar-menu" data-widget="tree">
                    <li class="header">Master Data</li>
                    <!-- Optionally, you can add icons to the links -->
                    <!--<li class="active"><a href="#"><i class="fa fa-link"></i> <span>Link</span></a></li>
                    <li><a href="#"><i class="fa fa-link"></i> <span>Another Link</span></a></li>-->
                    <li class="treeview">
                        <a href="#">

                            <i class="fa fa-suitcase"></i> <span>Administration</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="add_user.php"><i class="fa fa-user-circle-o"></i> User</a></li>
                            <li><a href="add_group.php"><i class="fa fa-user-circle"></i> User Level</a></li>
                            <li><a href="workflow.php"><i class="fa fa-check-circle"></i> Work-Flow </a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">

                            <i class="fa fa-users"></i> <span>Employee</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="employee_department.php"><i class="fa fa-sitemap"></i> Employee Department</a></li>
                            <li><a href="employee_designation.php"><i class="fa fa-graduation-cap"></i> Employee Designation</a></li>
                            <li><a href="employee.php"><i class="fa fa-handshake-o"></i> Employee</a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">

                            <i class="fa fa-address-book-o"></i> <span>Customer / Supplier</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="customer.php"><i class="fa fa-handshake-o"></i> Customer</a></li>
                            <li><a href="supplier.php"><i class="fa fa-truck"></i> Supplier</a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">

                            <i class="fa fa-tags"></i> <span>Items</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="location.php"><i class="fa fa-map-marker "></i>Location</a></li>
                            <li><a href="location_bin.php"><i class="fa fa-trash-o "></i>Stock Bin</a></li>
                            <!--<li><a href="department.php"><i class="fa fa-th-list"></i>Department</a></li>-->
                            <li><a href="category.php"><i class="fa fa-list"></i>Category</a></li>
                            <li><a href="subcategory.php"><i class="fa fa-th-large"></i>Sub Category</a></li>
                            <li><a href="product.php"><i class="fa fa-cubes"></i>Product</a></li>
                            <li><a href="tax.php"><i class="fa fa-money"></i>Tax</a></li>
                        </ul>
                    </li>
                    <li class="header">Transaction</li>

                    <li class="treeview">
                        <a href="#">

                            <i class="fa fa-folder"></i> <span>Transaction</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                       
                            <!--Purchase Requisition Menu-->
                            <li class="treeview">
                                <a href="#">
                                    <i class="fa fa-list-ol"></i> <span>Purchase Requisition</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="create_pr.php"><i class="fa fa-plus-square-o"></i>Create</a></li>
                                    <li><a href="edit_pr.php"><i class="fa fa-pencil-square-o "></i>Update</a></li>
                                </ul>
                            </li>
                            <!--End Purchase Requisition Menu-->


                            <!--Purchase Order Menu-->
                            <li class="treeview">
                                <a href="#">
                                    <i class="fa fa-shopping-cart"></i> <span>Purchase Order</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="create_po.php"><i class="fa fa-plus-square-o"></i>Create</a></li>
                                    <li><a href="edit_po.php"><i class="fa fa-pencil-square-o "></i>Update</a></li>
                                </ul>
                            </li>
                            <!--End Purchase Requisition Menu-->

                            <!--Good Received Note Menu-->
                            <li class="treeview">
                                <a href="#">
                                    <i class="fa fa-list"></i> <span>Goods Received Note</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="create_grn.php"><i class="fa fa-plus-square-o"></i>Create</a></li>
                                </ul>
                            </li>
                            <!--End Good Received Note Menu-->

                            <!--Transfer Note Menu-->
                            <li class="treeview">
                                <a href="#">
                                    <i class="fa fa-angle-double-right"></i> <span>Transfer Note</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="create_transfernote.php"><i class="fa fa-plus-square-o"></i>Create</a></li>
                                </ul>
                            </li>
                            <!--End Transfer Note Menu-->

                            <!--Sales Order Menu-->
                            <li class="treeview">
                                <a href="#">
                                    <i class="fa fa-file-text-o"></i> <span>Sales Order</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="create_salesorder.php"><i class="fa fa-plus-square-o"></i>Create</a></li>
                                    <li><a href="edit_salesorder.php"><i class="fa fa-pencil-square-o "></i>Update</a></li>
                                </ul>
                            </li>
                            <!--End Sales Order Menu-->

                            <!--Customer Purchase Order Menu-->
                            <li class="treeview">
                                <a href="#">
                                    <i class="fa fa-cart-plus"></i> <span>Customer PO</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="create_customerpo.php"><i class="fa fa-plus-square-o"></i>Create</a></li>
                                    <li><a href="edit_customerpo.php"><i class="fa fa-pencil-square-o "></i>Update</a></li>
                                </ul>
                            </li>
                            <!--End Customer Purchase Order Menu-->

                            <!--Invoice Menu-->
                            <li class="treeview">
                                <a href="#">
                                    <i class="fa fa-cart-plus"></i> <span>Invoice</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li><a href="create_invoice.php"><i class="fa fa-plus-square-o"></i>Create</a></li>
                                </ul>
                            </li>
                            <!--End Invoice Menu-->













                            
                        </ul>
                    </li>
                </ul>
                <!-- /.sidebar-menu -->
            </section>
            <!-- /.sidebar -->
        </aside>
        <?php endif;?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
