<?php
$page_title = 'Add User';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(1);
$groups = find_all('user_groups');
$all_Employees = find_by_sql("call spSelectAllEmployees();");
?>
<?php
if(isset($_POST['add_user'])){

    $req_fields = array('full-name','username','password','level' );
    validate_fields($req_fields);

    if(empty($errors)){
        $name   = remove_junk($db->escape($_POST['full-name']));
        $username   = remove_junk($db->escape($_POST['username']));
        $password   = remove_junk($db->escape($_POST['password']));  
        
        $parts = $_POST['level'];
        $arr = explode(':', $parts);
        $user_level = $arr[0];
        $Groupname = $arr[1];

        $employee = remove_junk($db->escape($_POST['EmployeeCode']));
        $password = sha1($password);
        $query = "INSERT INTO users (";
        $query .="name,username,password,user_level,status,EmployeeCode,group_name";
        $query .=") VALUES (";
        $query .=" '{$name}', '{$username}', '{$password}', '{$user_level}','1', '{$employee}', '{$Groupname}'";
        $query .=")";
        if($db->query($query)){
            //sucess
            $session->msg('s',"User account has been creted! ");
            redirect('add_user.php', false);
        } else {
            //failed
            $session->msg('d',' Sorry failed to create account!');
            redirect('add_user.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_user.php',false);
    }
}
?>
<?php include_once('layouts/header.php'); ?>
  

<section class="content-header">
    <h1>
        User
        <small>Create users</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="#">
                <i class="fa fa-dashboard"></i>Administration
            </a>
        </li>
        <li class="active">User</li>
    </ol>
    <style>
        form {
            display: inline;
        }
    </style>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12"><?php echo display_msg($msg); ?>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">User</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>

        <div class="box-body">
            <div class="col-md-6">
                <form method="post" action="add_user.php">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="full-name" placeholder="Full Name" required="required">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" name="username" placeholder="Username" required="required">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control" name="password" placeholder="Password" required="required">
                    </div>
                    <div class="form-group">
                        <label for="confirmpassword">Confirm Password</label>
                        <input id="confirmpassword" type="password" class="form-control" name="confirmpassword" placeholder="Confirm Password" required="required">
                    </div>
                    <div class="form-group">
                        <label for="level">Employee </label>
                        <select class="form-control select2" style="width: 100%;" name="EmployeeCode" id="EmployeeCode1" required="required">
                            <option value="">Select Employee</option><?php  foreach ($all_Employees as $EMP): ?>
                            <option value="<?php echo $EMP['EpfNumber'] ?>"><?php echo $EMP['EpfNumber'] ?> ~ <?php echo $EMP['EmployeeName'] ?>
                            </option><?php endforeach; ?>
                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="level">User Role</label>
                        <select class="form-control" name="level"><?php foreach ($groups as $group ):?>
                            <option value="<?php echo $group['group_level'];?>:<?php echo ucwords($group['group_name']);?>"><?php echo ucwords($group['group_name']);?> 
                            </option><?php endforeach;?>
                        </select>
                    </div>
                    <div class="form-group clearfix">
                        <button id="addUser" type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

    
    <?php include_once('layouts/footer.php'); ?>
    <script type="text/javascript">


        function validatePassword() {
            var pws = $("#password");
            var cpws = $("#confirmpassword");

            var cpwsMessage = $("#confirmpassword").get(0);
            if (pws.val() != cpws.val()) {
                cpwsMessage.setCustomValidity("Passwords Don't Match");
            }
            else {
                cpwsMessage.setCustomValidity('');
            }
        }

        $(function () {
            $("#password").change(function () {
                validatePassword();
            })

            $("#confirmpassword").keyup(function () {
                validatePassword();
            })
        })
    </script>

