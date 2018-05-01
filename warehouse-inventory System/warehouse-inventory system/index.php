<?php
ob_start();
require_once('includes/load.php');
  if($session->isUserLoggedIn(true)) { redirect('home.php', false);}
?>
<?php include_once('layouts/userlogin.php'); ?>
<div class="login-page">
    <?php echo display_msg($msg); ?>
    <form method="post" action="auth.php" class="clearfix">
        <div style="width: 400px; height: max-content" class="box box-primary box-group box-solid  center-block">
            <div class="box-body with-border">
                <p class="login-box-msg">Sign in to start your session</p>
                <!--<div class="form-group">
                    <label for="username" class="control-label">Username</label>
                    <input type="name" class="form-control" name="username" placeholder="Username">
                </div>-->
                <!--<div class="form-group">
                    <label for="Password" class="control-label">Password</label>
                    <input type="password" name= "password" class="form-control" placeholder="password">
                </div>-->
                <div class="form-group has-feedback">
                    <input type="text" name="username" class="form-control" placeholder="Username">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password"  name= "password" class="form-control" placeholder="Password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <!--<div class="form-group">
                    <button type="submit" class="btn btn-info  pull-right">Login</button>
                </div>-->

                <div class="row">
                    <div class="col-xs-8">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox"> Remember Me
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                    </div>
                    <!-- /.col -->
                </div>
            </div>
        </div>


    </form>
</div>
</div>

    <!-- jQuery 3 -->
<script src="libs/bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="libs/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- icheck -->
<script src="libs/dist/iCheck/icheck.min.js"></script>

<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>

</body>
</html>
