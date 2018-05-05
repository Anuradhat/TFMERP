<?php
require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
    global $db;
    if(tableExists($table))
    {
        return find_by_sql("SELECT * FROM ".$db->escape($table));
    }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
    global $db;
    $result = $db->query($sql);
    $result_set = $db->while_loop($result);
    return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
    global $db;
    $id = (int)$id;
	if(tableExists($table)){
        $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
        if($result = $db->fetch_assoc($sql))
			return $result;
        else
            return null;
	}
}

/*--------------------------------------------------------------*/
/*  Function for auto generate number for master and transaction table
/*--------------------------------------------------------------*/
function autoGenerateNumber($table,$mode)
{
    global $db;;
    $query = "SELECT TrnsactionTableName,Prefix,SerialNo,SerialLength,Mode FROM tfmAutoIncerementU WHERE TrnsactionTableName ='{$table}' AND Mode = {$mode};";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        $prefix = $row['Prefix'];
        $serialNo = $row['SerialNo'];
        $serialLength = $row['SerialLength'];

        $serialNo = $serialNo + 1;

        $query = "call spAutoIncrement('{$prefix}',{$serialNo},'{$table}');";
        $db->query($query);

        return  $prefix.str_pad($serialNo, $serialLength, "0", STR_PAD_LEFT);
    }

    return 0;

}


/*--------------------------------------------------------------*/
/*  Function for auto generate serial number for grn serial table
/*--------------------------------------------------------------*/
function autoGenerateSerialNumber()
{
    global $db;;
    $query = "SELECT TrnsactionTableName,Prefix,SerialNo,SerialLength,Mode FROM tfmAutoIncerementU WHERE TrnsactionTableName ='tfmGrnSerialT';";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        $prefix = $row['Prefix'];
        $serialNo = $row['SerialNo'];
        $serialLength = $row['SerialLength'];

        if($serialNo == "999999")
        {
            $prefix++;
            $serialNo = 1;
        }
        else
            $serialNo = $serialNo + 1;

        $query = "call spAutoIncrement('{$prefix}',{$serialNo},'tfmGrnSerialT');";
        $db->query($query);

        return  $prefix.str_pad($serialNo, $serialLength, "0", STR_PAD_LEFT);
    }

    return 0;

}

/*--------------------------------------------------------------*/
/*  Function for read system configuration settings from stored procedure
/*--------------------------------------------------------------*/

function ReadSystemConfig($key)
{
    global $db;
    $query = "call spReadSystemConfig('{$key}');";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        $Value = $row['Value'];

        return  $Value;
    }
    return null;
}




/*--------------------------------------------------------------*/
/*  Function for read stock SIH from stock table For Location and Stock code
/*--------------------------------------------------------------*/

function SelectStockSIH($StockCode,$LocationCode)
{
    global $db;
    $query = "call spSelectStockSIHFromStockNLocationCode('{$StockCode}','{$LocationCode}');";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        $Value = $row['SIH'];

        return  $Value;
    }
    return null;
}

/*--------------------------------------------------------------*/
/*  Function for read stock SIH from stock table For Location and Product code
/*--------------------------------------------------------------*/

function SelectStockSIHFormProduct($ProductCode,$LocationCode)
{
    global $db;
    $query = "call spSelectStockSIHFromProductNLocationCode('{$ProductCode}','{$LocationCode}');";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        $Value = $row['SIH'];

        return  $Value;
    }
    return null;
}


/*--------------------------------------------------------------*/
/*  Function check serial code avalable to return
/*--------------------------------------------------------------*/

function CheckSerialAbalableToReturn($InvoiceNo,$SerialCode)
{
    global $db;
    $query = "call spSelectProductDetailsFromInvoiceFromSerialCode('{$InvoiceNo}','{$SerialCode}');";
    if($result = $db->query($query))
    {
        return  true;
    }
    return false;
}


/*--------------------------------------------------------------*/
/*  Function for read po last process date-time from stored procedure
/*--------------------------------------------------------------*/

function getLastPoProcessDateTime($PurchaseOrderNo)
{
    global $db;
    $query = "call spSelectAllPOHeaderDetailsFromPONo('{$PurchaseOrderNo}');";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        $Value = $row['ProcessDate'];

        return  $Value;
    }
    return null;
}


/*--------------------------------------------------------------*/
/*  Function for read default bin in location from stored procedure
/*--------------------------------------------------------------*/
function DefaultBinFromLocation($LocationCode)
{
    global $db;
    $query = "call spSelectDefaultBinFromLocationCode('{$LocationCode}');";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        $Value = $row['BinCode'];

        return  $Value;
    }
    return null;
}

function ReadProductDatails($ProductCode)
{
    global $db;
    $query = "call spSelectProductFromCode('{$ProductCode}');";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);

        return array('ProductCode' => $row["ProductCode"],'CostPrice' => $row["CostPrice"],'StockNo' => $row["StockCode"],'ExpireDate' => $row["ExpireDate"]);
    }
    return null;
}


function CalculateAverageCost($ProductCode,$PurchaseQty,$PurchaseCostPrice)
{
    global $db;
    $query = "call spSelectStockForAvgCostPrice('{$ProductCode}');";
    if($result = $db->query($query))
    {
        $row = $db->fetch_assoc($result);
        $CurrentAvgCostPrice =$row["AvgCostPrice"];
        $CurrentSIH  = $row["SIH"];
        if($CurrentAvgCostPrice == 0)
            return $PurchaseCostPrice;
        else
        {
            $AvgCost =  (($CurrentAvgCostPrice * $CurrentSIH) + ($PurchaseCostPrice * $PurchaseQty))/ ($CurrentSIH + $PurchaseQty);

            return round($AvgCost);
        }

    }
    return 0;
}



/*--------------------------------------------------------------*/
/*  Function for Find data from stored procedure
/*--------------------------------------------------------------*/
function find_by_sp($sql)
{
	global $db;
	$db->db_connect();
	if($sql){
		$sql = $db->query($sql);
		if($result = $db->fetch_assoc($sql))
			return $result;
        else
			return null;

    }
}

/*--------------------------------------------------------------*/
/* Function for Delete data from stored procedure
/*--------------------------------------------------------------*/
function delete_by_sp($sql)
{
    global $db;
    if($sql)
    {
        $db->query($sql);
        return ($db->affected_rows() === 1) ? true : false;
    }
}

/*--------------------------------------------------------------*/
/* Function for check data exist for given select stored procedure
/*--------------------------------------------------------------*/
function row_count_sp($sql)
{
	global $db;
	if($sql){
		$result = $db->query($sql);
		$num_rows = mysql_num_rows($result);

		if($num_rows)
			return $num_rows;
		else
			return null;
	}
}


/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
    global $db;
    if(tableExists($table))
    {
        $sql = "DELETE FROM ".$db->escape($table);
        $sql .= " WHERE id=". $db->escape($id);
        $sql .= " LIMIT 1";
        $db->query($sql);
        return ($db->affected_rows() === 1) ? true : false;
    }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
    global $db;
    if(tableExists($table))
    {
        $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
        $result = $db->query($sql);
        return($db->fetch_assoc($result));
    }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
    global $db;
    $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
    if($table_exit) {
		if($db->num_rows($table_exit) > 0)
            return true;
        else
            return false;
    }
}
/*--------------------------------------------------------------*/
/* Login with the data provided in $_POST,
/* coming from the login form.
/*--------------------------------------------------------------*/
function authenticate($username='', $password='') {
	global $db;
	$username = $db->escape($username);
	$password = $db->escape($password);
	$sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
	$result = $db->query($sql);
	if($db->num_rows($result)){
        $user = $db->fetch_assoc($result);
        $password_request = sha1($password);
        if($password_request === $user['password'] ){
            return $user['id'];
        }
	}
    return false;
}
/*--------------------------------------------------------------*/
/* Login with the data provided in $_POST,
/* coming from the login_v2.php form.
/* If you used this method then remove authenticate function.
/*--------------------------------------------------------------*/
function authenticate_v2($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
        $user = $db->fetch_assoc($result);
        $password_request = sha1($password);
        if($password_request === $user['password'] ){
            return $user;
        }
    }
	return false;
}


/*--------------------------------------------------------------*/
/* Find current log in user by session id
/*--------------------------------------------------------------*/
function current_user(){
    static $current_user;
    global $db;
    if(!$current_user){
        if(isset($_SESSION['user_id'])):
            $user_id = intval($_SESSION['user_id']);
            $current_user = find_by_id('users',$user_id);
		endif;
    }
	return $current_user;
}
/*--------------------------------------------------------------*/
/* Find all user by
/* Joining users table and user gropus table
/*--------------------------------------------------------------*/
function find_all_user(){
    global $db;
    $results = array();
    $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
    $sql .="g.group_name,u.EmployeeCode ";
    $sql .="FROM users u ";
    $sql .="LEFT JOIN user_groups g ";
    $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
    $result = find_by_sql($sql);
    return $result;
}
/*--------------------------------------------------------------*/
/* Function to update the last log in of a user
/*--------------------------------------------------------------*/

function updateLastLogIn($user_id)
{
    global $db;
    date_default_timezone_set('Asia/Colombo');
    $date = date('Y/m/d h:i:s a', time());
	$sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
	$result = $db->query($sql);
	return ($result && $db->affected_rows() === 1 ? true : false);
}

/*--------------------------------------------------------------*/
/* Find all Group name
/*--------------------------------------------------------------*/
function find_by_groupName($val)
{
	global $db;
	$sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
	$result = $db->query($sql);
	return($db->num_rows($result) === 0 ? true : false);
}
/*--------------------------------------------------------------*/
/* Find group level
/*--------------------------------------------------------------*/
function find_by_groupLevel($level)
{
	global $db;
	$sql = "SELECT group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
	$result = $db->query($sql);
	return($db->num_rows($result) === 0 ? true : false);
}
/*--------------------------------------------------------------*/
/* Function for cheaking which user level has access to page
/*--------------------------------------------------------------*/
function page_require_level($require_level){
    global $session;
    $current_user = current_user();
    $login_level = find_by_groupLevel($current_user['user_level']);
    //if user not login
    if (!$session->isUserLoggedIn(true)):
        $session->msg('d','Please login...');
        redirect('index.php', false);
        //if Group status Deactive
    elseif($login_level['group_status'] === '0'):
        $session->msg('d','This level user has been band!');
        redirect('home.php',false);
        //cheackin log in User level and Require level is Less than or equal to
    elseif($current_user['user_level'] <= (int)$require_level):
        return true;
    else:
        $session->msg("d", "Sorry! you dont have permission to view the page.");
        redirect('home.php', false);
    endif;

}


/*--------------------------------------------------------------*/
/* Function for cheaking which user has access to page
/*--------------------------------------------------------------*/
function UserPageAccessControle($require_level,$PageName){
    global $session;
    $current_user = current_user();
    $login_level = find_by_groupLevel($current_user['user_level']);
    $UserAccess = PageApprovelDetailsByUserName('NoNeed');

    foreach($UserAccess as $UAccess){
        if($PageName == $UAccess['Page'] and $UAccess['Controller'] == 'Page Access'){
            $AccessStatus = $UAccess["Access"];
        }
    }

    //if user not login
    if (!$session->isUserLoggedIn(true)):
        $session->msg('d','Please login...');
        redirect('index.php', false);
        //if Group status Deactive
    elseif($login_level['group_status'] === '0'):
        $session->msg('d','This level user has been band!');
        redirect('home.php',false);
        //cheackin log in User level and Require level is Less than or equal to
    elseif($AccessStatus == '1'):
        return true;
    else:
        $session->msg("d", "Sorry! you dont have permission to view the page.");
        redirect('home.php', false);
    endif;
}


/*--------------------------------------------------------------*/
/* Function for Finding all approvals'
/*--------------------------------------------------------------*/
function check_pending_approvels($transaction_code = "")
{
    global $db;
    $current_user = current_user();

    if(isset($current_user))
    {
        if($current_user["EmployeeCode"] != "")
        {
            $EmployeeCode = $current_user["EmployeeCode"];

            if($transaction_code == "")
            {
                $sql  = "call spViewPendingApprovelsFromEmployeeCode('{$EmployeeCode}');";
            }
            else
            {
                $sql  =  "call spViewPendingApprovelsFromEmployeeTransactionCode('{$EmployeeCode}','{$transaction_code}');";
            }

            return find_by_sql($sql);
            //return $db->fetch_assoc($result);
        }
    }
    return "";
}

/*--------------------------------------------------------------*/
/* Function for Finding Page Approvels'
/*--------------------------------------------------------------*/
function PageApprovelDetailsByUserName($UserName){
    $current_user = current_user();

    $userAccessDetails = find_by_sql("call spSelectUserAccessDetailsByUser('{$current_user["username"]}');");
    //define("UserAccess",serialize($userAccessDetails));

    return $userAccessDetails;
}

/*--------------------------------------------------------------*/
/* Function for insert recent activity'
/*--------------------------------------------------------------*/
function InsertRecentActvity($Title,$Description){
    global $db;

    $current_user = current_user();
    $date    = make_date();

    $query = "call spInsertRecentActivity('{$current_user["username"]}','{$date}','{$Title}','{$Description}');";
    $db->query($query);
}


?>
