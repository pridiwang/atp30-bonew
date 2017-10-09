<?php
$version='2.1.0';
$systemname="BO New";
if($_SERVER[SERVER_ADDR]!='122.155.12.149') $systemname="BO Backup";
$dbuser='root';
$dbpwd='ks2789';
$dbname='atp30-mtn';
$dbuser="atp30_web";
$dbpwd="t3aybryfeHsb7Dh7";
$dbname="atp30_bonew";
$rpluser='atp30_rpl';
$rplpwd='bmArvDceUwRWR3BA';
/*
change master to master_host='122.155.12.149',master_user='atp30_rpl',master_password='bmArvDceUwRWR3BA',master_log_file='mysql-bin.000089',master_log_pos=94445623;
*/
$tbflds=array('vehicle','workorder','customer','route','trip','course','employee','contractor','timetable','fleet','st','to_fleet');
$tbcode=array('customer','vehicle','workorder','contractor','timetable','fleet');
$tbname=array('supplier','route','driver','course','employee','fleet');
$tbcodefleet=array('vehicle','customer');

$tbno=array('trip');
$fleettbs=array('vehicle','customer','accident','employee','user','accident','streq','breakdown','nc');
$vehicletbs=array('workorder','sparepart','milage','fuel','tire','battery');
$employeetbs=array('training','history','work_table');
$custtbs=array('route','trip','plan');
$controlflds=array('oid','ovehicle','ocustomer','active','logs','sup','opstaff','location','fleet','seq','trclass','oemp');
if($tb=='vehicle') array_push($controlflds,'type');
$hiddenflds=array('vehicle','workorder','record_by');
if(in_array($tb,array('job','breakdown'))) $hiddenflds=array();
$skipbrowse=array('plan_date','labor','contract');
$calflds=array('last_milage','total_milage','consumption');
$datetbs=array('job','battery','tire','streq');
$battery_life=23;
$employeeflds=array('employee','driver','c-driver','supervisor');
$bigreports=array('workorder');
$sumflds=array('qty');
$yeartbs=array('breakdown','suggest');
$allowedittbs=array('suggest','accident');
$sessflds=array('customer','date','driver');
$phototbs=array('mtn','ws_alcohol','ws_drug');
$doctbs=array('vehicle');
$fchangtbs=array('vehicle','driver','employee','user');
$codecontrol=array('vehicle','customer','form');
$rqflds=array('code','plate');
$attachtbs=array('accident','vehicle','form','nc');
$global_options=array('timetable');
$itemtbs=array('streq');
$printtbs=array('streq');
$strflds=array('part_no','book_no','no','number');
if($tb=='streq'){
	if($_SESSION[fleet]==0) $hiddenflds=array('type','vehicle','book_no');
	else $hiddenflds=array('type','to_fleet');
}
$statuscontroltbs=array('streq');

$deptflds=array('issue_dept','response_dept');
$llist=array('Nakorn','Bangpra','Maptaphut','Prachinburi','Others');
?>
