<?php
$db1="atp30_web";
$db2="atp30_zk";
error_reporting(E_ERROR);
if($_GET[report]) $_SESSION[vehicle]='';
if($action=='settime-submit'){
	$q="update 	atp30_zk.userinfo 
	set in_time='$in_time', out_time='$out_time'
	where userid='$id' ";
	mysql_query($q);
	$action='report';$report='ta3';
}
if($action=='settime'){
	$q="select name,lastname,in_time,out_time from atp30_zk.userinfo where userid='$id' ";
	$ck=mysql_query($q);
	list($n,$l,$i,$o)=mysql_fetch_array($ck);
	print "<table class=tb3><form action=?action=settime-submit&id=$id method=post>
<tr><td>ชื่อ-นามสกุล</td><td>$n $l</td></tr>
<tr><td>เวลาเข้า</td><td><input type=text name=in_time value=$i></td></tr>	
<tr><td>เวลาออก</td><td><input type=text name=out_time value=$o></td></tr>	
<tr><td></td><td><input type=submit value=Save></td></tr></form></table>";


}

/*
delimiter //
create function fuelv1(v int,d1 date,d2 date) returns decimal(10,2) DETERMINISTIC 
begin
declare rs decimal(10,2);
select sum(price) into rs from fuel where vehicle=v and date between d1 and d2;
return rs;
end;
//
delimiter ;

delimiter $$

create function fuelv1(v int,d1 date,d2 date) returns real DETERMINISTIC 
begin
declare rs real;
select sum(price) into rs from fuel where vehicle=v and date between d1 and d2;
return rs;
end;
$$

delimiter ;

create function fuelv1(v int,d1 date,d2 date) 
returns decimal(10,2)
return select sum(price) from fuel where vehicle=v and date between d1 and d2;

*/


$tosum=array('amount','labor','km','liter');
$sumdigit=2;
function checkalert($report,$fld,$val){
	$q="select comp,value,color from alert where report='$report' and fld like '%%$fld%%' ";
	$ck=mysql_query($q); //print $q;
	if(mysql_num_rows($ck)==0) return '';
	while(list($comp,$value,$color)=mysql_fetch_array($ck)){
		if(($comp=='>')&&($val>$value)) return 'to'.$color;
		if(($comp=='<')&&($val<$value)) return 'to'.$color;
		if(($comp=='>=')&&($val>=$value)) return 'to'.$color;
		if(($comp=='<=')&&($val>=$value)) return 'to'.$color;
		if(($comp=='=')&&($val==$value)) return 'to'.$color;
		if(($comp=='<>')&&($val!=$value)) return 'to'.$color;
	}
	return '';
}
function numfor($in){
	return str_replace(",","",number_format($in,2));
}
function fuelv($vehicle,$date1,$date2){
	$q="select sum(price) from fuel where vehicle='$vehicle' and date between 'date1' and 'date2' ";
	$ck=mysql_query($q);
}
function thdate($date){
	$yr=substr($date,0,4);
	$mo=substr($date,5,2);
	$dd=substr($date,8,2);
	$d=$dd+1-1;
	$mlist=array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$mlist=array('','มค.' ,'กพ.' ,'มีค.' ,'เม.ย.' ,'พค.' ,'มิย.' ,'กค.' ,'สค.' ,'กย.' ,'ตค.' ,'พย.' ,'ธค.');
	return "$d $mlist[$mo] $yr";
}
function stdreport($q){
	global $toreport, $tosum, $addpar, $prmt,$minwidth,$tored,$tolink,$report,$tbid,$report,$intflds,$sumdigit,$toaction;
	$ck=mysql_query($q);
	if(!$ck){
		print $q.' '.mysql_error();
	} 
	if(!$tbid) $tbid='tb1';
	print "<table class='rpt $report dt' id=$tbid ><thead><tr><th>No.</th>";
	$wflds=array('vehicle','amount','labor','qty','start','end','km','liter');
	$txt.="no\t";
	$txt2.="no,";

	for($j=1;$j< mysql_num_fields($ck);$j++){
		$fld=mysql_field_name($ck,$j);
		$ftype=mysql_field_type($ck,$j);
		$add="";
		if($ftype=='blob') $add="width=250";
		if(in_array($fld,$wflds)) $add="width=60";
		if($fld=='parts') $add="width=250";
		if($minwidth) $add="width=$minwidth"; 
		print "<th >".flddict($fld)."</th>";
		$txt .=flddict($fld)."\t";
		$txt2 .=flddict($fld).",";
	}
	$txt .="\n";
	$txt2 .="\n";
	print "</tr></thead><tbody>";
	for($i=0;$i< mysql_num_rows($ck);$i++){
		$id=mysql_result($ck,$i,0);
		$onclick='';
		$k=$i+1;
		if($toreport) $onclick="window.location.href='?action=report&report=$toreport&id=".urlencode($id)."&$prmt';";
		if($tolink) $onclick="window.location.href='$tolink".urlencode($id)."'";
		if($toaction) $onclick="window.location.href='$toaction".urlencode($id)."'";
		print "<tr class=bd onmouseover=this.className='bda'; onmouseout=this.className='bd'; onclick=\"$onclick\"><td>$k</td>";
		$txt.=$k."\t"; 
		$txt2.=$k.",";
		if($report=='tire'){
			$vcode=mysql_result($ck,$i,1);
			$vtype=substr($vcode,0,2);
		}
		for($j=1;$j< mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			$ftype=mysql_field_type($ck,$j);
			$val=mysql_result($ck,$i,$j);
			if(in_array($fld,$intflds)) $ftype='int';
			if(in_array($fld,$tosum)) $sum[$fld]+=$val;
			if($j>1) $aocolumns .=", ";
			if($ftype=='real'){
				$val=numfor($val);
				if($val==0) $val='';
			}
			if($ftype=='int'){
				if($val==0) $val='';
			}
//			if($ftype=='real') $val=round($val,2);
			if(substr($val,0,5)=='fuelv') {
				$inf=explode("|",$val);
				$val=fuelv($inf[0],$inf[1],$inf[2]);
			}
			$r='';
			if(($ftype=='int')&&($val>$tored)) $r="tored";
			$r=checkalert($report,$fld,$val);
            
            
			if($val=='00:00') $val='';
			$addclass='';
			if( in_array($fld,array('FL','FR')) && ($val>80000)) $addclass='red';
			if( in_array($fld,array('RLI','RLO','RRI','RRO')) && ($val>150000)) $addclass='red';
			if((substr($vtype,0,1)=='V')&&($j>1)){
				if($val>70000) $addclass='red';
			}
			print "<td class='$ftype $r $fld $addclass' value='$val' > $val</td>";
			$val=str_replace(",","",$val);
			$val=str_replace("\r\n ","",$val);
			$val=str_replace("\r\n",", ",$val);
			$val=str_replace("\t","",$val);
			$txt .=$val."\t";
			$txt2 .=$val.",";
		}
		$txt .=chr(13);
		$txt2 .=chr(13);
		print "</tr>";
	}
	print "</tbody><tfoot><tr><td></td>";
	$txt .="\t";
	$txt2.=",";
	
	for($j=1;$j< mysql_num_fields($ck);$j++){
		$fld=mysql_field_name($ck,$j);
		$ftype=mysql_field_type($ck,$j);
		$val='';$val2='';
		
		if(in_array($fld,$tosum)){
			$val=$sum[$fld];
			if($fld=='consumed') $val=$sum[km]/$sum[liter];
			if(in_array($fld,$intflds))$val2=number_format($val,0);
			else $val2=number_format($val,2);
		}
		print "<td class='$fld' >$val2</td>";
		$txt .=$val."\t";
		$txt2 .=$val.",";
	}
	$txt .="\n";
	$txt2 .="\n";
	print "</tr></tfoot></table>";
	//print "<pre>txt \n$txt \ntxt2 \n$txt2</pre>";
	print "<script>
$(document).ready(function() {
	$('#$tbid').dataTable({bPaginate:false $addpar });
} );

</script>";
	$txt=strip_tags($txt);
	$txt= mb_convert_encoding($txt, 'UTF-8', 'UTF-8'); 
	$txt=iconv("UTF-8","TIS-620",$txt);
	//$txt=mb_convert_encoding($txt,"CP874");
	//print "<pre>txt \n$txt \ntxt2 \n$txt2</pre>";
	$file="report-".$_SESSION[uid].".xls";
	$dir="txt";
	$floc=$dir."/".$file;
	file_put_contents($floc,$txt);
	$filename=rand(1000,9999).".xls";
	print "<br><a href=dl.php?dir=$dir&file=$file&filename=$filename&$rnd>download excel</a>";
	
	$file2="report-".$_SESSION[uid].".csv";
	$dir="txt";
	$floc2=$dir."/".$file2;
	$txt2=strip_tags($txt2);
	$txt2=iconv("UTF-8","TIS-620",$txt2);
	file_put_contents($floc2,$txt2);
	$file="report.csv";
	$dir="txt";
	$filename=rand(1000,9999).".csv";
	print "<br><a href=dl.php?dir=$dir&file=$file2&filename=$filename>download csv</a>";
	//print "<pre>txt \n$txt \ntxt2 \n$txt2</pre>";
	//<div class=msg2 style=color:#bbb; >$q</div>";
}

function monthnav_report(){
	global $mo,$yr,$action,$cmprmt,$report,$etype,$badge;
	if(!$mo) $mo=date("m");
	if(!$yr) $yr=date("Y");
	$nmo=$mo+1;$nyr=$yr;
	if($nmo>12){ $nmo=1; $nyr++;}
	$pmo=$mo-1;$pyr=$yr;
	if($pmo< 1){ $pmo=12; $pyr--;}
	if($pmo<10) $pmo="0".$pmo;
	if($nmo<10) $nmo="0".$nmo;

	$out ="<table><form action=?action=$action&report=$report&etype=$etype&badge=$badge method=post ><tr>
<td><input type=button value=&lt; onclick=\"window.location.href='?action=$action&report=$report&mo=$pmo&yr=$pyr&etype=$etype&badge=$badge';\"></td>
<td><select name=mo onchange=this.form.submit();>";
	$mlist=array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	for($i=1;$i< 13;$i++){
		$ii=$i;
		if($i< 10) $ii="0".$i;
		if($mo==$i) $out .="<option value=$ii selected>$mlist[$i]";
		else $out .="<option value=$ii>$mlist[$i]";
	}
	$out .="</select><select name=yr  onchange=this.form.submit();>";
	for($i=($yr-10);$i< ($yr+10);$i++){
		if($yr==$i) $out .="<option value=$i selected>$i";
		else $out .="<option value=$i>$i";

	}
	$out .="</select></td>
<td><input type=button value=&gt; onclick=\"window.location.href='?action=$action&report=$report&mo=$nmo&yr=$nyr&etype=$etype&badge=$badge';\"></td>

</tr></form></table>";
	print $out;
}
function chkwo($vehicle,$date){

	$q="select id,status,description from workorder where vehicle='$vehicle' and date='$date' ";
	$ck=mysql_query($q); //print $q;
	while(list($wo,$st,$desc)=mysql_fetch_array($ck)){
		$out .="<a href=?action=edit&tb=workorder&id=$wo class=wo-$st title=\"$desc\">$wo</a> ";
	}
	return $out;
}
if(($menu=='hr')&&($action=='report')){
	if(!$etype) $etype='office';
	$coffice=array('office'=>'checked','driver'=>'','cdriver'=>'');
	$cdriver=array('office'=>'','driver'=>'checked','cdriver'=>'');
	$ccdriver=array('office'=>'','driver'=>'','cdriver'=>'checked');
	$cond.=" and t2.type='$etype' ";
	print "<form action=?action=$action&report=$report&course=$course&mo=$mo&yr=$yr method=post >Type 
	<input name=etype type=radio value=office $coffice[$etype] onchange=this.form.submit(); >Office 
	<input type=radio name=etype value=driver $cdriver[$etype] onchange=this.form.submit(); >Driver 
	<input type=radio name=etype value=cdriver $ccdriver[$etype] onchange=this.form.submit(); >C-Driver 
	</form>";
}
if(($report)&&($_SESSION[fleet])){
	$cond .=" and t2.fleet='$_SESSION[fleet]' ";
} 

if($report=='plan'){
	monthnav();
	print "<table class=tb1><thead><tr><th>$mo/$yr</th>";
	for($d=1;$d< 32;$d++){
		if(!checkdate($mo,$d,$yr)) continue;
		$dow=date("D",mktime(0,0,0,$mo,$d,$yr));
		print "<th>$d<br>$dow</th>";
	}
	print "</tr></thead><tbody>";
	if($_SESSION[cid]) $cond .=" and t2.customer='$_SESSION[cid]' ";
	$ck=mysql_query("select t2.id,t2.code from vehicle as t2 where t2.contract=0 $cond and t2.code<>'' order by t2.type,t2.seq ");
	while(list($v,$code)=mysql_fetch_array($ck)){
		print "<tr class=bd onmouseover=this.className='bda'; onmouseout=this.className='bd'; ><td>$code</td>";
		for($d=1;$d< 32;$d++){
			if(!checkdate($mo,$d,$yr)) continue;
			$dd=$d;
			if($d< 10) $dd="0".$d;
			$date="$yr-$mo-$dd";
			$wo=chkwo($v,$date);
			print "<td>$wo</td>";
		}
		print "</tr>";
	}
	print "</tbody></table>";
}
if($report=='plan2'){
	if(!$date1) $date1=date("Y-m-d", mktime(0,0,0,date("n"),date("d")-date("w"),date("Y") ));
	if(!$date2) $date2=date("Y-m-d", mktime(0,0,0,date("n"),date("d")-date("w")+6,date("Y") ));
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="select date from workorder where date between '$date1' and '$date2' ";
	$ck=mysql_query($q);
	while(list($d)=mysql_fetch_array($ck)) array_push($dates,$d);
	$q="select t2.code,
	
from  workorder as t1, vehicle as t2
where t2.id=t1.vehicle and t1.date between '$date1' and '$date2' 
	";
	
}
if($report=='part'){
	print "<table><tr><td>";
	voptions();
	print "</td><td>";
	daterange();
	print "</td></tr></table>";
	if($vehicle) $cond .=" and t1.vehicle='$vehicle' ";	
	if($_SESSION[cid]) $cond .=" and t2.customer='$_SESSION[cid]' ";
	$q="
select t1.vehicle,t1.date,t2.code as 'Vehicle',t1.parts,t1.qty,t1.cost,t1.qty*t1.cost as 'amount'
from sparepart as t1, vehicle as t2
where t2.id=t1.vehicle 
and t1.date between '$date1' and '$date2'
$cond
";
	stdreport($q); 
}
if($report=='part1'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	if($vehicle) $cond .=" and t1.vehicle='$vehicle' ";	
	if($_SESSION[cid]) $cond .=" and t2.customer='$_SESSION[cid]' ";
	$q="
select t1.vehicle,t2.code as 'vehicle',sum(t1.qty*t1.cost) as 'amount',sum(t1.labor) as 'labor'
from sparepart as t1, vehicle as t2
where t2.id=t1.vehicle 
and t1.date between '$date1' and '$date2'
$cond
group by t2.code
";
	$toreport='part3';$prmt="date1=$date1&date2=$date2";
	stdreport($q); 
}
if($report=='part2'){
	$q=" update sparpart set parts=trim(parts) ";
	mysql_query($q);
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	if($vehicle) $cond .=" and t1.vehicle='$vehicle' ";	
	if($_SESSION[cid]) $cond .=" and t2.customer='$_SESSION[cid]' ";
	$q="
select t1.parts,t1.parts as 'Spare Part',sum(t1.qty) as 'qty',sum(t1.qty*t1.cost) as 'amount'
from sparepart as t1, vehicle as t2
where t2.id=t1.vehicle 
and t1.date between '$date1' and '$date2'
and t1.cost>0
$cond
group by t1.parts
";
	$toreport='part4';$prmt="date1=$date1&date2=$date2";
	stdreport($q); 
}
if($report=='part3'){
	if($id) $vehicle=$id;
	print "<table><tr><td>";
	voptions();
	print "</td><td>";
	daterange();
	print "</td></tr></table>";
	if($vehicle) $cond .=" and t1.vehicle='$vehicle' ";	
	$q="
select t1.vehicle,t2.code as 'vehicle',t1.date,t1.parts,t1.qty,t1.cost,t1.qty*t1.cost as 'amount',t1.labor as 'labor'
from sparepart as t1, vehicle as t2
where t2.id=t1.vehicle
and t1.date between '$date1' and '$date2'
$cond

";

//	$addpar="bAutoWidth:false;";
	stdreport($q); 
}
if($report=='part4'){
	if(!$parts) $parts=$id;
	print "<table><tr><td><input type=text name=id value=\"$parts\" size=60></td><td>";
	$prmt="&parts=".urlencode($parts);
	daterange();
	print "</td></tr></table>";
	$q="
select t1.vehicle,t2.code as 'vehicle',t1.date,t1.parts,t1.qty,t1.cost,t1.qty*t1.cost as 'amount',t1.labor as 'labor'
from sparepart as t1, vehicle as t2
where t2.id=t1.vehicle
and t1.date between '$date1' and '$date2'
$cond
and t1.parts='$parts'
";
	print "<h2>$parts</h2>";
//	$addpar="bAutoWidth:false;";
	stdreport($q); 
}
if($report=='part5'){
	monthnav();
	$q="select
t1.date,t1.date,sum(t1.qty*t1.cost) amount
from sparepart as t1, vehicle as t2
where t2.id=t1.vehicle  $cond
and date_format(t1.date,'%Y-%m')='$yr-$mo' 
group by t1.date
order by t1.date
";
	$toreport='part6';$prmt="date1=$date1&date2=$date2";
	print "<h2>Sparepart Daily Consumption</h2>";
	stdreport($q);  //print $q;

}
if($report=='part6'){
	$date=$id;
	$q="
select t1.vehicle,t2.code as 'vehicle',t1.date,t1.parts,t1.qty,t1.cost,t1.qty*t1.cost as 'amount',t1.labor as 'labor'
from sparepart as t1, vehicle as t2
where t2.id=t1.vehicle
and t1.date='$date'
order by t2.code
";
	print "<h2>Sparepart $date </h2>";
	stdreport($q); 
}
if($report=='part7'){
	if($id) $vehicle=$id;
	print "<table><tr><td>";
	voptions();
	print "</td><td>";
	daterange();
	print "</td></tr></table>";
	if($vehicle) $cond .=" and t1.vehicle='$vehicle' ";	
	$q=" select t1.vehicle,t2.code as 'vehicle',t1.date,t3.part_no,t3.name,t1.out_qty 'qty', t1.cost, t1.out_qty*t1.cost as amount  
	from stcard as t1, vehicle as t2,st as t3
where t2.id=t1.vehicle and t3.id=t1.st 
and t1.date between '$date1' and '$date2'
$cond

";
	stdreport($q);  //	print $q;
}
if($report=='workorder'){
	print "<table><tr><td>";
	voptions();
	print "</td><td>";
	daterange();
	print "</td></tr></table>";
	if($vehicle) $cond .=" and t1.vehicle='$vehicle' ";	
	if($_SESSION[cid]) $cond .=" and t2.customer='$_SESSION[cid]' ";
	$q="
select t1.id,t1.date,t2.code as 'Vehicle',t1.request_by, t1.request_date, t1.description,t1.mechanic,t1.note,t1.status
from workorder as t1, vehicle as t2
where t2.id=t1.vehicle
and t1.date between '$date1' and '$date2'
$cond
order by t1.date desc
";
//$addpar=";bSort:True;aaSorting:[[0,'desc']]";
//$addpar=";\"aaSorting\":[]";
	$minwidth='100%';
	$tolink="?action=edit&tb=workorder&id=";
	stdreport($q); 
}
if($report=='consump-abnormal'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
}
if($report=='consumption'){

	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	//and t2.fleet='$_SESSION[fleet]'
	$q=" select t1.vehicle,t2.code as 'vehicle', min(t1.last_milage) as 'start',max(t1.milage) as  'end', max(t1.milage)-min(t1.last_milage) 'km',sum(t1.liter) as 'liter', sum(t1.liter*t1.price) as 'amount', avg(consumption) as 'Consumption (km/liter)', min(consumption) as 'Min'
	,if(avg(consumption)-min(consumption) >0.5,'<font color=red>*</font>','') as 'Alert'
	
from fuel as t1, vehicle as t2
where t2.id=t1.vehicle 
and t1.date between '$date1' and '$date2' 
$cond
group by t1.vehicle
";
	$toreport="fuel2"; $prmt .="&date1=$date1&date2=$date2";
	$tosum=array('km','liter','amount');
	stdreport($q); //print $q;
}
if($report=='fuel2'){ 
	if($id) $vehicle=$id;
	print "<table><tr><td>";
	voptions();
	print "</td><td>";
	daterange();
	print "</td></tr></table>";
	$cond .=" and t1.vehicle='$vehicle' ";  
	$q=" select t1.vehicle,t2.code as 'vehicle',t1.date, t1.last_milage,t1.milage,t1.total_milage as 'km' ,t1.liter as 'liter', t1.liter*t1.price as amount, t1.consumption as 'consumed', t1.note
from fuel as t1, vehicle as t2
where t2.id=t1.vehicle
and t1.date between '$date1' and '$date2' 
$cond 
order by t1.date
";
	$tosum=array('km','liter','amount','consumed');
	$toavg=array('consumed');
	$intflds=array('km');
	stdreport($q); //print $q;
}
if($report=='opr2'){
	print "<table><tr><td>";
	monthnav();
	print "</td><form action=?action=$action&report=$report&mo=$mo&yr=$yr method=post>
<td>".flddict('customer')."</td>
<td><select name=customer onchange=this.form.submit();><option value=''>...".tboptions2('customer',$customer)."</select></td>
<td>".flddict('vehicle')."</td>
<td><select name=vehicle onchange=this.form.submit();><option value=''>...".tboptions2('vehicle',$vehicle)."</select></td>
<td>".flddict('driver')."</td>
<td><select name=driver onchange=this.form.submit();><option value=''>...".tboptions2('driver',$driver)."</select></td>
<td></td><tr></form></table>";
	calreport($mo,$yr);
}
function calreport($mo,$yr){
	global $vehicle,$driver,$customer;
	$wlist=array('อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์', 'เสาร์');
	print "<table class=rpt><tr class=hd>";
	while(list(,$w)=each($wlist)) print "<th>$w</th>";
	print "</tr>";
	$m1dow=date("w",mktime(0,0,0,$mo,1,$yr));
	print "<tr class='bd cal'>";
	for($i=$m1dow;$i>0;$i--){
		$d=date("d",mktime(0,0,0,$mo,-($i-1),$yr));
		print "<td class=prevm>$d</td>";
	}	
	for($d=1;$d<32;$d++){
		if(!checkdate($mo,$d,$yr)) continue;
		$dd=$d;
		if($d<10) $dd="0".$d;
		
		$w=date("w",mktime(0,0,0,$mo,$d,$yr));
		$date="$yr-$mo-$dd";
		$info=calinfo($date);
		print "<td><b>$d</b><div class=calinfo>$info</div></td>";
		if($w==6) print "</tr><tr class='bd cal'>";
	}
	$j=1;
	for($i=$w;$i<6;$i++){
		$d=date("j",mktime(0,0,0,$mo+1,$j,$yr));
		print "<td class=nextm>$d</td>";
		$j++;
	}	

	print "</tr></table>";
}
function calinfo($date){
	global $vehicle,$driver,$customer;
	if($vehicle) $cond .="and t1.vehicle='$vehicle' ";
	if($driver) $cond .=" and t1.driver='$driver' ";
	if($customer) $cond .=" and t1.customer='$customer' ";
	$q=" select t4.code,t3.name,t2.no,date_format(t1.time,'%H:%i') 
	from plan as t1, trip as t2, route as t3, customer as t4
	where t2.id=t1.trip and t3.id=t2.route and t4.id=t3.customer
	and t1.date='$date' $cond order by t2.no
	";
	$ck=mysql_query($q);
	while(list($cust,$r,$t,$time)=mysql_fetch_array($ck)){
		$out .="$cust $r#$t $time<br>";
	}
	//$out=" v $vehicle d $driver ";
	return $out;
}
function calinfo2($date){
	global $vehicle,$driver,$customer;
	if($vehicle) $cond .="and t1.vehicle='$vehicle' ";
	if($driver) $cond .=" and t1.driver='$driver' ";
	if($customer) $cond .=" and t1.customer='$customer' ";
	$q=" select t5.code,t4.code,t3.name,t2.no,date_format(t1.time,'%H:%i') 
	from plan as t1, trip as t2, route as t3, customer as t4, vehicle as t5
	where t2.id=t1.trip and t3.id=t2.route and t4.id=t3.customer and t5.id=t1.vehicle
	$cond 
	and t1.date='$date' order by t2.no 	";
	$ck=mysql_query($q);
	while(list($v, $cust,$r,$t,$time)=mysql_fetch_array($ck)){
		$out .="$v $cust $r#$t $time<br>";
	}

	return $out;
}
if($report=='opr3'){
	if(!$date1)$date1=date("Y-m-d",mktime(0,0,0,date('m'),date('d')-date('w'),date('Y')));
	if(!$date2)$date2=date("Y-m-d",mktime(0,0,0,date('m'),date('d')-date('w')+6,date('Y')));
	print "<table><tr><td>";
	daterange();
	print "</td><form action=?action=$action&report=$report&mo=$mo&yr=$yr method=post>
<td>".flddict('customer')."</td>
<td><select name=customer onchange=this.form.submit();><option value=''>...".tboptions2('customer',$customer)."</select></td>
<td>".flddict('vehicle')."</td>
<td><select name=vehicle onchange=this.form.submit();><option value=''>...".tboptions2('vehicle',$vehicle)."</select></td>
<td>".flddict('driver')."</td>
<td><select name=driver onchange=this.form.submit();><option value=''>...".tboptions2('driver',$driver)."</select></td>
<td></td><tr></form></table>";
	calweek($date1,$date2);
	
}
function calweek($date1,$date2){
	global $vehicle,$driver,$customer;
	$dates=array();
	$yr=substr($date1,0,4);
	$mo=substr($date1,5,2);
	$dd=substr($date1,8,2);
	$d=$dd;
	$d=$d+1-1;
	$date="$yr-$mo-$dd";
	while($date<=$date2){
		array_push($dates,$date);
		$d++;
		$dd=$d;
		if($d<10) $dd="0".$d;
		$date=date("Y-m-d",mktime(0,0,0,$mo,$dd,$yr));
	}
	print "<table class=rpt><tr class=hd>";
	while(list(,$date)=each($dates)){
		$d2=thdate($date);
		print "<th>$d2</th>";
	}
	print "</tr>";
	print "<tr class=bd>";
	reset($dates);
	while(list(,$date)=each($dates)){
		$info=calinfo2($date);
		print "<td><div class=calinfo>$info</div></td>";
	}
	print "</tr>";
	print "</table>";
}
if( ($report=='mgt1')||($report=='mgt2') ){
	$q=" update plan as t1, trip as t2 
	set t1.km=t2.standard_distance
	where t2.id=t1.trip and t1.km=0 ";
	mysql_query($q);
	$q=" update plan as t1, trip as t2 
	set t1.price=t2.price
	where t2.id=t1.trip and t1.price=0 ";
	mysql_query($q);
	$q=" update plan as t1, trip as t2 
	set t1.allowance=t2.allowance
	where t2.id=t1.trip and t1.allowance=0 ";
	mysql_query($q);
}
if($report=='mgt1'){
	print "<table><tr><td>";
	daterange();
	print "</td><td>";
	print "</td></tr></table>";
	$q="
select t2.id,t2.code as 'customer'
,sum(t1.price) as 'income'
,sum(t1.passengers) as 'passengers'
,sum(t1.price)/sum(t1.passengers) as 'cost/man-trip'

,count(t1.id) as 'trips',sum(t1.km) as 'km'
from plan as t1, customer as t2	
where  t2.id=t1.customer 
and t1.date between '$date1' and '$date2' 
group by t1.customer";
/*
	$q="
select id,customer,sum(income),sum(trips),sum(km) from (
select t4.id,t4.code as 'customer',sum(t2.price) as 'income',count(t1.id) as 'trips',sum(t2.standard_distance) as 'km'
from plan as t1, trip as t2, customer as t4	,vehicle as t5, driver as t6
where t2.id=t1.trip and t4.id=t1.customer and t5.id=t1.vehicle and t6.id=t1.driver
and t1.date between '$date1' and '$date2' and t1.status='done'
group by t1.customer
union
select t14.id,t14.code as 'customer',sum(t11.price) as 'income',count(t11.id) as 'trips',sum(t11.km) as 'km'
from job as t11, customer as t14	,vehicle as t15, driver as t16
where t14.id=t11.customer and t15.id=t11.vehicle and t16.id=t11.driver
and t11.date between '$date1' and '$date2' and t11.status='done'
group by t11.customer
) as t0
group by t0.customer
	";
*/
	
	$tosum=array('income','passengers','trips','km');
	stdreport($q); //print $q;
}
if($report=='mgt2'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	
	$q="
select t5.id,t5.code as 'Bus',sum(t1.price) as 'income',count(t1.id) as 'trips',sum(t1.km) as 'km'
from plan as t1, vehicle as t5
where  t5.id=t1.vehicle 
and t1.date between '$date1' and '$date2' 
group by t1.vehicle
order by t5.code
	";
/*	$q=" select id,bus,sum(income),sum(trips),sum(km)
from (select t5.id,t5.code as 'bus',sum(t2.price) as 'income',count(t1.id) as 'trips',sum(t2.standard_distance) as 'km'
from plan as t1, trip as t2, customer as t4	,vehicle as t5, driver as t6
where t2.id=t1.trip and t4.id=t1.customer and t5.id=t1.vehicle and t6.id=t1.driver
and t1.date between '$date1' and '$date2' and t1.status='done'
group by t1.vehicle
union
select t5.id,t5.code as 'bus',sum(t1.price) as 'income',count(t1.id) as 'trips',sum(t1.km) as 'km'
from job as t1, customer as t4	,vehicle as t5, driver as t6
where t4.id=t1.customer and t5.id=t1.vehicle and t6.id=t1.driver
and t1.date between '$date1' and '$date2' and t1.status='done'
group by t1.vehicle
) as t0
group by t0.Bus
	";*/

	$tosum=array('income','trips','km');
	$toreport='mgt5';$prmt="date1=$date1&date2=$date2";
	stdreport($q);
}
if($report=='kpi'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	//total_km ดึงจาก safety.max_speed.daily_distance
	$q="
select *
, fuel/income*100 as 'Fuel Ratio&#37;'
, sparepart/income*100 as 'Sparepart Ratio&#37;'
, income_km/total_km*100 as 'Y Ratio'
 from (
 select t1.vehicle as 'vid' ,t3.code as 'vehicle'
, sum(t1.price) as 'income'
, fuelv1(t1.vehicle,'$date1','$date2') as 'fuel'
, sparev1(t1.vehicle,'$date1','$date2') as 'sparepart'
, sum(t1.km) as 'income_km'
, milagev1(t1.vehicle,'$date1','$date2') as 'total_km'

from plan as t1, vehicle as t3
where  t3.id=t1.vehicle
and t1.date between '$date1' and '$date2' and t3.contract=0
and t1.vehicle<>0 
and t3.fleet='$_SESSION[fleet]' 
group by t1.vehicle
) as t4
order by vehicle
	";
	$q="select *

	, income_km/total_km*100 as 'Y Ratio'
 from (
 select t1.vehicle as 'vid' ,t2.code as 'vehicle'

, fuelv1(t1.vehicle,'$date1','$date2') as 'fuel'
, sparev1(t1.vehicle,'$date1','$date2') as 'sparepart'
, sum(t1.km) as 'income_km'
, milagev1(t1.vehicle,'$date1','$date2') as 'total_km'
, consumpv1(t1.vehicle,'$date1','$date2') as 'X Ratio<br>Km/L'
from plan as t1, vehicle as t2
where  t2.id=t1.vehicle
and t1.date between '$date1' and '$date2' and t2.contract=0
and t2.fleet='$_SESSION[fleet]' 
$cond
group by t1.vehicle
) as t4
order by vehicle
	";
	
	$tosum=array('income','trips','income_km','total_km','fuel','sparepart');
	stdreport($q); //print $q;
}
if($report=='allowance'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q=" 
select t1.driver as 'vid' ,t3.name as 'driver'
, sum(t1.allowance) as 'allowance', count(t1.id) as 'trips'

from plan as t1, employee as t3
where  t3.id=t1.driver  
and t1.date between '$date1' and '$date2' and t3.type='driver'
group by t1.driver

	";
	$tosum=array('allowance','trips');
	$toreport='mgt6';$prmt="date1=$date1&date2=$date2";
	stdreport($q);
}

if($report=='mgt5'){
	$vehicle=$id;
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="
select t4.id,t4.code as 'Customer',sum(t1.price) as 'income',count(t1.id) as 'trips',sum(t1.km) as 'km'
from plan as t1, customer as t4	,vehicle as t5
where t4.id=t1.customer and t5.id=t1.vehicle 
and t1.date between '$date1' and '$date2' 
and t1.vehicle='$vehicle' 
group by t1.customer

	";
	$tosum=array('income','trips','km');
	$toreport='mgt7';$prmt="vehicle=$vehicle&date1=$date1&date2=$date2";
	stdreport($q);

}
if($report=='mgt6'){
	$driver=$id;
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q=" 
select t1.customer as 'cid' ,t4.code as 'Customer'
, sum(t1.allowance) as 'allowance', count(t1.id) as 'trips'

from plan as t1, employee as t3, customer as t4
where t3.id=t1.driver and t4.id=t1.customer
and t1.date between '$date1' and '$date2' 
and t1.driver='$driver' 
group by t1.customer

	";

	$tosum=array('allowance','trips');
	$toreport='mgt8';$prmt="driver=$driver&date1=$date1&date2=$date2";
	stdreport($q); //print $q;
}
if($report=='mgt7'){
	$customer=$id;
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="
select t4.id,t1.date,t1.in_out,t1.time,t1.price as 'income',t1.km
from plan as t1 , customer as t4 
where  t4.id=t1.customer 
and t1.date between '$date1' and '$date2' 
and t1.vehicle='$vehicle' and t1.customer ='$customer'

	";
	$tosum=array('income','trips','km');

	$toreport='mgt7';$prmt="vehicle&$vehicle&date1=$date1&date2=$date2";
	stdreport($q); //print $q;

}
if($report=='mgt8'){
	$customer=$id;
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="
select t4.id,t1.date,t1.in_out ,t1.time,t1.allowance as 'allowance'

from plan as t1, customer as t4	,vehicle as t5, employee as t6
where  t4.id=t1.customer and t5.id=t1.vehicle and t6.id=t1.driver 
and t1.date between '$date1' and '$date2' 
and t1.driver='$driver' and t1.customer ='$customer'

	";
	$tosum=array('allowance','trips','km');
	$toreport='mgt7';$prmt="vehicle&$vehicle&date1=$date1&date2=$date2";
	stdreport($q); //print $q;

}
if($report=='kpi2'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="
select *
, income-cost as 'Margin'
, (income-cost)/income*100 as 'Ratio&#37;'
 from (
 select t1.vehicle as 'vid' ,t3.code as 'vehicle'
, sum(t1.price) as 'income'
, sum(t1.cost) as 'cost'

from plan as t1, vehicle as t3
where t3.id=t1.vehicle
and t1.date between '$date1' and '$date2' and t3.contract=1
group by t1.vehicle
) as t4
	";
	$tosum=array('income','cost','Margin');
	stdreport($q);
}

if($report=='mgt10'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="
select vid,vehicle,income,fuel,sparepart,labor,allowance,prb,insurance,register
, income-fuel-sparepart-labor-allowance-prb-insurance-register as 'gross'
, installment
, income-fuel-sparepart-labor-allowance-prb-insurance-register-installment as 'net'

 from (
 select t1.vehicle as 'vid' ,t3.code as 'vehicle'
, incomev1(t1.vehicle,'$date1','$date2') as 'income'
, fuelv1(t1.vehicle,'$date1','$date2') as 'fuel'
, sparev1(t1.vehicle,'$date1','$date2') as 'sparepart'
, laborv1(t1.vehicle,'$date1','$date2') as 'labor'
, allowancev1(t1.vehicle,'$date1','$date2') as 'allowance'
, t3.prb/12 as 'prb'
, t3.class1_3/12 as 'insurance'
, t3.register/12 as 'register'
, t3.installment as 'installment'

from plan as t1,trip as t2, vehicle as t3 
where t2.id=t1.trip and t3.id=t1.vehicle
and t1.date between '$date1' and '$date2'  and t3.contract=0
group by t1.vehicle
) as t4
	";
	$tosum=array('income','fuel','sparepart','labor','allowance','prb','insurance','register','installment','gross','net');
	stdreport($q); //print $q;
}
if($report=='mgt11'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="
select vid,vehicle,income,fuel,sparepart,labor,allowance,prb,insurance,register
,income-fuel-sparepart-labor-allowance-prb-insurance-register 'gross'
,installment
,income-fuel-sparepart-labor-allowance-prb-insurance-register-installment 'net'

from (
 select t1.id as 'vid' ,t1.code as 'vehicle'
, incomev1(t1.id,'$date1','$date2') as 'income'
, fuelv1(t1.id,'$date1','$date2') as 'fuel'
, sparev1(t1.id,'$date1','$date2') as 'sparepart'
, laborv1(t1.id,'$date1','$date2') as 'labor'
, allowancev1(t1.id,'$date1','$date2') as 'allowance'
, t1.prb/12 as 'prb'
, t1.class1_3/12 as 'insurance'
, t1.register/12 as 'register'
, t1.installment as 'installment'

from vehicle as t1 
where t1.code<>'' and t1.contract=0
group by t1.id
) as t4
	";
	$tosum=array('income','fuel','sparepart','labor','allowance','prb','insurance','register','installment','gross','net');

	stdreport($q);  print $q;
}
if($report=='mgt12'){
	print "<table><tr><td>";
	daterange();
	print "</td></tr></table>";
	$q="
select vid,vehicle,income
,fuel/income*100 'fuel'
,sparepart/income*100 'sparepart'
,labor/income*100 'labor'
,allowance/income*100 'allowance'
,prb/income*100 'prb'
,insurance/income*100 'insurance'
,register/income*100 'register'
,(income-fuel-sparepart-labor-allowance-prb-insurance-register)/income*100 'gross &#37;'
,installment/income*100 'installment' 
,(income-fuel-sparepart-labor-allowance-prb-insurance-register-installment)/income*100 'net &#37;'

from (
 select t1.id as 'vid' ,t1.code as 'vehicle'
, incomev1(t1.id,'$date1','$date2') as 'income'
, fuelv1(t1.id,'$date1','$date2') as 'fuel'
, sparev1(t1.id,'$date1','$date2') as 'sparepart'
, laborv1(t1.id,'$date1','$date2') as 'labor'
, allowancev1(t1.id,'$date1','$date2') as 'allowance'
, t1.prb/12 as 'prb'
, t1.class1_3/12 as 'insurance'
, t1.register/12 as 'register'
, t1.installment as 'installment'

from vehicle as t1 
where t1.code<>'' and t1.contract=0
group by t1.id
) as t4
	";
	$tosum=array('income');

	stdreport($q); 
}
function datelist($date1,$date2){
	$o=array();
	$start=strtotime($date1);
	$dt=$date1;
	$i=0;
	while($dt<$date2){
		$dt=date('Y-m-d', strtotime("+$i day", $start));
		array_push($o,$dt);
		$i++;
	}
	return $o;
}
if($report=='month-mtn'){
	
	print "<table><form action=? method=get><input type=hidden name=action value=$action>
	<input type=hidden name=report value=$report><tr><td>Customer</td><td><select name=customer ><option value=\"\">...".tboptions2('customer',$customer)."</select></td><td>
	<input type=text class=date name=date1 value=$date1>
	<input type=text class=date name=date2 value=$date2>
	</td><td><input type=submit value=Report>
	</td></tr></table>";
	$q="select t1.id,t2.code 'vehicle',t1.date,t1.milage,t1.mechanic,t1.note
	from workorder as t1,vehicle as t2
	where t2.id=t1.vehicle
	and t1.date between '$date1' and '$date2'
	and t2.customer='$customer'
	$cond 
	";
	stdreport($q);
	print "<style>.mechanic{white-space:nowrap;}</style>";
}
if($report=='monthly'){
	
	print "<table><form action=? method=get><input type=hidden name=action value=$action>
	<input type=hidden name=report value=$report><tr><td>Customer</td><td><select name=customer ><option value=\"\">...".tboptions2('customer',$customer)."</select></td><td>
	<input type=text class=date name=date1 value=$date1>
	<input type=text class=date name=date2 value=$date2>
	</td><td><input type=submit value=Report>
	</td></tr></table>";
	$dlist=datelist($date1,$date2);
	$q=" select  t1.trip,t2.in_out,time_format(t2.start_time,'%H:%i') as 'start',t2.name ";
	
	while(list(,$dt)=each($dlist)){
		$d=substr($dt,8,2);
		$q.=", sum(if(t1.date='$dt',t1.passengers,0)) as '$d' ";
		array_push($tosum,$d);
	}
	$q.=",sum(t1.passengers) 'total' from plan as t1, trip as t2
where t2.id=t1.trip and t2.customer='$customer' and t1.date between '$date1' and '$date2'
group by t1.trip
order by t2.name,t2.in_out
";
	$sumdigit=0;
	array_push($tosum,'total');
	$intflds=$tosum;
	stdreport($q);
}
if($report=='daily'){
	if(!$date) $date=date("Y-m-d");;
	print "<table><form action=? method=get><input type=hidden name=action value=$action><input type=hidden name=report value=$report><tr><td>Customer</td><td><select name=customer ><option value=\"\">...".tboptions2('customer',$customer)."</select></td><td>Date</td><td><input type=text name=date id=date value=$date ></td><td><input type=submit value=Report></tr></form></table>";
print "<script>$('#date').datepicker({dateFormat:'yy-mm-dd'});</script>
";
	$q=" select t1.id
, t1.time as 'Plan Start'
, t1.in_out as 'In/Out'
, t2.name as 'Trip'
, t4.code as 'Vehicle' 
, t5.name as 'Driver'
, if(t1.actual_start>t1.time,concat('<font color=red>',t1.actual_start,'</font>'),t1.actual_start) 'start'
, t1.actual_finish 'finish'
, t1.passengers 'passengers'

from plan as t1, trip as t2, customer as t3, vehicle as t4, employee as t5
where t2.id=t1.trip and t3.id=t1.customer and t4.id=t1.vehicle and t5.id=t1.driver
and t1.customer='$customer' 
and t1.date='$date'
order by t4.code,t2.name ";
	global $minwidth;
	$minwidth=100;
	$tosum=array('passengers');
	$tolink="?action=edit&tb=plan&id=";
	stdreport($q);
}
if($report=='tire'){
	$q=" select t2.id,t2.code as 'Vehicle'
, tirev1(t2.id,'FL') as 'FL'
, tirev1(t2.id,'FR') as 'FR'
, tirev1(t2.id,'RLI') as 'RLI'
, tirev1(t2.id,'RLO') as 'RLO'
, tirev1(t2.id,'RRI') as 'RRI'
, tirev1(t2.id,'RRO') as 'RRO'

from vehicle as t2 
where t2.contract=0 and t2.code<>''
$cond
";
	$tored=$config[tire_life];
	$tolink="?action=history&deftab=tire&vehicle=";
	stdreport($q); //print $q; 
}
if($report=='battery'){
	$q=" select t2.id,t2.code as 'Vehicle'
, batteryv1(t2.id,'engine1') as 'engine1'
, batteryv1(t2.id,'engine2') as 'engine2'
, batteryv1(t2.id,'air1') as 'air1'
, batteryv1(t2.id,'air2') as 'air2'
, batteryv1(t2.id,'radio') as 'radio'

from vehicle as t2
where t2.contract=0 and t2.code<>''
$cond
";
	$tored=$config[battery_life];
	$tolink="?action=history&deftab=tire&vehicle=";
	stdreport($q); //print $q; 
}
if($report=='list'){
	$q="select t2.id,t2.name,concat('<img width=50 src=images/employee/',t2.id,'.jpg >') as 'Picture' from employee as t2 where 1 $cond ";
	$tolink="?action=edit&tb=employee&id=";
	stdreport($q); //print $q; 
	
}
if($report=='history'){
	monthnav();
	$q="select t1.id,t2.name,t1.date,t1.history,t1.note from $report as t1,employee as t2 where t2.id=t1.employee and date_format(t1.date,'%Y-%m')='$yr-$mo' $cond ";
	$tolink="?action=edit&tb=$report&id=";
	stdreport($q); //print $q; 
	
}
if($report=='training'){
	print "<form action=?action=$action&report=$report&etype=$etype method=post><select name=course onchange=this.form.submit();><option>".tboptions2('course',$course)."</select><input type=submit value=report></form>";
	$q="select t1.id,t2.name,t1.date,t3.name 'course',t1.note from $report as t1,employee as t2,course as t3 where t2.id=t1.employee and t3.id=t1.course and t1.course='$course' and t1.date>'0000-00-00' $cond ";
	$tolink="?action=edit&tb=$report&id=";
	print "<h2>พนักงานที่เข้าอบรมแล้ว</h2>";
	stdreport($q); //print $q; 
	$q="select t2.id,t2.name,t2.employed,t2.birth from employee as t2 where t2.resign=0 and  t2.id not in (select employee from training where course='$course') $cond  ";
	$q="select t1.id,t2.name,t2.employed,t2.birth,t3.name 'course',t1.note from $report as t1,employee as t2,course as t3 where t2.id=t1.employee and t3.id=t1.course and t1.course='$course' and t1.date='0000-00-00' $cond ";
	$tolink="?action=edit&tb=$report&id=";

	print "<h2>พนักงานที่ยังไม่ได้เข้าอบรม</h2>";
	$tbid='tb2';
	stdreport($q); //print $q; 
	
}
if($report=='resign'){
	yearnav();
	print "<h2>พนักงานลาออก</h2>";
	$q="select t1.id,t1.name 'ชื่อพนักงานที่ลาออก',t1.type,t1.employed,t1.resign_date,year(t1.resign_date)-year(t1.employed) as 'อายุงาน',t1.resign_interview 'สาเหตุ' from employee as t1 where t1.resign=1 and  date_format(t1.resign_date,'%Y')='$yr' ";
	stdreport($q); //print $q; 
	
}
if($report=='bill'){
	print "<table><form action=?action=$action&report=$report method=post><tr><td>Customer</td><td><select name=customer ><option value=\"\">...".tboptions2('customer',$customer)."</select></td><td>Date</td><td>";
	daterange2();
	print "</td><td><input type=submit value=Report></tr></form></table>";
	$q="select t1.trip,t2.name 'trip',count(t1.id) as 'qty',sum(t1.price) 'amount' from plan as t1,trip as t2 
	where t2.id=t1.trip 
	and t1.customer='$customer' and t1.date between '$date1' and '$date2' group by t2.name  ";
	stdreport($q);

}
if($report=='billed'){
	print "<table><form action=?action=$action&report=$report method=post><tr><td>Contractor</td><td><select name=contractor ><option value=\"\">...".tboptions2('contractor',$contractor)."</select></td><td>Date</td><td>";
	daterange2();
	print "</td><td><input type=submit value=Report></tr></form></table>";
	if($contractor) $cond .=" and t3.contractor='$contractor' ";
	$q="select t1.trip,t4.code 'contractor',t2.name 'trip',count(t1.id) as 'qty',sum(t1.price) 'amount' from plan as t1,trip as t2 ,vehicle as t3, contractor as t4
	where t2.id=t1.trip and t3.id=t1.vehicle and t4.id=t3.contractor and t3.fleet='$_SESSION[fleet]' 
	 and t1.date between '$date1' and '$date2' group by t2.name order by t4.code,t3.code ";
	stdreport($q);

}
if($report=='speedy'){
	datenav();
	$q=" select t2.id,t2.code as 'Bus&Van',convert(t1.max_speed,unsigned int) as 'Speed(km/hr)',t3.name as 'Driver' from max_speed as t1,vehicle as t2,employee as t3 where t2.id=t1.vehicle and t3.id=t2.driver and t2.fleet='$_SESSION[fleet]' and t1.date='$date' and t1.max_speed>t2.control_speed order by convert(t1.max_speed,unsigned int) desc ";
	stdreport($q);
}

if($report=='speedy2'){
//	print "<table><form action=?action=$action&report=$report method=post><tr><td>Date</td><td>";
	print "<h2>$rptlist[$report]</h2>";
	daterange2();
	//print "</td><td><input type=submit value=Report></tr></form></table>";
	$q=" select t2.id,t2.code 'Bus&Van',count(t1.id) as 'Frequency' from max_speed as t1,vehicle as t2 where t2.id=t1.vehicle and t2.fleet='$_SESSION[fleet]' and t1.date between '$date1' and '$date2' and t1.max_speed>t2.control_speed group by t1.vehicle  order by count(t1.id) desc ";
	$tolink="?action=report&report=speedy3&date1=$date1&date2=$date2&vehicle=";
	stdreport($q);
}
if($report=='speedy3'){
	$q=" select t2.id,t1.date,t2.code as 'Bus&Van',convert(t1.max_speed,unsigned int) as 'Speed(km/hr)',t3.name as 'Driver' from max_speed as t1,vehicle as t2,employee as t3 where t2.id=t1.vehicle and t3.id=t2.driver and t2.fleet='$_SESSION[fleet]' and t1.date between '$date1' and '$date2' and t1.max_speed>t2.control_speed and t1.vehicle='$vehicle' order by t1.date ";
	stdreport($q); //print $q;
}
if($report=='speedy4'){
//	print "<table><form action=?action=$action&report=$report method=post><tr><td>Date</td><td>";
	print "<h2>$rptlist[$report]</h2>";
	daterange2();
	$dd=substr($date1,8,2);
	$dd=$dd+1-1;
	$mm=substr($date1,5,2);
	$yy=substr($date1,0,4);
	$date=date("Y-m-d",mktime(0,0,0,$mm,$dd,$yy));
	$q="select t1.id,t2.code as 'Bus&Van' ";
	while($date<=$date2){
		$d=substr($date,8,2);
		$d=$d+1-1;
		$q .=", sum(if(t1.date='$date',convert(t1.daily_distance,unsigned int),'')) as '$d'  ";
		$dd++;
		$date=date("Y-m-d",mktime(0,0,0,$mm,$dd,$yy));
	}
	$q .=" from max_speed as t1,vehicle as t2 where t2.id=t1.vehicle and t2.fleet='$_SESSION[fleet]' 
	group by t2.code 
	order by t2.code ";
	stdreport($q); //print $q;
	
}
if($report=='speedy5'){
//	print "<table><form action=?action=$action&report=$report method=post><tr><td>Date</td><td>";
	print "<h2>$rptlist[$report]</h2>";
	daterange2();
	$dd=substr($date1,8,2);
	$dd=$dd+1-1;
	$mm=substr($date1,5,2);
	$yy=substr($date1,0,4);
	$date=date("Y-m-d",mktime(0,0,0,$mm,$dd,$yy));
	$q="select t1.id,t2.code as 'Bus&Van' ";
	while($date<=$date2){
		$d=substr($date,8,2);
		$d=$d+1-1;
		$q .=", max(if(t1.date='$date',convert(t1.max_speed,unsigned int),'')) as '$d'  ";
		$dd++;
		$date=date("Y-m-d",mktime(0,0,0,$mm,$dd,$yy));
	}
	$q .=" from max_speed as t1,vehicle as t2 where t2.id=t1.vehicle and t2.fleet='$_SESSION[fleet]' 
	and t1.date between '$date1' and '$date2' and t1.max_speed>t2.control_speed 
	group by t1.vehicle
	order by t2.code ";
	stdreport($q); //print $q;
	
}
$db1='atp30_web';
$db2='atp30_zk';
if($report=='ta1'){
	datenav();
	$yr=substr($date,0,4);
	$mo=substr($date,5,2);
	$q=" update atp30_zk.checkinout set checktype='I' where date_format(checktime,'%H:%i')< '12:00' " ;
	mysql_query($q);
	$q=" update atp30_zk.checkinout set checktype='O' where date_format(checktime,'%H:%i')>= '12:00' " ;
	mysql_query($q);
	
	$q="select t2.badgenumber,t2.name,t2.lastname 'นามสกุล' 
	,min(date_format(t1.checktime,'%H:%i')) as 'เข้า'
	,if( max(t1.checktime)=min(t1.checktime) ,'',max(date_format(t1.checktime,'%H:%i'))) as 'ออก',t4.areaname 'สนง'
from atp30_zk.checkinout as t1, atp30_zk.userinfo as t2 , atp30_zk.iclock as t3, atp30_zk.personnel_area as t4
where t2.userid=t1.userid and t3.sn=t1.sn_name and t4.areaid=t3.area_id
and date_format(t1.checktime,'%Y-%m-%d')='$date'
group by t1.userid
	";
	$tolink="?action=report&report=ta2&yr=$yr&mo=$mo&badge=";
	stdreport($q); //print $q;
	
}
if($report=='ta2'){
	$cmprmt="&badge=$badge";
	monthnav();
	$fullname=qval("select concat(name,' ',lastname) from atp30_zk.userinfo where badgenumber='$badge' ");
	print "<table class=rpt><tr><td>$fullname</td></tr></table> ";
	$q="select t2.badgenumber,date_format(t1.checktime,'%d/%m/%Y') 'Date',date_format(t1.checktime,'%a') 'D' ,min(t1.checktime) as 'เข้า',max(t1.checktime) as 'ออก'
from atp30_zk.checkinout as t1, atp30_zk.userinfo as t2 , atp30_zk.iclock as t3
where t2.userid=t1.userid and t3.sn=t1.sn_name and date_format(t1.checktime,'%Y-%m')='$yr-$mo'
and t2.badgenumber='$badge' 
group by date_format(t1.checktime,'%Y-%m-%d') 
	";
	stdreport($q); //print $q;
	
}
function checkstatus($date){
	$q="select t2.userid,right(t2.badgenumber,3) 'รหัส',t2.name,t2.lastname 'นามสกุล' 
,intime1(t2.userid,'$date') 'เข้า'
,outtime1(t2.userid,'$date') 'ออก'
,t2.in_time,t2.out_time
from $db2.userinfo as t2 ,$db1.employee as t3 where t3.badgenumber=t2.badgenumber
and t3.resign=0 and t3.lvel='officer'
";
	$ck=mysql_query($q);
	while(list($uid,$uno,$n,$l,$it,$ot,$it0,$ot0)=mysql_fetch_array($ck)){
		if(($it>$it0)||($it=='')) $st='สาย';
		if(($ot<$ot0)||($ot=='')) $st='ออกก่อน';
		if(($it=='')||($ot=='')) $st='ขาด';
		
	}
}

if($report=='ta3'){
	mysql_select_db("atp30_zk");
	if(!$date) $date=date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	datenav();
	
	$yr=substr($date,0,4);
	$mo=substr($date,5,2);
	$dd=substr($date,8,2);
	$wlist=array('อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์', 'เสาร์');
	$w=qval("select date_format('$date','%w' ) ");
	print "$wlist[$w]";
	$q=" update atp30_zk.checkinout set checktype='I' where date_format(checktime,'%H:%i')< '12:00' " ;
	mysql_query($q);
	$q=" update atp30_zk.checkinout set checktype='O' where date_format(checktime,'%H:%i')>= '12:00' " ;
	mysql_query($q);
	$q="select t2.badgenumber,right(t2.badgenumber,3) 'รหัส',t2.name,t2.lastname 'นามสกุล' 
,intime1(t2.userid,'$date') 'เข้า'
,outtime1(t2.userid,'$date') 'ออก'
,if(t3.level='officer', if(intime1(t2.userid,'$date')>t2.in_time,'สาย',''),'') 'สาย'
,if(t3.level='officer',if(outtime1(t2.userid,'$date')<t2.out_time,'ออกก่อน',''),'') 'ออกก่อน'
,if(isnull(intime1(t2.userid,'$date'))&&isnull(outtime1(t2.userid,'$date'))  ,'ขาด','') 'ขาด'

from $db2.userinfo as t2 ,$db1.employee as t3 where t3.badgenumber=t2.badgenumber
and t3.resign=0 
order by t2.badgenumber

	";
	if(($w==6)||($w==0))	$q="select t2.userid,right(t2.badgenumber,3) 'รหัส',t2.name,t2.lastname 'นามสกุล' 
,intime1(t2.userid,'$date') 'เข้า'
,outtime1(t2.userid,'$date') 'ออก'
,'' as 'สาย'
,'' as 'ออกก่อน'
,'' as 'ขาด'

from $db2.userinfo as t2 ,$db1.employee as t3 where t3.badgenumber=t2.badgenumber
and t3.resign=0 
order by t2.badgenumber

	";

	//,$db1.employee as t3 where t3.badgenumber=t2.badgenumber
//	$tolink="?action=settime&id=";
	$tolink="?action=report&report=ta2&yr=$yr&mo=$mo&badge=";
	stdreport($q); print $q;
/*
	,intime(t2.userid,'$date') as 'เข้า'
	,outtime(t2.userid,'$date') as 'ออก'
	,if(intime(t2.userid,'$date')>t2.in_time,'สาย','')

*/	
	mysql_select_db("atp30_web");

}
if($action=='redump'){
	dump($date,'');
	$report='ta4';$action='report';

}
if($report=='ta4'){
	if(!$date) $date=date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	datenav();
	print "<a href=?action=redump&date=$date>ดึงข้อมูลใหม่</a>";
	$q="select t1.id,right(t2.badgenumber,3) 'รหัส',t2.name
	,t2.department 'หน่วยงาน' 
	,date_format(t1.in_time,'%H:%i') 'เข้า'
	, date_format(t1.out_time,'%H:%i') 'ออก' 
	,if(t1.remark='L','สาย','') 'สาย'
	,if(t1.remark='E','ออกก่อน','') 'ออกก่อน'
	,if(t1.remark='A','ขาด','') 'ขาด'
	,if(t1.remark='LV','ลา','') 'ลา'
	,if(t1.remark='O','ปกติ','') 'ปกติ',t1.comment";
	
	if($_SESSION[ulevel]!='officer') $q .=", if(t1.remark<>'',concat('<a href=?action=edit&tb=ta&id=',t1.id,'>อนุมัติ</a>'),'') 'บันทึก' ";
	
$q.="from ta as t1, employee as t2
where t2.id=t1.employee	and t2.badgenumber > 0 
and t1.date='$date'
	";
	if($date==date("Y-m-d")){
		$q=" 
select t3.id,right(t3.badgenumber,3) 'รหัส',t3.name
,if(min(date_format(t1.checktime,'%H:%i'))<'12:00',min(date_format(t1.checktime,'%H:%i')),'') as 'เข้า'
,if(max(date_format(t1.checktime,'%H:%i'))>'12:00',max(date_format(t1.checktime,'%H:%i')),'') as 'ออก'
,'' as 'สาย'
,'' as 'ออกก่อน'
,'' as 'ขาด'
,'' as 'ปกติ'
,'' as 'comment'
from $db2.checkinout as t1, $db2.userinfo as t2, $db1.employee as t3
where t2.userid=t1.userid and t3.badgenumber+0=t2.badgenumber+0  and t3.badgenumber> 0 
and date_format(t1.checktime,'%Y-%m-%d')='$date' group by t1.userid
		";	
//		$q="select t3.id,t3.name, min(t1.checktime)		from $db2.checkinout as t1, $db2.userinfo as t2 left outer join  $db1.employee as t3 on t3.badgenumber=t2.badgenumber where t2.userid=t1.userid and t3.resign=0 and t3.badgenumber>0  and date_format(t1.checktime,'%Y-%m-%d')='$date' group by t1.userid		";

	$q=" update atp30_zk.checkinout set checktype='I' where date_format(checktime,'%H:%i')< '12:00' " ;
	mysql_query($q);
	$q=" update atp30_zk.checkinout set checktype='O' where date_format(checktime,'%H:%i')>= '12:00' " ;
	mysql_query($q);
	$q="select t2.badgenumber,right(t2.badgenumber,3) 'รหัส',t2.name,t2.lastname 'นามสกุล' 
,$db2.intime1(t2.userid,'$date') 'เข้า'
,$db2.outtime1(t2.userid,'$date') 'ออก'
,'' as 'สาย'
,'' as 'ออกก่อน'
,'' as 'ขาด'
,'' as 'ปกติ'
,'' as 'comment'

from $db2.userinfo as t2 ,$db1.employee as t3 where t3.badgenumber=t2.badgenumber
and t3.resign=0 
order by t2.badgenumber

	";
		//print $q;
	}
	stdreport($q); //print $q;
}

if($report=='ta5'){
	daterange();
	$q=" select  t1.employee,t1.date,right(t2.badgenumber,3) 'รหัส',t2.name
	,sum(if(t1.remark='L',1,0)) as 'L' 
	,sum(if(t1.remark='E',1,0)) as 'E'
	,sum(if(t1.remark='A',1,0)) as 'A'
	,sum(if(t1.remark='LV',1,0)) as 'LV'
	,sum(if(t1.remark='O',1,0)) as 'O'
	
	from ta as t1,employee as t2
	where t2.id=t1.employee and t2.badgenumber>0 
	and t1.date between '$date1' and '$date2'
	and t1.remark in ('L','A','E','LV','O') 
	group by t1.employee
	";
	$tosum=array('L','A','E','LV','O');
	$intflds=array('L','A','E','LV','O');
	$tolink="?action=report&report=ta6&date1=$date1&date2=$date2&employee=";
	stdreport($q); //print $q;
}
if($report=='ta6'){
	daterange();
	$q=" select  t1.id,t1.date,t2.badgenumber,t2.name
	,if(t1.remark='L','สาย','') as 'สาย' 
	,if(t1.remark='E','ออกก่อน','') as 'ออกก่อน'
	,if(t1.remark='A','ขาด','') as 'ขาด'
	,if(t1.remark='LV','ลา','') as 'ลา'
	,if(t1.remark='O','ปกติ','') as 'ปกติ'
	,t1.comment
	from ta as t1,employee as t2
	where t2.id=t1.employee
	and t1.date between '$date1' and '$date2'
	and t1.remark in ('L','A','E','LV')
	and t1.employee='$employee' group by t1.date
	";
	stdreport($q); //print $q;
}
if($report=='ta7'){
	if(!$date1) $date1=date("Y-m-d",mktime(date('Y'),date('m')-1,1));
	daterange();
	$q=" select t1.userid,t3.tiger,t1.checktime 
	from $db2.checkinout as t1,$db2.userinfo as t2,$db1.employee as t3 where t2.userid=t1.userid and t3.badgenumber=t2.badgenumber and t1.checktime between '$date1 00:00:00' and '$date2 23:59:59' order by t1.checktime ";
	
	stdreport($q); 
}

if($report=='stock'){
	$q=" select id,part_no,part_name,balance,unit,cost,amount,supplier,min_stock,location from stock ";
	$tolink="?action=edit&tb=stock&id=";
	stdreport($q);
}
if($report=='bd'){
	daterange();
	$q=" Select t0.id,t1.date,t2.code 'Vehicle',t0.actions 'Prevention Actions',t0.status 
	from prevention as t0, breakdown as t1, vehicle as t2
where t1.id=t0.breakdown and t2.id=t1.vehicle
and t1.date between '$date' and '$date2' 	
$cond
	";
	stdreport($q); //print $q;
}
if($report=='accident'){
	daterange();
	$q=" Select t1.id,t5.date,t2.code 'Vehicle',t4.name 'Driver',t1.actions 'Prevention Actions',t1.status 
	from ac_prevention as t1, accident as t5, vehicle as t2, employee as t4
where t5.id=t1.accident and t2.id=t5.vehicle and t4.id=t5.driver
and t5.date between '$date' and '$date2' 	 $cond 
	";
	stdreport($q); //print $q;
}
if($report=='suggest'){
	daterange();
	if($_SESSION[utype]=='customer') $cond .=" and t1.customer='$_SESSION[cid]' ";
	$q=" select t1.id,t1.date,concat(t2.name) customer,t1.suggestion,t0.corrective_action,t0.status
	from suggest_action as t0, suggest as t1, customer as t2
	where t1.id=t0.suggest and t2.id=t1.customer  and t1.date between '$date1' and '$date2'
	$cond
	";
	stdreport($q); //print $q;
}
if($report=='mtn'){
	$q=" update mtn as t1,workorder as t2 set t1.request_date=t2.request_date where t2.id=t1.workorder and t1.request_date='0000-00-00' ";
	qexe($q);
	daterange();
	
	print "<select name=location id=location onchange=window.location.href='?action=report&date1=$date1&date2=$date2&report=$report&location='+$(this).val(); ><option>";
	while(list(,$l)=each($llist)){
		if($location==$l) print "<option selected>$l";
		else print "<option>$l";
	}
	print "</select>";
	//<script>$(function(){$('#location').val('$location');});</script>";
	if($location) $cond .=" and t1.plan_location='$location' ";
	$q="select t1.id,t1.workorder,t1.plan_location,t2.code 'vehicle',t1.done_milage ,t1.request_date ,t1.plan_date,t1.plan_detail, t1.status from 
	mtn as t1,vehicle as t2 where t2.id=t1.vehicle and t1.plan_date between '$date1' and '$date2' 
	$cond 
	order by t1.plan_location,t1.plan_date ";
	$tolink="?action=edit&tb=mtn&id=";

	stdreport($q);
}
if($report=='plan4'){
	print "<form action=?action=$action&report=$report method=post>Customer<select name=customer><option>".qoptions("select id,code from customer",$customer)."</select></form>";
daterange();
}
if($report=='plan5'){
	daterange();
	$q="select t1.id,t2.code as 'vehicle' ,t3.name  as 'driver',t4.code 'customer'
	,t5.name 'route',count(t1.id) 'Trips'
	from plan as t1,vehicle as t2,employee as t3 ,customer as t4 ,route as t5
	where t2.id=t1.vehicle and t3.id=t1.driver and t4.id=t1.customer and t5.id=t1.route
	and t1.date between '$date1' and '$date2' 
	$cond
	group by t1.vehicle,t1.driver,t1.customer,t1.route
	order by t1.customer,t1.route
	";
	print "<h3>สรุปข้อมูลเที่ยวรับส่ง</h3>";
	stdreport($q);
}
if($report=='userlog'){
	daterange();
	$q="select  t1.login,t1.login,t1.datetime,t2.department,t3.name 'fleet' from userlog as t1, user as t2,fleet as t3 where t2.login=t1.login and t3.id=t2.fleet and t1.datetime between '$date1 00:00:00' and '$date2 23:59:59' order by t1.datetime desc ";
	$tolink='?action=report&report=userlog1&login=';
	stdreport($q); print $q;
}
if($report=='userlog1'){
	$prmt="&login=$login";
	daterange();
	$q="select  t1.login,t1.login,t1.datetime,t2.department,t3.name 'fleet' from userlog as t1, user as t2,fleet as t3 where t2.login=t1.login and t3.id=t2.fleet and t1.datetime between '$date1 00:00:00' and '$date2 23:59:59' and t1.login='$login' order by t1.datetime desc ";
	stdreport($q); print $q;
}
//print "action $action report $report ";
if($report=='nc'){
	//$type=substr($report,3,strlen($report));
	yearnav();
	$q="select t1.id,concat(' ',t1.id) 'nc_number', t1.type,t1.date,t1.report_detail,t1.issue_by,t1.issue_dept, t1.response_by, t1.response_dept
	,preventive_date 'กำหนดแล้วเสร็จ'
		,if(follow1_closed=1,'ปิด','') 'สถานะการติดตามครั้ง1'
		,if(follow2_closed=1,'ปิด','') 'สถานะการติดตามครั้ง2'
	from nc as t1 where year(date)='$yr' ";
	stdreport($q); //print $q;
}
?>
