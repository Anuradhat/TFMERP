<?php
$errors = array();

/*--------------------------------------------------------------*/
/* Function for Remove escapes special
/* characters in a string for use in an SQL statement
/*--------------------------------------------------------------*/
function real_escape($str){
    global $con;
    $escape = mysqli_real_escape_string($con,$str);
    return $escape;
}
/*--------------------------------------------------------------*/
/* Function for Remove html characters
/*--------------------------------------------------------------*/
function remove_junk($str){
    $str = nl2br($str);  $str = htmlspecialchars(strip_tags($str, ENT_QUOTES));
    return $str;

}




function RemoveValueFromListOfArray($array,$search) {
    $count = 0;
    foreach($array as $row => $value){
        if(array_search($search, $value,true) !== false)
        {
            unset($array[$count]);
            $array = array_values($array);
            return $array;
        }
        $count++;
    }
    return $array;
}


function ChangValueFromListOfArray($array,$search,$changeIndex,$NewValue) {
    $count = 0;
    foreach($array as $row => $value){
        if(array_search($search, $value,true) !== false)
        {
            $value[$changeIndex] = $NewValue;
            $array[$count] = $value;

        }
        $count++;
    }
    return $array;
}

function ChangValueOfArray($array,$changeIndex,$NewValue) {

    $array[$changeIndex] = $NewValue;
    return $array;
}

function ExistInArray($array,$search) {
    if(is_array($array)){
        $count = 0;
        foreach($array as $row => $value){
            if(array_search($search, $value,true) !== false)
            {
                return true;
            }
            $count++;
        }
        return false;
    }
    return false;
}

function ExistInArray_New($array,$search) {
    if(is_array($array)){
        if(array_search($search, $array,true) !== false)
            {
                return true;
            }
        return false;
    }
    return false;
}



function ArraySearch($array,$search) {
    $count = 0;
    foreach($array as $row => $value){
        if(array_search($search, $value,true) !== false)
        {
            return $value;
        }
        $count++;
    }
    return null;
}

function ArraySearchWithCoulmn($array,$search,$coulmn) {
    $count = 0;
    foreach($array as $row => $value){
        if($value[$coulmn] == $search)
        {
            return $value;
        }
        $count++;
    }
    return null;
}

/*--------------------------------------------------------------*/
/* Validate value
/*--------------------------------------------------------------*/
function string2Value($str){
    if(empty($str) || !is_numeric($str))
        return 0;
    else
        return $str;
}

/*--------------------------------------------------------------*/
/* Validate Boolean
/*--------------------------------------------------------------*/
function string2Boolean($str){
    if(empty($str))
        return 0;
    else if ($str == "on" || $str == true)
        return 1;
    else
        return 0;
}

/*--------------------------------------------------------------*/
/* Function for Uppercase first character
/*--------------------------------------------------------------*/
function first_character($str){
    $val = str_replace('-'," ",$str);
    $val = ucfirst($val);
    return $val;
}
/*--------------------------------------------------------------*/
/* Function for Checking input fields not empty
/*--------------------------------------------------------------*/
function validate_fields($var){
    global $errors;
    foreach ($var as $field) {
        $val = remove_junk($_POST[$field]);
        if(isset($val) && $val==''){
            $errors = $field ." can't be blank.";
            return $errors;
        }
    }
}
/*--------------------------------------------------------------*/
/* Function for Display Session Message
Ex echo displayt_msg($message);
/*--------------------------------------------------------------*/
function display_msg($msg =''){
    $output = array();
    if(!empty($msg)) {
        foreach ($msg as $key => $value) {
            $output  = "<div class=\"alert alert-{$key}\">";
            $output .= "<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>";
            $output .= remove_junk(first_character($value));
            $output .= "</div>";
        }
        return $output;
    } else {
        return "" ;
    }
}
/*--------------------------------------------------------------*/
/* Function for redirect
/*--------------------------------------------------------------*/
function redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }
}


function RedirectWithMethodPost($dest)
{
    $url = $params = '';
    if( strpos($dest,'?') ) { list($url,$params) = explode('?',$dest,2); }
    else { $url = $dest; }
    echo "<form id='the-form'
      method='post'
      enctype='multipart/form-data'
      action='$url'>\n";
    foreach( explode('&',$params) as $kv )
    {
        if( strpos($kv,'=') === false ) { continue; }
        list($k,$v) = explode('=',$kv,2);
        echo "<input type='hidden' name='$k' value='$v'>\n";
    }
}



function preventGetAction($url)
{
    if (!isset($_SERVER['HTTP_REFERER']))
    {
        header('Location: ' . $url, true);
    }
}

/*--------------------------------------------------------------*/
/* Function for find out total saleing price, buying price and profit
/*--------------------------------------------------------------*/
function total_price($totals){
    $sum = 0;
    $sub = 0;
    foreach($totals as $total ){
        $sum += $total['total_saleing_price'];
        $sub += $total['total_buying_price'];
        $profit = $sum - $sub;
    }
    return array($sum,$profit);
}
/*--------------------------------------------------------------*/
/* Function for Readable date time
/*--------------------------------------------------------------*/
function read_date($str){
    if($str)
        return date('F j, Y, g:i:s a', strtotime($str));
    else
        return null;
}
/*--------------------------------------------------------------*/
/* Function for  Readable Make date time
/*--------------------------------------------------------------*/
function make_date(){
    return strftime("%Y-%m-%d", time());
}

/*--------------------------------------------------------------*/
/* Function for covert Readable Make date
/*--------------------------------------------------------------*/
function convert_date($date){
    $time = strtotime($date);
    return strftime("%Y-%m-%d", $time);
}


/*--------------------------------------------------------------*/
/* Function for  Readable Make date time
/*--------------------------------------------------------------*/
function make_datetime(){
    return strftime("%Y-%m-%d %H:%M:%S", time());
}
/*--------------------------------------------------------------*/
/* Function for  Readable date time
/*--------------------------------------------------------------*/
function count_id(){
    static $count = 1;
    return $count++;
}
/*--------------------------------------------------------------*/
/* Function for Creting random string
/*--------------------------------------------------------------*/
function randString($length = 5)
{
    $str='';
    $cha = "0123456789abcdefghijklmnopqrstuvwxyz";

    for($x=0; $x<$length; $x++)
        $str .= $cha[mt_rand(0,strlen($cha))];
    return $str;
}

function EqualValue($val,$arr)
{
    foreach ($arr as &$value) {
        if( $val == $value[0])
        {
            return true;
        }
    }
    return false;
}


function SendMailForApprovals($ToMail,$subject,$htmlContent)
{
    
    // Set content-type header for sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    // Additional headers
    $headers .= 'From: TFM ERP Mail Notification <erp@tfm.lk>' . "\r\n";


    //mail($ToMail,$subject,$htmlContent,$headers);

}


?>
