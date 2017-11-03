<?php
require_once(LIB_PATH_INC.DS."config.php");

class MySqli_DB {

    private $con;
    public $query_id;

    function __construct() {
      $this->db_connect();
    }

/*--------------------------------------------------------------*/
/* Function for Open database connection
/*--------------------------------------------------------------*/
public function db_connect()
{
  $this->con = mysqli_connect(DB_HOST,DB_USER,DB_PASS);
  if(!$this->con)
         {
           die(" Database connection failed:". mysqli_connect_error());
         } else {
           $select_db = $this->con->select_db(DB_NAME);
             if(!$select_db)
             {
               die("Failed to Select Database". mysqli_connect_error());
             }
         }
}
/*--------------------------------------------------------------*/
/* Function for Close database connection
/*--------------------------------------------------------------*/

public function db_disconnect()
{
  if(isset($this->con))
  {
    mysqli_close($this->con);
    unset($this->con);
  }
}
/*--------------------------------------------------------------*/
/* Function for mysqli query
/*--------------------------------------------------------------*/
public function query($sql)
   {
      if (trim($sql != "")) {
          $this->query_id = $this->con->query($sql);
          do{} while(mysqli_more_results($this->con) && mysqli_next_result($this->con));
          //$this->next_result();
      }
      if (!$this->query_id)
        // only for Develope mode
              die("Error on this Query :<pre> " . $sql ." Error: ".$this->con->error."</pre>");
       // For production mode
        //  die("Error on Query");
       return $this->query_id;

   }

//public function query($sql)
//{
//    if (trim($sql != "")) {
//        $this->query_id = $this->con->query($sql);
//        //do{} while(mysqli_more_results($this->con) && mysqli_next_result($this->con));
//    }
//    if (!$this->query_id)
//        // only for Develope mode
//        die("Error on this Query :<pre> " . $sql ." Error: ".$this->con->error."</pre>");
//    // For production mode
//    //  die("Error on Query");
//    return $this->query_id;

//}


function callProcedure($pv_proc, $pt_args )
{
    if (empty($pv_proc) || empty($pt_args))
    {
        return false;
    }
    $lv_call   = "CALL `$pv_proc`(";
    $lv_select = "SELECT";
    $lv_log = "";
    foreach($pt_args as $lv_key=>$lv_value)
    {
        $lv_query = "SET @_$lv_key = '$lv_value'";
        $lv_log .= $lv_query.";\n";
        //if (!$lv_result =  $this->con->query($lv_query))
        //{
        //    /* Write log */
        //    return false;
        //}
        $lv_call   .= " @_$lv_key,";
        $lv_select .= " @_$lv_key AS $lv_key,";
    }
    $lv_call   = substr($lv_call, 0, -1).")";
    $lv_select = substr($lv_select, 0, -1);
    $lv_log .= $lv_call;
    if ($lv_result = $this->con->query($lv_call))
    {
        if($lo_result = $this->con->query($lv_select))
        {
            $lt_result = $lo_result->fetch_assoc();
            $lo_result->free();
            return $lt_result;
        }
        /* Write log */
        return false;
    }
    /* Write log */
    return false;
}



/*--------------------------------------------------------------*/
/* Function for Query Helper
/*--------------------------------------------------------------*/
// Begin transaction
function begin(){
    //$this->query("BEGIN");
    //mysqli_autocommit($this->con,false);
    mysqli_begin_transaction($this->con,MYSQLI_TRANS_START_READ_WRITE);
}

//Commit transaction
function commit(){
   // mysqli_autocommit($this->con,false);
    mysqli_commit($this->con);
}

//Rollback transaction
function rollback(){
    //$this->query("ROLLBACK");
    mysqli_rollback($this->con);
}

public function next_result()
{
    mysqli_next_result($this->con);
    //mysqli_more_results($this->con);

}


public function fetch_array($statement)
{
  return mysqli_fetch_array($statement);
}
public function fetch_object($statement)
{
  return mysqli_fetch_object($statement);
}
public function fetch_assoc($statement)
{
  return mysqli_fetch_assoc($statement);
}
public function num_rows($statement)
{
  return mysqli_num_rows($statement);
}
public function insert_id()
{
  return mysqli_insert_id($this->con);
}
public function affected_rows()
{
  return mysqli_affected_rows($this->con);
}
/*--------------------------------------------------------------*/
 /* Function for Remove escapes special
 /* characters in a string for use in an SQL statement
 /*--------------------------------------------------------------*/
 public function escape($str){
   return $this->con->real_escape_string($str);
 }

 /*--------------------------------------------------------------*/
 /* Function for Remove escapes special in array
 /* characters in a string for use in an SQL statement
 /*--------------------------------------------------------------*/
 public function escape_array($arr){
     $array = array();

     foreach ($arr as &$value) {
         $array[] = $this->con->real_escape_string($value);
     }
     return $array;
 }

/*--------------------------------------------------------------*/
/* Function for while loop
/*--------------------------------------------------------------*/
public function while_loop($loop){
 global $db;
   $results = array();
   while ($result = $this->fetch_array($loop)) {
      $results[] = $result;
   }
 return $results;
}

}

$db = new MySqli_DB();

?>
