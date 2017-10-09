<?php
ini_set("display_errors",On);
session_start();
$sid=session_id();
extract($_POST); extract($_GET);
require "config.php";
mysql_connect('localhost',$dbuser,$dbpwd) ;
mysql_select_db($dbname);
mysql_query(" set names 'tis620' ");
$ck=mysql_query("select name,value from config ");
$config=array();
while(list($f,$v)=mysql_fetch_array($ck)) $config[$f]=$v;
require "include.php";

if($action=='checkval'){
	$q="select $fld from $tb where id='$id' ";
	$out=qval($q);
	$json=array('data'=>$out);
}
if($json){
	header('Content-Type: application/json; charset=UTF-8');
	print json_encode($json);
	exit;
}
print "<meta http-equiv=content-type content=text/html;charset=windows-874 >";
if($action=='alllist'){
	$q="select id,name from $tb order by name ";
	if($tbcode) $q="select id,code from $tb where code<>'' order by code ";
	if($tb=='driver')	$q="select id,name from employee where type='$tb' and resign=0 order by name ";
	$rs.="<option>";
	$rs.=qoptions($q,$val);
	print $rs;
}
if($action=='rmdriver'){
	$tbitem=$tb.'item';
	$q="delete from $tbitem where driver='$driver' and logs='$sid'  ";
	mysql_query($q);
}
if($action=='rmalldriver'){
	$tbitem=$tb.'item';
	$q="delete from $tbitem where logs='$sid'  ";
	mysql_query($q);
}
if($action=='adddriver'){
	$tbitem=$tb.'item';
	$q="delete from $tbitem where driver='$driver' and logs='$sid'  ";
	mysql_query($q);
	$q="insert into $tbitem (driver,logs) values ('$driver','$sid') ";
	mysql_query($q);
}
if($action=='driverlist'){
	$type='driver';
	if(!$contract){
		if($vehicle) $contract=tbval('vehicle','contract',$vehicle);
	}
	if($contract=='true') $contract=1;
	
	if($contract==1) $type='cdriver';
	//print "vehicle $vehicle contract $contract ";
	$q=" select id,name from employee where resign=0 and type='$type' order by name ";
	if($customer) $q=" select t2.id,t2.name from vehicle as t1,employee as t2 where t2.id=t1.driver and t2.resign=0 and  t1.customer='$customer' order by name ";
	
	$out="<option>";
	$ck=mysql_query($q); //print $q;
	if(!$ck) print $q;
	if(mysql_num_rows($ck)==0) print $q;
	
	while(list($i,$t)=mysql_fetch_array($ck)){
		if($i==$val) $out .="<option value=$i selected >$t";
		else $out .="<option value=$i  >$t";
	}
	print $out;
}

mysql_close();
?>