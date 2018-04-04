<?php
 ob_start();

 session_set_cookie_params(0);
 session_start();

 require_once('includes/load.php');
 preventGetAction('create_barcode.php');

if (!isset( $_SESSION['Transaction ']))
    redirect('create_barcode.php');

$datetime = make_datetime();
$Transaction = $_SESSION['Transaction'];
$TransactionNo = $_SESSION['TransactionNo'];

if($Transaction == 'GRN')
    $all_Trans = find_by_sql("call spBarCodeForGRN('{$TransactionNo}');");

header('Content-disposition: attachment; filename=Barcode_'.$datetime.'_'.$Transaction.'.txt');
header('Content-type: text/plain');

foreach($all_Trans as &$value){
    echo $value[0].",".$value[1].",".$value[2].",".$value[3].PHP_EOL;
}

?>