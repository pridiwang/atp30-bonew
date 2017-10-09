<?php
function plan2($customer,$route,$mo,$yr){
	mysql_query($q);
	$q="select id,no,name,date_format(start_time,'%H:%i'),in_out from trip where customer='$customer' and route='$route' order by no ";
	$ck=mysql_query($q); //print $q;
	print "<table class=tb3><tr class=bd><td> *
	<span class=plan>Plan</span> |
	<span class=done>Done</span> |
	<span class=cancel>Cancel</span> |
	</td></tr></table>";
	print "<table class=tb3><thead><tr class=hd><th rowspan=2>Date</th><th>Trip</th>";
	$trips=array();
	while(list($i,$n,$t,$time,$io)=mysql_fetch_array($ck)){
		print "<th>#$n ($io)<br>$t </th>";
		$trips[$i]=$time;
		$inout[$i]=$io;
	}
	print "</tr><tr class=hd><th>Time</th>";
	while(list($trip,$time)=each($trips)) print "<th>$time</th>";
	print "</thead><tbody>";
	$today=date("Y-m-d");
	for($i=1;$i< 32;$i++){
		if(!checkdate($mo,$i,$yr)) continue;
		$d=$i;
		if($i< 10) $d="0".$i;
		$date="$yr-$mo-$d";
		$w=date("w",mktime(0,0,0,$mo,$i,$yr));
		$wlist=array('อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสฯ','ศุกร์','เสาร์');		
		print "<tr class='bd dow$w' align=center><td align=right>$i<br><a href=?action=plan-copy&customer=$customer&route=$route&fromdate=$date>copy</a></td><td>$wlist[$w]</td>";
		$val='';
		reset($trips);
		$tb='plan';
		while(list($trip,$time)=each($trips)){
			$q="delete from plan where customer='$customer' and route='$route' and trip='$trip' and date='$date' and vehicle=0 ";
			mysql_query($q); //print $q;

			$q="select t1.id,t1.vehicle,t1.driver,t1.status  from plan as t1 where  t1.customer='$customer' and t1.route='$route' and t1.trip='$trip' and t1.date='$date'  ";
			$ck=mysql_query($q); //print $q;
			//window.location.href='?action=edit&tb=plan&id=$plan&date=$date&mo=$mo&yr=$yr';
			if(mysql_num_rows($ck)>0){
				$found=mysql_num_rows($ck);
				list($plan,$vehicle,$driver,$st)=mysql_fetch_array($ck);
				$dr=tbval('employee','name',$driver);
				$vh=tbval('vehicle','code',$vehicle);
				$onclick="window.location.href='?action=plan-edit&plan=$plan';";

//>
			}else{
				$vh='';$dr='';$st='';

			}
			if(($vh)){  //&&($st=='done')
				$vh=$found;
				$sum[$trip]+=$found;
			}else{
				$vh='';
			}
			print "<td  onclick=window.location.href='?action=plan-check&trip=$trip&date=$date'; onmouseover=this.style.background='#faa'; onmouseout=this.style.background=''; class='tdplan'><span class=$st>$vh</span></td>";
		}
		print "</tr>";
	}
	print "</tbody><tfoot><tr><td colspan=2>Total ".array_sum($sum)."</td>";
	reset($trips);
	while(list($trip,$time)=each($trips)){
		print "<td>$sum[$trip]</td>";
		$sums+=$sum[$trip];
	}
	print "</tfoot></tr></table>";
}

function plan($customer,$route,$mo,$yr){
	unset($_SESSION[customer]);
	$q=" update plan as t1,trip as t2
set t1.price=t2.price
,t1.cost=t2.cost
,t1.km=t2.standard_distance
,t1.allowance=t2.allowance
where t2.id=t1.trip";
	mysql_query($q);
	$q="select t1.id,t1.no,t2.name,date_format(t1.start_time,'%H:%i'),t1.in_out from trip as t1,route as t2 where t2.id=t1.route and t1.customer='$customer' and t1.route='$route' order by t1.no ";
	$ck=mysql_query($q); //print $q;
	print "<table class=tb3><tr class=bd><td> *
	<span class=plan>Plan</span> |
	<span class=done>Done</span> |
	<span class=cancel>Cancel</span> |
	</td></tr></table>";
	print "<table class=tb3><thead><tr class=hd><th rowspan=2>Date</th><th>Trip</th>";
	$trips=array();
	while(list($i,$n,$t,$time,$io)=mysql_fetch_array($ck)){
		print "<th>#$n ($io)<br>$t </th>";
		$trips[$i]=$time;
		$inout[$i]=$io;
	}
	print "</tr><tr class=hd><th>Time</th>";
	while(list($trip,$time)=each($trips)) print "<th>$time</th>";
	print "</thead><tbody>";
	$today=date("Y-m-d");
	for($i=1;$i< 32;$i++){
		if(!checkdate($mo,$i,$yr)) continue;
		$d=$i;
		if($i< 10) $d="0".$i;
		$date="$yr-$mo-$d";
		$w=date("w",mktime(0,0,0,$mo,$i,$yr));
		$wlist=array('อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสฯ','ศุกร์','เสาร์');
		print "<tr class='bd dow$w' align=center><td align=right>$i<br><a href=?action=plan-copy&customer=$customer&route=$route&fromdate=$date>copy</a></td><td>$wlist[$w]</td>";
		$val='';
		reset($trips);
		$tb='plan';
		while(list($trip,$time)=each($trips)){
			$q="delete from plan where customer='$customer' and route='$route' and trip='$trip' and date='$date' and vehicle=0 ";
			mysql_query($q); //print $q;

			$q="select t1.id,t1.vehicle,t1.driver,t1.status  from plan as t1 where  t1.customer='$customer' and t1.route='$route' and t1.trip='$trip' and t1.date='$date'  ";
			$ck=mysql_query($q); //print $q;
			//window.location.href='?action=edit&tb=plan&id=$plan&date=$date&mo=$mo&yr=$yr';
			$f='';
			if(mysql_num_rows($ck)>0){
				$found=mysql_num_rows($ck);
				if($found==1)$f='';
				else $f="<a href=?action=plan-check&trip=$trip&date=$date>($found)</a>";
				list($plan,$vehicle,$driver,$st)=mysql_fetch_array($ck);
				$dr=tbval('employee','name',$driver);
				$vh=tbval('vehicle','code',$vehicle);
				$onclick="window.location.href='?action=plan-edit&plan=$plan';";
				if($date<= $today)	$onclick="window.location.href='?action=edit&tb=plan&id=$plan';";
//>
			}else{
				$vh='';$dr='';$st='';
				$onclick="window.location.href='?action=plan-add&customer=$customer&route=$route&trip=$trip&date=$date';";
			}
			print "<td onclick=\"$onclick\" onmouseover=this.style.background='#faa'; onmouseout=this.style.background=''; class='tdplan'><span class=$st>$vh<br>$dr $f </span></td>";
		}
		print "</tr>";
	}
	print "</tbody></table>";
}
function plancancel($customer,$route,$mo,$yr){
	$q="select t1.id  ,t1.date,t2.name,t3.name,t4.code,t5.name,t1.status
from plan as t1 ,trip as t2, route as t3, vehicle as t4, driver as t5
where  t2.id=t1.trip and t3.id=t1.route and t4.id=t1.vehicle and t5.id=t1.driver
and t1.customer='$customer' and t1.route='$route' and date_format(t1.date,'%Y-%m')='$yr-$mo' and t1.status='cancel' order by t1.date ";
	$ck=mysql_query($q); //print $q;
	print "<table class=tb3><thead><tr class=hd><th colspan=6>Cancel List</th></tr><tr class=hd><th >Date</th><th >Trip</th><th >Route</th><th >Vehicle</th><th >Driver</th><th>Status</th></tr></thead><tbody>";
	while(list($plan,$date,$trip,$route,$vh,$driver,$status)=mysql_fetch_array($ck)){
		$onclick="window.location.href='?action=edit&tb=plan&id=$plan';";
		print "<tr class=bd onclick=$onclick
onmouseover=this.className='bda';
onmouseout=this.className='bd';

><td >$date</td><td >$trip</td><td >$route</td><td >$vh</td><td >$driver</td><td class=$status>$status</td></tr>";
	}
	print "</tbody></table>";
}
function editplan($plan){
	$q="select id,vehicle,driver from plan where id='$plan' ";
	$ck=mysql_query($q); print $q;
	list($plan,$vehicle,$driver)=mysql_fetch_array($ck);
	print "driver $driver<table><form action=?action=plan-save&plan=$plan&customer=$customer&route=$route&trip=$trip&date=$date method=post>
<tr><td><a href=?action=delete&tb=plan&id=$plan onclick=\"return confirm('confirm delete?')\">delete</a></td></tr>
<tr><td>".flddict('vehicle')."</td><td><select name=vehicle >".tboptions2('vehicle',$vehicle)."</select></td></tr>
<tr><td>".flddict('driver')."</td><td><select name=driver >".qoptions("select id,name from employee where type='driver' and resign=0 order by name ",$driver)."</select></td></tr>
<tr><td><input type=submit value=Save></td></tr>
</form></table>
	";
}

if($action=='route'){
	print "<table><form action=?action=$action method=get><input type=hidden name=action value=$action><tr><td> Select Customer <select name=customer onchange=this.form.submit();><option>...".tboptions2('customer',$customer)."</select></td><td>$vinfo ";
	print "</td></tr></form></table>";
	$tb=$action;
	if(($customer)){
		print "<a href=?action=new&tb=$tb&customer=$customer> + Record New ".ucfirst($tb)." + </a>";
		$toaction="edit&tb=$tb&customer=$customer";
		browse("select * from $tb where customer='$customer'  ",$tb);
	}

}
if($action=='trip'){
	print "<table><form action=?action=$action method=get><input type=hidden name=action value=$action><tr><td> Select Customer <select name=customer onchange=this.form.submit();><option>...".qoptions("select id,code from customer where fleet=$_SESSION[fleet] order by code ",$customer)."</select></td><td>route</td><td><select name=route onchange=this.form.submit(); ><option>..".qoptions("select id,name from route where customer='$customer' order by name ",$route)."</select></td></tr></form></table>";
	$tb=$action;
	if(($customer)&&($route)){
		print "<a href=?action=new&tb=$tb&customer=$customer&route=$route> + Record New ".ucfirst($tb)." + </a>";
		$toaction="edit&tb=$tb&customer=$customer&route=$route";

		browse("select * from $tb where customer='$customer' and route='$route'  order by no ",$tb);
	}

}
if($action=='plan-save'){
	$q=" update plan set vehicle='$vehicle', driver='$driver' where id='$plan' ";
	mysql_query($q);
	$q=" update plan as t1,trip as t2
set t1.price=t2.price
,t1.cost=t2.cost
,t1.km=t2.standard_distance,t1.allowance=t2.allowance
where t2.id=t1.trip and t1.id='$plan' ";
	mysql_query($q);
	$ck=mysql_query("select date_format(date,'%m'),date_format(date,'%Y'),customer,route,trip from plan where id='$plan' ");
	list($mo,$yr,$customer,$route,$trip)=mysql_fetch_array($ck);
	$action='plan';
}
if($action=='plan-delete'){
	$q=" delete from plan where date='$date' and customer='$customer' and route='$route' ";
	mysql_query($q); print $q;
}
if($action=='plan-paste'){
	$q=" delete from plan where date='$todate' and customer='$customer' and route='$route' ";
	mysql_query($q); //print $q;
	$q=" insert into plan (date,customer,route,trip,in_out,time,vehicle,driver,status,plan_by )
	select '$todate','$customer','$route', trip,in_out,time,vehicle,driver,'plan','$_SESSION[user]'
	from plan where customer='$customer' and route='$route' and date='$fromdate' ";
	mysql_query($q); //print $q;
	$q=" update plan as t1,trip as t2 set t1.price=t2.price ,t1.cost=t2.cost ,t1.km=t2.standard_distance,t1.allowance=t2.allowance where t2.id=t1.trip and t1.customer='$customer' and toute='$route' and date='$fromdate' ";
	mysql_query($q);
	$action='plan';
}
if($action=='plan'){
	print "<table><form action=?action=$action method=get><input type=hidden name=action value=$action><tr><td> Select Customer <select name=customer onchange=this.form.submit();><option value=''>...".qoptions("select id,code from customer where fleet=$_SESSION[fleet] order by code",$customer)."</select></td><td>route</td><td><select name=route onchange=this.form.submit(); ><option value=''>..".qoptions("select id,name from route where customer='$customer' order  by name ",$route)."</select></td><td>Month</td><td>".monthnav2()."</td><td><input type=submit value=go></td></tr></form></table>";
	$tb=$action;
	if(($customer)&&($route)){
		print "<a href=?action=new&tb=$tb&customer=$customer&route=$route&mo=$mo&yr=$yr> + Record New ".ucfirst($tb)." + </a>";
		$toaction="edit&tb=$tb&customer=$customer&route=$route&mo=$mo&yr=$yr";

		plan($customer,$route,$mo,$yr);
		plancancel($customer,$route,$mo,$yr);

	}

}
if($action=='plan2'){
	print "<table><form action=?action=$action method=get><input type=hidden name=action value=$action><tr><td> Select Customer <select name=customer onchange=this.form.submit();><option value=''>...".qoptions("select id,code from customer where fleet='$_SESSION[fleet]' order by code " ,$customer)."</select></td><td>route</td><td><select name=route onchange=this.form.submit(); ><option value=''>..".qoptions("select id,name from route where customer='$customer' order by name ",$route)."</select></td><td>Month</td><td>".monthnav2()."</td><td><input type=submit value=go></td></tr></form></table>"; 
	$tb=$action;
	if(($customer)&&($route)){
		print "<a href=?action=new&tb=$tb&customer=$customer&route=$route&mo=$mo&yr=$yr> + Record New ".ucfirst($tb)." + </a>";


		plan2($customer,$route,$mo,$yr);


	}

}
if($action=='plan-copy'){
	print "<form action=?action=plan-paste&customer=$customer&route=$route&fromdate=$fromdate method=post>
	to date<input type=text size=10 name=todate id=todate > <input type=submit value=Paste>
	</form><script>
	$('#todate').datepicker({dateFormat:'yy-mm-dd'});
	</script>
	";
}
if($action=='plan-add'){
	$ck=mysql_query("select t1.in_out,t1.start_time,t1.id,t2.id,t2.customer from trip as t1, route as t2 where t2.id=t1.route and t1.id='$trip' ");
	list($in_out,$start_time,$trip,$route,$customer)=mysql_fetch_array($ck);
	$q="select driver,vehicle from plan where trip='$trip' order by id desc limit 1 ";
	$ck=mysql_query($q);
	list($driver,$vehicle)=mysql_fetch_array($ck);
	$q="select id from plan where customer='$customer' and route='$route' and trip='$trip' and date='$date' and in_out='$in_out' ";
	$ck=mysql_query($q);
	if(mysql_num_rows($ck)==0){
		$q=" insert into plan
		(customer,route,trip,date,in_out,time,plan_start,status,plan_by,logs,vehicle,driver) values
		('$customer' ,'$route' ,'$trip' ,'$date' ,'$in_out' ,'$start_time' ,'$start_time', 'plan','$_SESSION[user]','$now added by $_SESSION[user]\n','$vehicle','$driver' )
		";

		mysql_query($q); //print $q;
		$plan=mysql_insert_id();
	}else{

		list($plan)=mysql_fetch_array($ck);
		print "plan# $plan existed";
	}
	$q=" update plan as t1,trip as t2 set t1.price=t2.price,t1.cost=t2.cost, t1.km=t2.standard_distance,t1.allowance=t2.allowance where t2.id=t1.trip and t1.id='$plan' ";
	mysql_query($q);

//	print "plan $plan ";
//	$action='plan-edit';
	$action='edit';$tb='plan';$id=$plan;
//	print "action $action tb $tb $id ";
}
if($action=='plan-edit'){
	editplan($plan);
}
if($action=='plan-check'){
	$q="select * from plan where trip='$trip' and date='$date' ";
	$toaction="edit&tb=plan";
	browse($q,'');
}
?>
