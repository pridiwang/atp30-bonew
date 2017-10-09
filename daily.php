<?php
ini_set("display_errors",On);
ini_set("default_charset",'TIS-620');

error_reporting(E_ERROR | E_WARNING | E_PARSE);
print "starting ";
extract($_POST); extract($_GET);
require "config.php";
mysql_connect('localhost',$dbuser,$dbpwd) ;
mysql_select_db($dbname);
mysql_query(" set names 'tis620' ");
require "include.php";
$db1="atp30_web";
$db2="atp30_zk";
print "<meta http-equiv=content-type content=text/html;charset=windows-874 >";
//mysql_query("truncate table ta ");

/*
for($d=1;$d<32;$d++){
	$dd=$d;
	if($d<10) $dd="0".$d;
	$date="2013-08-".$dd;
	print "<h2>$date </h2>";
	dump($date);
}
*/
if(!$date) $date=date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));
dump($date,$badge);
if($action=='dump0'){
	$q=" update atp30_zk.checkinout set checktype='I' where date_format(checktime-interval 2 hour ,'%H:%i')< '12:00' " ;
	mysql_query($q);
	$q=" update atp30_zk.checkinout set checktype='O' where date_format(checktime-interval 2 hour ,'%H:%i')>= '12:00' " ;
	mysql_query($q);
	mysql_query("delete from $db1.ta where date='$date' ");
	$q=" insert into $db1.ta   (employee,badgenumber,date,in_time,out_time) 
	select t3.id,right(t2.badgenumber,3),'$date'
	,$db2.intime1(t2.userid,'$date') 
	,$db2.outtime1(t2.userid,'$date') 
	from $db2.userinfo as t2 ,$db1.employee as t3 where t3.badgenumber=t2.badgenumber
	and t3.resign=0  order by t2.badgenumber  ";
	$ck=mysql_query($q); //print $q;
	print "$date : ".mysql_affected_rows()."<br>";
	$action='eval';
}

if($action=='eval'){
	$q="select t1.id,t1.employee,t1.in_time,t1.out_time,t3.in_time,t3.out_time,date_format(t1.date,'%w')
FROM ta as t1, work_table as t2,timetable as t3
WHERE t2.employee=t1.employee and t3.id=t2.timetable
and t1.date between t2.date and t2.end_date
and t1.date='$date' ";

	$ck=mysql_query($q); print $q;
	while(list($id,$emp,$i1,$o1,$i2,$o2,$w)=mysql_fetch_array($ck)){
		if(($w==0)||($w==6)) continue;
		$r='';
		if($i1=='00:00:00') $i1='';
		if($o1=='00:00:00') $o1='';
		if(($i1)&&($o1)){
			if($i1>$i2) $r='L';
			if($o1<$o2) $r='E';
		}
		if((!$i1)&&($o1)) $r='L';
		if(($i1)&&(!$o1)) $r='E';
		if((!$i1)&&(!$o1)) $r='A';
		if($r) mysql_query("update ta set remark='$r' where id='$id' ");
		print "$id,$emp,$i1,$o1,$i2.$o2,$w, - $r<br>";
		
	}

}


mysql_close();
?>