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
        if(array_search($search, $value) !== false)
        {
            unset($array[$count]);
            return $array;
        }
        $count++;
    }
    return $array;
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


?>
