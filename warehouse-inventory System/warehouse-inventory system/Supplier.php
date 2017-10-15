<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
$page_title = 'Supplier';
require_once('includes/load.php');
page_require_level(1);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Supplier</title>
    </head>
    <body>
        <?php
        include_once 'layouts/header.php';
        ?>
        <section class="content-header">
            <h1>
                Supplier Master
                <small>Create and edit supplier details</small>
            </h1>
            <ol class="breadcrumb">
                <li>
                    <a href="#">
                        <i class="fa fa-dashboard"></i>Master
                    </a>
                </li>
                <li class="active">Supplier</li>
            </ol>
            <style>
                form {
                    display: inline;
                }
            </style>
        </section>

        <section class="content">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Supplier Details</h3>
                    <div class="box-tools pull-right"></div>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        </section>
    </body>
</html>
