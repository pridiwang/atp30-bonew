<?php 
$codes=array(
		'L'=>array('L','Leave','yellow'),
		'A'=>array('A','Accident','pink'),
		'B'=>array('B','Breakdown มีผลต่อโบนัส','orange'),
		'B-'=>array('B','Breakdown ไม่มีผลต่อโบนัส','white'),
		'Al'=>array('Al','แอลกอฮอล์','cyan'),
		'Am'=>array('Am','สารเสพติด','cyan'),
		'F'=>array('F','ตกรถ','grey'),
		'X'=>array('X','ผิดวินัยร้ายแรง','red'),
		'S'=>array('S','เริ่มงาน','magenta'),
		'R'=>array('R','ลาออก','grey')
		
	);
if(substr($action,0,5)=='12yim') print "
<h2>ระบบบันทึก 12 ยิ้ม <a href=?action=12yim>รายเดือน</a> | 
<a href=?action=12yim-sum>รายปี</a> </h2>";
$monlistth=array('','มค.','กพ.','มีค.','เมย.','พค.','มิย.','กค.','สค.','กย.','ตค.','พย.','ธค.');
if($action=='12yim-bal-save'){
	extract($_POST);
	if($_GET[yim]) $q=" update 12yimbal set yim='$yim' ,logs=concat(logs,now(),' updated by $_SESSION[user]\n') where driver='$driver' and year='$year' ";
	else  $q=" insert into 12yimbal (driver,year,yim,logs) values ('$driver','$year','$yim',concat(now(),' added by $_SESSION[user]\n') ) ";
	qexe($q); //print $q;
	$action='12yim-sum';
}
if($action=='12yim-bal'){
	$q="delete * from 12yimbal where yim=0 ";
	qexe($q);
	$dname=qval("select name from employee where id='$driver' ");
	$q="select yim from 12yimbal where driver='$driver' and year='$year' ";
	$yim=qval($q);
	print "<center><table class=tb><form action=?action=$action-save&driver=$driver&year=$year&yim=$yim  method=post>
	<tr class=bd><td>$dname $year </td></tr>
	<tr class=bd><td><input type=text name=yim value=$yim></td></tr>
	<tr class=bd><td><button type=submt >Save</button></td></tr>
	</form></table>";
}
if($action=='12yim-record-save'){
	//$description=htmlspecialchars($description);
	$description=addslashes($description);
	if($id) $q=" update 12yim set code='$code',description='$description',logs=concat(logs,now(),' updated by $_SESSION[user]\n') where id='$id' ";
	else $q=" insert into 12yim (driver,date,code,description,logs) values ('$driver','$date','$code','$description',concat(now(), ' added by $_SESSION[user]\n') ) ";
	qexe($q); //print $q;
	$action='12yim';
}
if($action=='12yim-record'){
	$dname=qval("select name from employee where id='$driver' ");
	
	$q="select id,code,description from 12yim where driver='$driver' and date='$date' ";
	$ck=mysql_query($q);
	list($id,$code,$desc)=mysql_fetch_array($ck);
	while(list($c,$data)=each($codes)){
		if($code==$c)$coptions.="<option selected value=$c>$data[0] ".$data[1];
		else $coptions.="<option value=$c>$data[0] ".$data[1];
	}	
	print "<center><table class='tb'><form action=?action=$action-save&driver=$driver&date=$date&id=$id method=post>
	<thead><tr><td>$dname $date </td></tr></thead><tbody>
	<tr class=bd><td><select name=code>$coptions</select></td></tr>
	<tr class=bd><td><textarea name=description cols=30 rows=3>$desc</textarea></td></tr>
	<tr><td><input type=submit value=Save></td></tr>
	</form></table><button type=button onclick=history.back();>back</button>";
	//print"utype $_SESSION[utype] ulevel $_SESSION[ulevel] ";
	if(in_array($_SESSION[ulevel],array('md','manager'))){
		print " <a  href=?action=delete&tb=12yim&id=$id onclick=\"return confirm('Confirm Delete?');\" style=color:red;>delete</a>";
	}
}
if($action=='12yim'){
	
	while(list($c,$data)=each($codes)) 	print "<span class='yimlabel $c'>$c $data[1] </span>";
	
	monthnav();
	
	print "<table class='tb1 dt'><thead><tr><td>ลูกค้า</td><td>รถ</td><td>นักขับ</td><td>เริ่มงาน</td>";
	for($i=1;$i<32;$i++){
		if(!checkdate($mo,$i,$yr)) continue;
		print "<td>$i</td>";
		$days[]=$i;
	}
	print "</tr></thead><tbody>";
	$q="select t2.id, t2.name,t2.employed,t2.resign_date,t2.resign from employee as t2 where t2.type='driver' and t2.employed<'$yr-$mo-31' and (t2.resign=0  or (t2.resign=1 and t2.resign_date>'$yr-$mo-01'  )) and t2.fleet='$_SESSION[fleet]' order by name ";
	$ck=mysql_query($q); //print $q;
	while(list($id,$name,$employed,$resigned,$isresign)=mysql_fetch_array($ck)){
		$q="select t2.code,t1.code from vehicle as t1,customer as t2 where t2.id=t1.customer and t1.driver='$id' ";
		$ck2=mysql_query($q);
		list($cust,$v)=mysql_fetch_array($ck2);
		
		print "<tr class='bd'><td>$cust</td><td>$v</td><td>$name</td><td>$employed</td>";
		reset($days);
		while(list(,$d)=each($days)){
			$c='';$cl='';
			$dd=$d;
			if($d<10)$dd='0'.$d;
			$date="$yr-$mo-$dd";
			$q="select code from 12yim where driver='$id' and date='$date' ";
			$c=qval($q);
			if($date<$employed) $cl='bS';
			if($date==$employed) $c='S';
			if($date==$resigned) $c='R';
			if(($resigned>'0000-00-00')&&($date>=$resigned)) $cl='bS';
			
			print "<td class='yim $cl $c' date='$date' driver='$id' title=$date 
			onclick=window.location.href='?action=12yim-record&driver=$id&date=$date';
			>$c</td>";
		} 
		print "</tr>";
	}
	print "</tbody></table><script>	$('.dt').dataTable({bPaginate:false});	</script>";
}
if($action=='12yim-sum'){
	
	while(list($c,$data)=each($codes)) 	print "<span class='yimlabel $c'>$c $data[1] </span>";
	
	yearnav();
	
	print "<table class='tb1 dt'><thead><tr><td>ลูกค้า</td><td>รถ</td><td>นักขับ</td>
	<td>เริ่มงาน ลาออก</td>
	<td>ยิ้มยกมา</td>";
	for($i=1;$i<13;$i++){
		print "<td>".$monlistth[$i]."</td>";
		$months[]=$i;
	}
	print "</tr></thead><tbody>";
	$q="select t2.id, t2.fleet, t2.name,t2.employed,t2.resign_date,t2.resign from employee as t2 where t2.type='driver'   and t2.fleet='$_SESSION[fleet]' and t2.employed<'$yr-$mo-31' and (t2.resign=0  or (t2.resign=1 and t2.resign_date>'$yr-$mo-01' ))  ";
	$ck=mysql_query($q); //print $q;
	while(list($id,$fl,$name,$employed,$resigned,$isresign)=mysql_fetch_array($ck)){
		
		$yr1=substr($employed,0,4);$mo1=substr($employed,5,2);
		
		if($resigned<>'0000-00-00'){
			$yr2=substr($resigned,0,4);$mo2=substr($resigned,5,2);
			$rs=$resigned;
			
		}else{
			$rs='';
			$yr2=9999;
		}
		//if($yr1>$yr) continue;
		if($yr2<$yr) continue;
		
		
		$q="select t2.code,t1.code from vehicle as t1,customer as t2 where t2.id=t1.customer and t1.driver='$id' ";
		$ck2=mysql_query($q);
		list($cust,$v)=mysql_fetch_array($ck2);
		$bal=qval("select yim from 12yimbal where driver='$id' and year='$yr' ");
		$add="onclick=window.location.href='?action=12yim-bal&driver=$id&year=$yr'";
		if($yr<=$yr1) $add='';
		print "<tr class='bd'><td>$cust</td><td>$v</td><td>$name</td>
		<td>$employed <br>$rs </td>
		<td	$add >$bal</td>";
		
		
		reset($ms);
		for($m=1;$m<13;$m++){
			$c='';$cl='';
			$mo=$m;
			if($m<10)$mo='0'.$m;

			$q="select code from 12yim where driver='$id' and year(date)='$yr' and month(date)='$mo' ";
			$ck2=mysql_query($q);
			$c='';
			while(list($cc)=mysql_fetch_array($ck2)){
				$c.=$cc." ";
			}
			if($date<$employed) $cl='bS';
			if($date==$employed) $c='S';
			if($date==$resigned) $c='R';
			if(($resigned>'0000-00-00')&&($date>=$resigned)) $cl='bS';
			
			if($c=='') $bal++;
			else $bal=0;
			if($bal>12) $bal=1;
			if( $yr>date("Y") ) $bal='';
			if( $yr>=date("Y") && $m>=date("m") ) $bal='';
			if($yr<$yr1) $bal='';
			if(($yr==$yr1)&&($m<=$mo1)) $bal='';
			if($resigned<>'0000-00-00'){
				if($yr>$yr2) $bal='';
				if(($yr==$yr2)&&($m>=$mo2)) $bal='';
			}
			$img='';
			if($bal) $img="<img src=images/yim.png width=20><br>";
			$addclass='';
			if($bal>0) $addclass='yim1';
			print "<td width=20 class='yim $addclass $cl $c' date='$date' driver='$id' title=$date 
			onclick=window.location.href='?action=12yim-record&driver=$id&date=$date';
			>$c $bal</td>";
		} 
		//print "<td>$bal</td>";
		print "</tr>";
	}
	print "</tbody></table><script>	$('.dt').dataTable({bPaginate:false});	</script>";
}
if(substr($action,0,5)=='12yim'){
	print "<style>
	.yim{text-align:center;}
	.yimlabel{text-align:center;margin:3;padding:3 5px;border:1px solid #666;}
	
	.bS{background:#ddd;}
	.S{background:#ddd;}
	.L{background:#fc0;}
	.A{background:#faa;}
	.B{background:#f90;}
	.B-{background:#fff;}
	.Al{background:#aaf;}
	.Am{background:#aaf;}
	.X{background:#f22;}
	.S{background:#f0f;}
	.F{background:#aaa;}
	</style>";
}
?>