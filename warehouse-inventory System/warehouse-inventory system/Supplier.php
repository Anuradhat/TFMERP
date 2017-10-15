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
            <div class="box-tools pull-left"></div>
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
        </div>
        <div class="box-body">
            <div id="1stRow" class="row form-group">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="box">
                        <div class="box-body">
                            <table class="table table-condensed table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Tel</th>
                                        <th>Fax</th>
                                        <th>email</th>
                                        <th>Contact Person</th>
                                        <th>Vat No</th>
                                        <th>SVat No</th>
                                        <th>Credit Period</th>
                                        <th>currency</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include_once 'layouts/footer.php'; ?>
    
