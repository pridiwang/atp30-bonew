<?php
if($action=='mtn-plan'){
	array_push($controlflds,'workorder');
	print "<table width=100%><tr><td>Plan</td><td>";
	//$cmprmt.="&status=$s";
	datenav();
	print "</td><td>";
	if($_GET[status]) $_SESSION[status]=$_GET[status];	
	$status=$_SESSION[status];
	
	if($_GET[location]) $_SESSION[location]=$_GET[location];
	$location=$_SESSION[location];
	print "Place:<a href=?action=$action&status=new&date=$date&location=&>New</a> | ";
	while(list(,$l)=each($llist)){
		if($location==$l) print "<b>$l</b> | "; 
		else print "<a href=?action=$action&status=plan&location=$l&date=$date>$l</a> | ";
	}

	$slist=array('new','plan','done','complete');
	print "</td><td align=right>Status:";
	while(list(,$l)=each($slist)){
		
		if($status==$l) print "<b>$l</b> | ";
		else print "<a href=?action=$action&status=".urlencode($l)."&location=$location&date=$date>$l</a> | ";
	}
	if($_GET[location]) $_SESSION[location]=$_GET[location];
	if($status!='new'){ 
		$location=$_SESSION[location];
		$cond .=" and plan_date='$date' ";
	}
	else $location='';
	print "</td></tr></table>";
		//$cond .="  and t1.plan_date='$date' ";
	
	if($status) $cond .=" and t1.status='$status' ";
	if($location) $cond.="	and t1.plan_location='$location' ";
	$cond .=" and t3.fleet='$_SESSION[fleet]' ";
	$q=" select t1.id,t1.workorder 'Wo#',t3.code 'bus' ,t1.request_date,t1.plan_date 'plan date',to_days(t1.plan_date)-to_days(t1.request_date) 'days',t1.request_description
		,t1.plan_location,t1.plan_date,t1.plan_detail,t1.plan_parts,t1.status
		
		from mtn as t1,workorder as t2, vehicle as t3 
	where t2.id=t1.workorder and t3.id=t1.vehicle $cond ";
	
	$tb='mtn';$toaction="edit&tb=$tb";
	browse($q,1); //print $q.'<br>';
}
?>
