<?php
ini_set("display_errors",On); 
ini_set('mbstring.substitute_character', "none"); 
setlocale(LC_CTYPE, 'th_TH');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ERROR);
session_start(); 
extract($_POST); extract($_GET);
require "config.php";
mysql_connect('localhost',$dbuser,$dbpwd) ;
mysql_select_db($dbname);
mysql_query(" set names 'utf8' ");
$ck=mysql_query("select name,value from config ");
$config=array();
if($action=='fleetchange'){
	$_SESSION[fleet]=$_POST[fleet];
	$action=$_GET[fromaction];
	if($action=='log') $action='';
	if($action=='edit') $action=$tb;
}
while(list($f,$v)=mysql_fetch_array($ck)) $config[$f]=$v;
$pvar=array('vehicle','customer','route','mo','yr','vehicle','fleet');
//if($_POST[fleet]==0) $_SESSION[fleet]=0;
while(list(,$var)=each($pvar)){
	if($_POST[$var]) $_SESSION[$var]=$_POST[$var];
	if(!${$var}) ${$var}=$_SESSION[$var];
}


$gvar=array('mo','yr','menu','report','vehicle','fleet');
while(list(,$var)=each($gvar)){
	if($_GET[$var]) $_SESSION[$var]=$_GET[$var];
	if(!${$var}) ${$var}=$_SESSION[$var];
}
if(($_GET[action]=='report')&&(!$_GET[report])) $report='';
$fvar=array('vehicle','customer','route');
while(list(,$var)=each($fvar)){
//	if($action!='report') $_SESSION[$var]='';
}

//extract($_SESSION);
require "include.php";
$rnd=rand(1000,9999);
//<link type=text/css href=css/demo_page.css rel=Stylesheet />
//<link type=text/css href=css/demo_table_jui.css rel=Stylesheet />

print "<html><head><title>BackOffice</title>
<meta http-equiv=content-type content=text/html;charset=UTF-8 >

<link type=text/css href=css/ui-darkness/jquery-ui-1.8.13.custom.css rel=Stylesheet />
<link type=text/css href=css/demo_page.css rel=Stylesheet />
<script type=text/javascript src=js/jquery-1.6.1.min.js   ></script>
<script type=text/javascript src=js/jquery.dataTables.js  ></script>
<script type=text/javascript src=js/jquery-ui-1.8.16.custom.min.js></script>
<script type=text/javascript src=js/jquery-ui-timepicker-addon.js></script>
<script type=text/javascript src=js/css_browser_selector.js ></script>
<script type=text/javascript src=js/jquery.number.min.js ></script>
<script type=text/javascript src=js/include.js ></script>
<link rel=stylesheet type=text/css href=style.css?$rnd  />
<link rel=stylesheet type=text/css href=print.css?$rnd  media=print />
<meta http-equiv=\"X-UA-Compatible\" content=\"IE=9\"/>
<meta name=format-detection content=\"telephone=no\">
<link rel='shortcut icon' type=image/x-icon href=/favicon.ico>
</head>
<body>";

require "user.php";

if($_SESSION[afleets]<>'officer'){
	$foptions="<select onchange=this.form.submit(); name=fleet><option value=0>.".qoptions("select code,name from fleet",$fleet)."</select>";
}else{
	$foptions=qval("select name from fleet where id='$_SESSION[fleet]' ");
}
if($_SESSION[utype]=='customer'){
	
	$foptions='';
	//print_r($_SESSION);
	
}
$hdcolor='#f90';
if($systemname!='BO New')$hdcolor='#f00';
print "<div id=container><div id=header style=color:#f90;font-weight:bold; class='noprint'>
<Table width=100%><Tr><Td width=94><img src=images/logo450x240.jpg height=50 style=\"margin:0 5px;border:4px solid #fff;border-radius:5px;\"></Td><Td>
<table width=100%><tr><form action=?action=fleetchange&menu=$menu&sub=$sub&fromaction=$action&report=$report&ws=$ws&date=$date&tb=$tb&st=$st&date1=$date1&date2=$date2&id=$id method=post><td  style=color:#f90;font-weight:bold>ATP30 : $systemname
 > $foptions
</td></form><td align=right style=color:#f90;> $_SESSION[udept] / $_SESSION[user] / $_SESSION[ulevel] <a href=?action=change-pwd>Change Password</a> |
<a href=?action=logout>Log-Out</a> |
</td></tr></table>";
print "<div id=menubar>";

$menulist=array('mtn'=>'Maintenance','opr'=>'Operation','mgt'=>'Managment');
if($_SESSION[user]=='admin'){ $menulist=array('mtn'=>'Maintenance','opr'=>'Operation','hr'=>'HR');}
elseif($_SESSION[user]=='piya'){ $menulist=array('mtn'=>'Maintenance','opr'=>'Operation','hr'=>'HR');}

$menulist=array();
if(!$menu) $menu='mtn';
//if($_SESSION[udept]=='maintenance'){$menu='mtn';$menulist=array();}
//if($_SESSION[udept]=='operation'){$menu='opr';$menulist=array();}
if(strtolower($_SESSION[user])=='admin'){ $menulist=array('mtn'=>'Maintenance','opr'=>'Operation');}
if(strtolower($_SESSION[user])=='piya'){ $menulist=array('mtn'=>'Maintenance','opr'=>'Operation');}
$menulist=array('mtn'=>'Maintenance','opr'=>'Operation','hr'=>'HR','safety'=>'Safety','suggest'=>'Customer Suggestion','dcc'=>'DCC','nc'=>'NC');
if($_SESSION[utype]=='customer'){$menulist=array('suggest'=>'Customer Suggestion');$menu='suggest';}
while(list($a,$t)=each($menulist)){
	$add='';
	if($menu==$a) $add='menua';
	print "<a href=?menu=$a class='menu $add'>$t</a>  ";
}

print "</div>";
if(!$menu) $menu='mtn';

if($menu=='mtn'){
//,'mtn-plan'=>'Mtn Plan'
	unset($_SESSION[customer]);
	$actlist=array('history'=>'History','fuel'=>'Fuel', 'vehicle'=>'Vehicle', 'customer'=>'Customer', 'dashboard'=>'Dashboard','stock-balance'=>'Stock', 'breakdown'=>'BD Record','mtn-plan'=>'MTN Plan','report'=>'Report');
	if($_SESSION[utype]=='customer') $actlist=array('history'=>'History','report'=>'Report');


}
if($menu=='dcc'){
	$actlist=array('policy'=>'Policy','manual'=>'Manual','procedure'=>'Procedures','work_instruction'=>'Work Instruction','overview'=>'Overview','form'=>'Form',);
}
if($menu=='nc'){
	$actlist=array('nc'=>'NC','report'=>'Report');
}

if($menu=='opr'){

	$actlist=array('plan'=>'Daily Record','plan2'=>'Plan2','job'=>'Job','customer'=>'Customer','route'=>'Route','trip'=>'Trip','contractor'=>'Contractor', 'accident'=>'Accident Record','report'=>'Report','ws'=>'WorkSheet','12yim'=>'12ยิ้ม');
	if($_SESSION[udept]=='operation') $actlist=array('plan'=>'Plan','plan2'=>'Plan2','job'=>'Job','route'=>'Route','trip'=>'Trip',  'accident'=>'Accident Record','report'=>'Report','ws'=>'WorkSheet','12yim'=>'12ยิ้ม');
	if($_SESSION[udept]=='md') $actlist=array('plan'=>'Plan','plan2'=>'Plan2','job'=>'Job','route'=>'Route','trip'=>'Trip',  'accident'=>'Accident Record','report'=>'Report','ws'=>'WorkSheet','12yim'=>'12ยิ้ม');

}
if($menu=='safety'){
	$actlist=array('max_speed'=>'Speed & Distance','repair'=>'Repair','report'=>'Report');
}
if($menu=='suggest'){
	$actlist=array('suggest'=>'Customer Suggestions','report'=>'Report');
}

if($menu=='hr'){
$actlist=array('employee'=>'Employee', 'drivers'=>'Driver', 'cdriver'=>'C-Driver','course'=>'Course','report'=>'Report','resign'=>'Resign');
	if($_SESSION[ulevel]=='md') $actlist=array('employee'=>'Employee', 'drivers'=>'Driver', 'cdriver'=>'C-Driver','course'=>'Course','report'=>'Report','resign'=>'Resign','user'=>'User');
	if($_SESSION[ulevel]=='officer') $actlist=array('employee'=>'Employee', 'drivers'=>'Driver', 'cdriver'=>'C-Driver','course'=>'Course');

}

while(list($a,$t)=each($actlist)){
	if($action==$a) 	print "<a href=?menu=$menu&action=$a class=act>$t</a> | ";
	else print "<a href=?menu=$menu&action=$a>$t</a> | ";
}

print "<div id=sub>";
if($action=='report'){
	print " Report > ";
	$rptlist=array('plan'=>'Plan','part'=>'SparePart','part1'=>'Part1','part2'=>'Part2','part5'=>'Part3', 'workorder'=>'WorkOrder', 'consumption'=>'Fuel','tire'=>'Tire','battery'=>'Battery','stock'=>'Stock','bd'=>'BD','mtn'=>'MTN Plan');
//	if($_SESSION[utype]=='customer') $rptlist=array('plan'=>'Plan','part'=>'SparePart','workorder'=>'WorkOrder');
//	if($_SESSION[udept]=='maintenance')	$rptlist=array('plan'=>'Plan','part'=>'SparePart', 'workorder'=>'WorkOrder');
	if($menu=='opr'){
		$rptlist=array('daily'=>'Daily','monthly'=>'Monthly','mgt1'=>'Income/Customer','mgt2'=>'Income/bus','kpi'=>'KPI','kpi2'=>'KPI2','allowance'=>'Allowance','bill'=>'Billing','billed'=>'Contract Billing','mgt11'=>'B&V','mgt12'=>'B&V Ratio','accident'=>'Accident' );
	$rptlist=array('daily'=>'Daily','monthly'=>'Monthly','kpi'=>'KPI','accident'=>'Accident','ready'=>'Ready','month-mtn'=>'Month MTN','plan5'=>'Plan5' );

		if($_SESSION[ulevel]=='officer'){
		$rptlist=array('daily'=>'Daily','monthly'=>'Monthly','month-mtn'=>'Month MTN','plan5'=>'Plan5');
		}


	}
	if($menu=='hr'){
		$rptlist=array('list'=>'List','history'=>'Monthly','training'=>'Training','resign'=>'Resign','ta4'=>'Time Stamp','ta5'=>'สรุปสาย/ขาด','ta7'=>'for Payroll','userlog'=>'การเข้าใช้ระบบ' );
	}
	if($menu=='safety'){
		$rptlist=array('speedy'=>'Over Speed','speedy2'=>'Over Speed Frequency','speedy4'=>'Distance','speedy5'=>'Speed');
	}
	if($menu=='suggest'){
		$rptlist=array('suggest'=>'Suggestion Report');
	}
	if($menu=='nc'){
		//$rptlist=array('nc-internal_audit'=>'Internal Audit','nc-customer_complaints'=>'Customer Complaints', 'nc-other'=>'Other');
		$rptlist=array('nc'=>'NC All');
	}
	while(list($r,$t)=each($rptlist)){
		if($report==$r) print "<b>$t</b> | ";
		else print "<a href=?action=$action&$action=$r>$t</a> | ";
	}
}
if($action=='ws'){
	$wslist=array('alcohol'=>'ตรวจวัดแอลกอฮอล์','drug'=>'ตรวจวัดสารเสพติด','busweek'=>'ตรวจรถประจำสัปดาห์','busmonth'=>'ตรวจรถประจำเดือน','talk'=>'Safety Talk','report'=>'รายงาน','opstaff'=>'OP Staff');
	while(list($r,$t)=each($wslist)){
		if($ws==$r) print "<a href=?action=$action&$action=$r><b style=color:#f90;>$t</b></a> | ";
		else print "<a href=?action=$action&$action=$r>$t</a> | ";
	} 
	print "</div>";
	require "ws.php";

}

print "</div></Td></Tr></Table></div>";
if($msg) print "<div class=msg style=color:red;text-align:center; >$msg</div>";
require "stock.php";

require "plan.php";

$browsetbs=array('course','driver','customer','contractor','user','employee','drivers','cdriver','resign','suggest','repair','max_speed','accident','breakdown','vehicle','nc');

if(in_array($action,$browsetbs)){
	$tb=$action;
	$action='browse';
	if($tb=='employee'){ $type='office';}
	if($tb=='drivers'){$type='driver';$tb='employee';}
	if($tb=='cdriver'){$type='cdriver';$tb='employee';}
	if($tb=='employee')	$cond =" and type='$type' and resign=0 ";
	if($tb=='resign'){$tb='resign';$cond =" and resign=1 ";}
	if($tb=='suggest'){
		if($_SESSION[utype]=='customer') $cond .=" and customer='$_SESSION[cid]' ";
		
	}
	if($tb=='accident') $hiddenflds=array();
	if($tb=='vehicle') unset($_SESSION[customer]);
}
$formacts=array('form','policy','manual','procedure','work_instruction','overview');
if(in_array($action,$formacts)){
	$type=$action;
	
	$action='browse';
	$tb='form';
}



/*
if($action=='accident'){
	$tb=$action;
	$action='browse';
}
if($tb=='accident') $hiddenflds=array();

if($action=='breakdown'){
	$tb=$action;
	$action='browse';
}
if($tb=='breakdown') $hiddenflds=array();
if($action=='vehicle'){
	unset($_SESSION[customer]);
	$tb=$action;
	$action='browse';
}

if($tb=='employee'){
	$tb=$action; $cond =" and type='office' and resign=0 ";
	$type='office';
	$action='browse';
}
if($action=='drivers'){
	$tb='employee'; $cond=" and type='driver'  and resign=0 ";
	$type=$action;
	$action='browse';$type='driver';
}
if($action=='cdriver'){

	//print "<a href=?action=new&tb=employee class=addnew> + + Add new $action + + </a>";
	$tb='employee'; $cond=" and type='cdriver'  and resign=0 ";
	$type=$action;
	$action='browse';
}

if($action=='resign'){
	$tb='resign'; $cond=" and resign=1 ";
	$action='browse';
}
if($action=='suggest'){
	if($_SESSION[utype]=='customer') $cond .=" and customer='$_SESSION[cid]' ";
	$tb=$action;
	$action='browse';
}
if($action=='repair'){
	$tb=$action;
	$action='browse';
}
*/
if($action=='job'){
	print "<a href=?action=new&tb=$action&mo=$mo&yr=$yr class=addnew> + + Add new $action + + </a>";
	$tb=$action;
	job();
}
if($action=='stock'){
	$q="update stock set amount=balance*cost ";
	mysql_query($q); //print $q;
	$ck=mysql_query("select sum(balance*cost) from stock ");
	list($sum)=mysql_fetch_array($ck);
	print "<center><h2>Total Stock Value ".number_format($sum,2)." B.<h2></center> ";
	$tb=$action;
	$action='browse';
}

if(($action=='browse')&&($tb=='st')){
	$action='stock-balance';
}

if($action=='browse'){
	if($tb=='max_speed'){
		
		datenav();
		$q=" insert into max_speed (fleet,date,vehicle) select '$_SESSION[fleet]','$date',id from vehicle where fleet='$_SESSION[fleet]' and control_speed>0 and id not in (select vehicle from max_speed where date='$date' )   ";
		mysql_query($q); //print $q;
		$hiddenflds=array('logs');
	}
	
	
	$_SESSION[type]=$type;
	if($tb=='employee') $anew=ucfirst($type);
	elseif($tb=='streq'){
		$anew='Stock '.ucfirst($type);
	} 
	else $anew=ucfirst($tb);
	$isadd=1;
	if($tb=='form'){
		$isadd=0;
		$cond.=" and type='$type' ";
		$anew=ucfirst(str_replace('_',' ',$type));
		$hiddenflds=array('type','updated','is_new');
		if(in_array($_SESSION[udept],array('admin','dcc'))) $isadd=1;
	}
	if($tb=='nc'){
		//$isadd=0;
		//if(in_array($_SESSION[udept],array('admin','dcc'))) $isadd=1;
		$isadd=1;
	}
	
	if($isadd) print "<a href=?action=new&tb=$tb&type=$type ><button> + + Add New $anew + + </button></a>";
	$toaction="edit&tb=$tb";
	if($_SESSION[ulevel]=='officer') $toaction="view&tb=$tb";
	if(in_array($tb,$allowedittbs)) $toaction="edit&tb=$tb";

	if(in_array($tb,$yeartbs)){
		yearnav();
		$cond .=" and year(date)='$yr' ";
	}
	if((in_array($tb,$fleettbs))&&($tb!='streq')) $cond.=" and fleet='$_SESSION[fleet]' ";
	//,date_format(license_expire_date+interval 543 year,'%d/%m/%Y') 
	$q="select * from $tb where 1 $cond ";
	if($tb=='employee') $q="select id,name,concat('<img width=50  src=images/$tb/',id,'.jpg?$rnd >') 'Picture',type,position
	,date_format(birth+interval 543 year,'%Y/%m/%d') 'birth'
	,if(birth>'0000-00-00',year(now())-year(birth),'') 'age'
	,address
	,employed 'เริ่มงาน'
	,to_days(now())-to_days(employed) 'อายุงาน'
	,license_expire_date from $tb where 1 $cond order by name ";
	if(($tb=='employee')&&($type=='office')) $q="select id,name,concat('<img width=50  src=images/$tb/',id,'.jpg?$rnd >') 'Picture',type,position
	,date_format(birth+interval 543 year,'%d/%m/%Y') 'birth'
	,if(birth>'0000-00-00',year(now())-year(birth),'') 'age',address
	,date_format(employed+interval 543 year,'%Y/%m/%d') 'เริ่มงาน'
	,to_days(now())-to_days(employed) 'อายุงาน' from $tb where 1 $cond order by name ";

	if($tb=='resign'){
		$tb='employee';
		$toaction="edit&tb=$tb";
		$q="select id,name,concat('<img width=50  src=images/$tb/',id,'.jpg?$rnd >') 'Picture',type,position
	,date_format(birth+interval 543 year,'%d/%m/%Y') 'birth'
	,if(birth>'0000-00-00',year(now())-year(birth),'') 'age',address
	,date_format(employed+interval 543 year,'%Y/%m/%d') 'เริ่มงาน'
	,date_format(resign_date+interval 543 year,'%Y/%m/%d') 'ลาออก'
	,to_days(resign_date)-to_days(employed) 'อายุงาน'  from $tb where 1 $cond ";
		//print "tb $tb ";
	}

	if($tb=='max_speed') $q=" select * from $tb where date='$date' and fleet='$_SESSION[fleet]' ";
	if($_SESSION[utype]=='customer') $q=" select * from $tb where customer='$_SESSION[cid]' ";
	if($tb=='vehicle') $q="select id,code,plate,driver,customer,control_speed,last_pm_mile,next_pm_mile,register_expire,prb_expire,insurance_expire from $tb where fleet='$_SESSION[fleet]' ";
	if($tb=='stock') $q="select * from $tb where fleet='$_SESSION[fleet]' ";
	if($tb=='breakdown')$q=" select t1.* from $tb as t1,vehicle as t2 where t2.id=t1.vehicle and t2.fleet='$_SESSION[fleet]' and year(t1.date)='$yr' ";
	
	if($tb=='suggest'){
		if($_SESSION[utype]=='customer') $cond.=" and t2.id='$_SESSION[cid]' ";
		$q=" select t1.* from $tb as t1,customer as t2 where t2.id=t1.customer and t2.fleet='$_SESSION[fleet]' and year(t1.date)='$yr' $cond ";
	}
	if($tb=='nc'){
		yearnav();
		$q="select id, date,cast(id as char(5)) 'nc_number', report_detail,issue_by,issue_dept,response_by,response_dept,preventive_date 'กำหนดแล้วเสร็จ'
		,if(follow1_closed=1,'ปิด','') 'สถานะการติดตามครั้ง1'
		,if(follow2_closed=1,'ปิด','') 'สถานะการติดตามครั้ง2'
		from $tb where fleet='$_SESSION[fleet]' and year(date)='$yr' order by date desc ";
	}
	if($tb=='form'){
		$q="select id,code,name,start_date
		,if((updated<>'0000-00-00') and (to_days(now())-to_days(updated))<30,1,0) 'is_new' 
from $tb where type='$type' ";
	}
	browse($q,$tb); //print $q;
}
if($action=='view'){
	edit($tb,$id,1);
}
if($action=='new'){
	//$action='edit';
}
if($action=='new'){
	
	if(in_array($tb,$vehicletbs)) $q=" insert into $tb (date,vehicle,logs) values (now(),'$vehicle','$now added by $_SESSION[user]\n') ";
	elseif(in_array($tb,$employeetbs))$q=" insert into $tb (date,employee,logs) values (now(),'$employee','$now added by $_SESSION[user]\n') ";
	else $q=" insert into $tb (logs) values ('$now added by $_SESSION[user]\n') ";
	if($tb=='accident') $q=" insert into $tb (fleet) values ('$_SESSION[fleet]') ";
	if($tb=='vehicle') $q=" insert into $tb (fleet) values ('$_SESSION[fleet]') ";
	if($tb=='workorder') $q=" insert into $tb (date,request_date,request_by,vehicle,logs) values ( now(), now(), '$_SESSION[user]', '$vehicle','$now added by $_SESSION[user]\n') ";
	if($tb=='breakdown') $q=" insert into $tb (date,logs) values (now(),'$now added by $_SESSION[user]\n') ";
	if($tb=='streq'){
		
		$bkno=qval("select book_no from $tb where type='$type' and fleet='$_SESSION[fleet]' order by id desc limit 1 ");
		$no=qval("select number from $tb where type='$type' and fleet='$_SESSION[fleet]' order by id desc limit 1 ");
		$no++;
		$q=" insert into $tb (type,book_no,number,fleet,date,logs) values ('$type','$bkno','$no','$_SESSION[fleet]',now(),'$now added by $_SESSION[user]\n') ";
	}
	
	if($tb=='fuel'){
		mysql_query(" delete from $tb where vehicle='$vehicle' and milage=0 ");
		$lm=lastmile($vehicle);
		$q=" insert into $tb (last_milage,date,vehicle,logs) values ('$lm',now(),'$vehicle','$now added by $_SESSION[user]\n') ";
	}
	if($tb=='trip'){
		$ck=mysql_query("select max(no) from trip where customer='$customer' and route='$route' ");
		list($no)=mysql_fetch_array($ck);
		$no++;
		$q=" insert into $tb (customer,route,no,logs) values ('$customer','$route','$no','$now added by $_SESSION[user]\n') ";
	}
	if($tb=='route'){
		$q=" insert into $tb (customer,name,logs) values ('$customer','new $tb','$now added by $_SESSION[user]\n') ";
	}
	if($tb=='driver'){
		$q=" insert into $tb (name,logs) values ('new $tb','$now added by $_SESSION[user]\n') ";
	}
	if($tb=='job'){
		$tb='plan';
		$q=" insert into $tb (type,date,time,plan_by,logs) values ('extra',now(),'08:00','$_SESSION[user]','$now added by $_SESSION[user]\n') ";

	}
	if($tb=='suggest'){
		$q=" insert into $tb (date,customer,suggested_by,logs) values (now(),'$_SESSION[cid]','$_SESSION[user]','$now added by $_SESSION[user]\n' ) ";
	}
	if($tb=='nc')$q=" insert into $tb (fleet,date,status,logs) values ('$_SESSION[fleet]',now(),'report','$now added by $_SESSION[user]\n') ";
	if($tb=='form'){
		$q="insert into $tb (type,start_date,logs) values ('$type',now(),'$now added by $_SESSION[user]\n') ";
		//print $q;
	}

	mysql_query($q); //print $q;
	$id=mysql_insert_id();
	if($tb=='workorder'){
		$q=" insert into mtn (workorder,vehicle,logs) values ('$id','$vehicle','$now added by wo $id - $_SESSION[user]\n') ";
		mysql_query($q);
	}
	$action='edit';
}
if($action=='edit'){
	//print_r($_SESSION);
	$vo=0;
	if($tb=='suggest'){
		if($_SESSION[utype]=='customer') $vo=1;
		$sugby=qval("select suggested_by from $tb where id='$id' ");
		if($sugby==$_SESSION[user]) $vo=0;
		
	}
	
	edit($tb,$id,$vo);
	if($tb=='suggest'){
		print "<script>$('#suggested_by').attr('disabled','disabled');</script>";
	} 
}
if($action=='print'){
	tbprint($tb,$id);
}
if($action=='workorder'){
	daterange($date1,$date2);
	$toaction="edit&tb=workorder";
	browse("select * from workorder where date between '$date1' and '$date2'  order by date",$action);
}

if($action=='history') history();
if($action=='fuel'){
	fuel();
	if($csv) {
		$file="txt/report.csv";
		file_put_contents($file,iconv("UTF-8","TIS-620",$csv));
		print "<a href=$file?$rnd> export to CSV </a>";
	}
} 
if($action=='dashboard'){
	dashboard();
}
require "mtn.php";

if($action!='report') $report='';
require "report.php";
require "12yim.php";
require "util.php";
require "printform.php";
print "<script src=include.js?a=1 ></script>";
print "<div id=footer></div></div>";
//print "<div class=msg2 style=color:#ffa;>";print_r($_SESSION);print "</div>";

mysql_close();
?>
<iframe id=tmpframe name=temframe style=width:1000px;display:none;></iframe>
</body></html>
