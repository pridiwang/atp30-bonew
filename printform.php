<?php 

if($action=='printform'){
	//error_reporting(E_ALL);
	$dr=qdr(" select * from $tb where id='$id' ");
	//print_r($dr);
	while(list($fld,$val)=each($dr)){
		if($val=='0000-00-00') $dr[$fld]='____-__-__';
	}
	if($tb=='nc') $dr[dcc_valid_date]=qval("select date_format(dcc_valid_date,'%d.%m.%y') from $tb where id='$id' ");
	if($dr[type]=='internal_audit') $chkaudit='checked'; 
	elseif($dr[type]=='customer_complaints') $chkcomp='checked'; 
	else $chkother='checked';
	print "
<div class='formpage'>

<table width=100%><tr>
<td width=30%> $dr[dcc_number] </td><td width=40% align=center> Valid date : $dr[dcc_valid_date]  </td><td width=30% align=right>Approved by : $dr[dcc_approve_by] </td>
</tr>
</table><center>
<h2>แบบฟอร์มรายงาน NC และการติดตามการแก้ไข
<br>(Nonconformity Report)
</h2>
 
</center>
<div class=box>
<table width=100%><tr><td>
<span class=box style=width:30%>NC No. <span class=data>$dr[id] </span></span>
</td><td align=right>
<input type=radio name=type $chkaudit > Internal Audit
<input type=radio name=type $chkcomp > Customer Complaints
<input type=radio name=type $chkother > Other
</td></tr></table>";
$slist=array('report','analyse','corrective','preventive','follow1','follow2');
//,'การป้องกันไม่ให้เกิดขึ้นซ้ำ (Corrective Action) '
$ttlist=array('รายละเอียดความไม่สอดคล้อง '
,'การวิเคราะห์สาเหตุ (Root Cause) '
,'การแก้ไข (Correction) '
,'การป้องกันไม่ให้เกิดซ้ำ(Corrective Action)'
,'ครั้งที่ 1','ครั้งที่ 2');
$bytext="รายงานโดย แก้ไขเรียบร้อยแล้ว วิเคราะห์โดย การป้องกันเรียบร้อยแล้ว ผู้ติดตาม ผู้จัดการ";
$bylist=explode(' ',$bytext);
for($i=0;$i<count($slist);$i++){
	$detailfld=$slist[$i].'_detail';
	$byfld=$slist[$i].'_by';
	$datefld=$slist[$i].'_date';
	$closedfld=$slist[$i].'_closed';
	$tt=$ttlist[$i];
	$detail=$dr[$detailfld];
	$by=$dr[$byfld];
	$date=$dr[$datefld];
	$closed=$dr[$closedfld];
	if($i==4) print "</div><div class=box><h3>การติดตามการแก้ไข</h3>";
	$cstatus='';
	if($i>3){
		if($closed==1) $cstatus="สถานะ <input type=radio checked> ปิด <input type=radio > ยังไม่ปิด ";
		else  $cstatus="สถานะ <input type=radio > ปิด <input type=radio checked> ยังไม่ปิด ";
	}
	print "<table width=100%><tr><td colspan=2><label>$ttlist[$i]</label ></td></tr><tr><td colspan=3 style=height:60px><span class=data style=height:80px;>$detail </span></td></tr>
	<tr><td><b>$bylist[$i]  ลงชื่อ</b> : <input type=text class=data value=\"$by\" size=40> </td><td align=center> $cstatus </td><td align=right><b> วันที่  : </b> <span class=data>$date </span> </td></tr></table><hr>";
}
	print "
</div>
หมายเหตุ : ระยะเวลาในการจัดเก็บเอกสาร 2 ปี
	</div>	
<style>

.formpage{background:#fff;width:210mm;padding:10mm;font-size:12pt}
h2{font-size:16pt;margin:5px;}
h3{margin:0;padding:0;font-size:12pt;}
label{font-size:12pt;font-weight:bold;}
.box{border:1px solid #666;padding:10px;}
.data{border:none;text-decoration:underline;line-height:150%;font-size:12pt;}
</style>	
	";
	
}

?>
