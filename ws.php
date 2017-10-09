<?php
$q="update ws_alcohol set opstaff=trim(left(logs,locate('\n',logs)-1)) where opstaff='' ;
update ws_drug set opstaff=trim(left(logs,locate('\n',logs)-1))  where opstaff='' ;
update ws_busweek set opstaff=trim(left(logs,locate('\n',logs)-1))  where opstaff='' ;
update ws_busmonth set opstaff=trim(left(logs,locate('\n',logs)-1))  where opstaff='' ;
update ws_talk set opstaff=trim(left(logs,locate('\n',logs)-1))  where opstaff='' ;
";
$q="update ws_alcohol set opstaff=trim(left(logs,locate('\n',logs)-1))  ;
update ws_drug set opstaff=trim(left(logs,locate('\n',logs)-1))   ;
update ws_busweek set opstaff=trim(left(logs,locate('\n',logs)-1))   ;
update ws_busmonth set opstaff=trim(left(logs,locate('\n',logs)-1)) ;
update ws_talk set opstaff=trim(left(logs,locate('\n',logs)-1))   ;
";
mysql_query($q);
if($ws=='opstaff'){ 
	monthnav();
	$wslist2=array('al'=>'Alcohol','am'=>'Drug','bw'=>'Bus Week','bm'=>'Bus Month','st'=>'Safety Talk');
	reset($wslist2);
	
	$thead.="<tr><td>Staff</td>";
	while(list($w,$t)=each($wslist2)){
		
		$thead.="<td>$t</td>";
	}
	$thead.="<td>Total</td></tr>";
	$q=" select staff from v_wsstaff where date_format(date,'%Y-%m')='$yr-$mo' and staff<>'' group by staff ";

	$ck=mysql_query($q);
	while(list($staff)=mysql_fetch_array($ck)){
		//$staff=trim($staff);
		if($staff=='') continue;
		$sum=0;
		$tbody.="<tr><td>$staff</td>";
		reset($wslist2);	
		while(list($w,$t)=each($wslist2)){
			$q="select c from v_opstaff where yr='$yr' and  mo='$mo' and opstaff='$staff' and ws='$w' ";
			$ck2=mysql_query($q); //print $q.'<br>';
			list($c)=mysql_fetch_array($ck2);
			$sum+=$c;
			$suma[$w]+=$c;
			$tbody.="<td class=int>$c</td>";
		}
		$tbody.="<td class=int>$sum</td></tr>";
	}
	$tfoot="<tr><td>Total</td>";
	reset($wslist2);
	$sum=0;
	while(list($w,$t)=each($wslist2)){
		$sum+=$suma[$w];
		$tfoot.="<td>$suma[$w]</td>";
		
	}
	$tfoot.="<td>$sum</td></tr>";
	print "<table class='rpt tb5'><thead>$thead</thead><tbody>$tbody</tbody><tfoot>$tfoot</tfoot></table><script>$('.tb5').dataTable();</script>";
	
}elseif($ws=='report'){
	reset($wslist);
	monthnav();
	print "<form action=?action=$action&ws=$ws&mo=$mo&yr=$yr method=post><span class=custselect> Customer <select name=customer onchange=this.form.submit();><option>".qoptions("select id,code from customer where fleet='$_SESSION[fleet]' order by code ",$customer)."</select></span><input type=submit value=Report></form>";
	
	while(list($w,$t)=each($wslist)){
		if($w=='report')continue;
		if($w=='opstaff')continue;
		$tli.= "<li><a  href=#tab-$w>$t</a></li>";
		$tdi.="<div id=tab-$w>";
		$tb="ws_".$w;
		if($w=='alcohol') $tdi .= monthrpt1('ws_'.$w,$customer,$mo,$yr);
		if($w=='drug') $tdi .= monthrpt1('ws_'.$w,$customer,$mo,$yr);
		if($w=='busweek') $tdi .= monthrpt2('ws_'.$w,$customer,$mo,$yr);
		if($w=='busmonth') $tdi .= monthrpt2('ws_'.$w,$customer,$mo,$yr);
		if($w=='talk') $tdi .= monthrpt3('ws_'.$w,$customer,$mo,$yr);
		else $tdi.="";
		$tdi.="</div>";
	}
	print "<div id=rpttabs><ul>$tli</ul>
	$tdi
	</div><script>$(function(){
		$('#rpttabs').tabs({});
	});	</script>";
}else{
	$tb='ws_'.$ws;
	$wsaction='record';
	$action='edit';
	if(!$ws) $action='';	
}
function monthrpt1($tb,$customer,$mo,$yr){
	$out= "<table class=tb5><thead><tr><td>No.</td><td>นักขับ</td>";
	for($i,1;$i<32;$i++){
		if(!checkdate($mo,$i,$yr)) continue;
		$out.= "<td width=2.7%>$i</td>";
		$maxd=$i;
	}
	$out.= "</tr></thead><tbody>";
	$q="select t1.driver,t2.name from vehicle as t1,employee as t2 where t2.id=t1.driver and t1.customer='$customer' and t2.fleet='$_SESSION[fleet]' order by t2.name ";
	
	$ck=mysql_query($q); //print $q;
	$ii=1;
	while(list($d,$dname)=mysql_fetch_array($ck)){
		$out.= "<tr><td>$ii</td><td style=text-align:left;>$dname</td>";
		for($i=1;$i<=$maxd;$i++){
			$rs='';
			$dd=$i;
			if($i<10) $dd='0'.$i;
			$q="select id,result from $tb where date='$yr-$mo-$dd' and driver='$d' order by id desc limit 1 ";
			$ck2=mysql_query($q);
			list($id,$rs)=mysql_fetch_array($ck2);
			
			if($rs==1) $icon="<a href=?action=ws&ws=edit&tb=$tb&id=$id><img src=images/icon-green.png></a>";
			if($rs==0){
				if($tb=='ws_alcohol'){
					$tt=qval("select level from $tb where date='$yr-$mo-$dd' and driver='$d' ");
				}
				$icon="<a href=?action=ws&ws=edit&tb=$tb&id=$id><img src=images/icon-red.png title='$tt'></a> ";
			} 
			if(mysql_num_rows($ck2)==0) $icon='';
			
			$out.= "<td > $icon </td>";
		} 
		$out.= "</tr>";
		$ii++;
	}
	$out.= "</tbody></table>";
	return $out;
}
function monthrpt2($tb,$customer,$mo,$yr){
	$out= "<table class=tb5><thead><tr><td>No.</td><td>รถ</td><td>พขร.</td>";
	for($i,1;$i<32;$i++){
		if(!checkdate($mo,$i,$yr)) continue;
		$out.= "<td width=2.7%>$i</td>";
		$maxd=$i;
	}
	$out.= "</tr></thead><tbody>";
	$q="select t1.id,t1.code,t2.name from vehicle as t1,employee as t2 where t2.id=t1.driver and t1.customer='$customer' and t1.fleet='$_SESSION[fleet]'  order by t1.code ";
	
	$ck=mysql_query($q); //print $q;
	$ii=1;
	$tbitem=$tb.'item';
	while(list($v,$vcode,$driver)=mysql_fetch_array($ck)){
		$out.= "<tr><td>$ii</td><td style=text-align:left;>$vcode</td><td style=text-align:left;>$driver</td>";
		for($i=1;$i<=$maxd;$i++){
			$rs='';
			$dd=$i;
			if($i<10) $dd='0'.$i;
			$q="select t2.id,avg(result) from $tbitem as t1,$tb as t2 where t2.id=t1.$tb and t2.date='$yr-$mo-$dd' and t2.vehicle='$v' group by t2.date,t2.vehicle ";
			$ck2=mysql_query($q); //print $q.'<br>';
			list($id,$rs)=mysql_fetch_array($ck2);
			
			if($rs==1) $icon="<a href=?action=ws&ws=edit&tb=$tb&id=$id><img src=images/icon-green.png></a>";
			else $icon="<a href=?action=ws&ws=edit&tb=$tb&id=$id><img src=images/icon-red.png title='$tt'></a> ";
			
			if(mysql_num_rows($ck2)==0) $icon='';
			
			$out.= "<td > $icon </td>";
		} 
		$out.= "</tr>";
		$ii++;
	}
	$out.= "</tbody></table>";
	return $out;
}
function monthrpt3($tb,$customer,$mo,$yr){
	$out= "<table class=tb5><thead><tr><td>No.</td><td>ลูกค้า</td>";
	for($i,1;$i<32;$i++){
		if(!checkdate($mo,$i,$yr)) continue;
		$out.= "<td width=2.7%>$i</td>";
		$maxd=$i;
	}
	$out.= "</tr></thead><tbody>";
	$q="select t1.id,t1.code from customer as t1 where fleet='$_SESSION[fleet]' and code<>'' order by t1.code ";
	
	$ck=mysql_query($q); //print $q;
	$ii=1;
	$tbitem=$tb.'item';
	while(list($c,$ccode)=mysql_fetch_array($ck)){
		$out.= "<tr><td>$ii</td><td style=text-align:left;>$ccode</td>";
		for($i=1;$i<=$maxd;$i++){
			$rs='';
			$dd=$i;
			if($i<10) $dd='0'.$i;
			$q="select id from $tb as t1 where  t1.date='$yr-$mo-$dd' and t1.customer='$c' group by t1.customer and t1.date ";
			$ck2=mysql_query($q); //print $q.'<br>';
			list($id)=mysql_fetch_array($ck2);
			if($id) $rs=1;
			if($rs==1) $icon="<a href=?action=ws&ws=edit&tb=$tb&id=$id><img src=images/icon-green.png></a>";
			if($rs==0){
				$icon="<a href=?action=ws&ws=edit&tb=$tb&id=$id><img src=images/icon-red.png title='$tt'></a>";
			} 
			if(mysql_num_rows($ck2)==0) $icon='';
			$out.= "<td > $icon </td>";
		} 
		$out.= "</tr>";
		$ii++;
	}
	$out.= "</tbody></table>";
	return $out;
}
if($ws=='view'){
	extract($_GET);
	edit($tb,$id,1);
	//$('input,select').attr('disabled','disabled');		
	print "<script>$(function(){
	
	$('input,select,textarea').attr('readonly','readonly');		
	$('input,select,textarea').attr('disabled','disabled');		
	});
	</script><style>
	input[readonly],select[readonly=readonly]{background:#fff;color:#000;border:none;}
	input[type=radio]:disabled{color:#000;}
	input[type=submit]{display:none;}
	</style>";
	
}
if($ws=='edit'){
	extract($_GET);
	edit($tb,$id,0);
	print "<script>$(function(){

	});
	</script><style>

	</style>";
	
}
/*
ALTER TABLE `ws_alcohol`  drop `opstaff` ;
ALTER TABLE `ws_drug`  drop `opstaff` ;
ALTER TABLE `ws_busweek`  drop `opstaff` ;
ALTER TABLE `ws_busmonth`  drop `opstaff` ;
ALTER TABLE `ws_talk`  drop `opstaff` ;

ALTER TABLE `ws_alcohol`  ADD `opstaff` VARCHAR(10) NOT NULL  AFTER `logs`;
ALTER TABLE `ws_drug`  ADD `opstaff` VARCHAR(10) NOT NULL  AFTER `logs`;
ALTER TABLE `ws_busweek`  ADD `opstaff` VARCHAR(10) NOT NULL  AFTER `logs`;
ALTER TABLE `ws_busmonth`  ADD `opstaff` VARCHAR(10) NOT NULL  AFTER `logs`;
ALTER TABLE `ws_talk`  ADD `opstaff` VARCHAR(10) NOT NULL  AFTER `logs`;

update ws_alcohol set opstaff=trim(left(logs,locate('\n',logs)-1)) ;
update ws_drug set opstaff=trim(left(logs,locate('\n',logs)-1)) ;
update ws_busweek set opstaff=trim(left(logs,locate('\n',logs)-1)) ;
update ws_busmonth set opstaff=trim(left(logs,locate('\n',logs)-1)) ;
update ws_talk set opstaff=trim(left(logs,locate('\n',logs)-1)) ;

create view v_opstaff as 
select 'al' as 'ws',year(date) as 'yr',month(date) as 'mo', opstaff,count(id) 'c' from ws_alcohol group by opstaff,date_format(date,'%Y-%m')
union 
select 'am' as 'ws',year(date) as 'yr',month(date) as 'mo', opstaff,count(id) 'c' from ws_drug group by opstaff,date_format(date,'%Y-%m')
union 
select 'bw' as 'ws',year(date) as 'yr',month(date) as 'mo', opstaff,count(id) 'c' from ws_busweek group by opstaff,date_format(date,'%Y-%m')
union
select 'bm' as 'ws',year(date) as 'yr',month(date) as 'mo', opstaff,count(id) 'c' from ws_busmonth group by opstaff,date_format(date,'%Y-%m')
union
select 'st' as 'ws',year(date) as 'yr',month(date) as 'mo', opstaff,count(id) 'c' from ws_talk group by opstaff,date_format(date,'%Y-%m')



drop view v_wsstaff;
create view v_wsstaff as 
SELECT 'al',id,trim(left(logs,locate('\n',logs)-1)) 'staff',mid(logs,locate('\n',logs)+1,10) as 'date' FROM `ws_alcohol`
union 
SELECT 'am',id,trim(left(logs,locate('\n',logs)-1)) 'staff',mid(logs,locate('\n',logs)+1,10) as 'date' FROM `ws_drug`
union
SELECT 'bw',id,trim(left(logs,locate('\n',logs)-1)) 'staff',mid(logs,locate('\n',logs)+1,10) as 'date' FROM `ws_busweek`
union
SELECT 'bm',id,trim(left(logs,locate('\n',logs)-1)) 'staff',mid(logs,locate('\n',logs)+1,10) as 'date' FROM `ws_busmonth`
union
SELECT 'st',id,trim(left(logs,locate('\n',logs)-1)) 'staff',mid(logs,locate('\n',logs)+1,10) as 'date' FROM `ws_talk`# MySQL returned an empty result set (i.e. zero rows).
*/

?>
