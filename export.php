<?php
require_once __DIR__ . '/config.php'; require_login();
$allowed=['residents'=>'residents','households'=>'households','certificates'=>'certificates','finance'=>'finance','blotter'=>'blotter','ordinances'=>'ordinances','inventory'=>'inventory'];
$key=$_GET['table']??''; if(!isset($allowed[$key])){http_response_code(400);exit('Invalid export');}
$rows=db()->query("SELECT * FROM `{$allowed[$key]}`")->fetchAll();
header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="'.$key.'_'.date('Ymd').'.csv"');
$out=fopen('php://output','w'); if($rows){fputcsv($out,array_keys($rows[0]));foreach($rows as $row)fputcsv($out,$row);} fclose($out);
