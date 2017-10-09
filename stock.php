<?php 
/*
insert into atp30_bonew.st (part_no,part_name,unit,cost,amount,sup,supplier,min_stock)
select part_no,part_name,balance,unit,cost,amount,sup,supplier,min_stock from atp30_bo.stock
datadict st master
stcard  for in/out to fleet 
fleet 0=center stock
*/
 

if((substr($action,0,6)=='stock-')||(substr($tb,0,2)=='st')){
	if($_SESSION[fleet]==0 ) $salist=array('balance'=>'ยอดคงเหลือ','balance-all'=>'ยอดรวม','list'=>'รายการ','transfer'=>'ใบโอน','receive'=>'ใบรับ');
	else $salist=array('balance'=>'ยอดคงเหลือ','balance-all'=>'ยอดรวม','list'=>'รายการ','transfer'=>'รับโอน','request'=>'ใบเบิก');//,'transfer','adjust','check');//,'transfer','adjust','check');
	
	print "<div class='depmenu noprint' ><center> รายการสต๊อค | ";
	while(list($a,$t)=each($salist)){ 
		print "<a href=?action=stock-$a>";
		if($action=='stock-'.$a) print "<b> $t </b>  ";
		else print " $t ";
		print "</a> | ";
	}
	print "</center></div>";
}
if($action=='stock-request-complete'){
	$dr=qdr("select * from streq where id='$id' ");
	if($dr[type]=='transfer'){
		$q="insert into stcard (date,fleet,st,out_qty,cost,ref_no,record_by,logs) select t2.date,t2.fleet,t1.st,t1.qty,t3.cost,concat('TRO-',t2.book_no,'/',t2.number),'$_SESSION[user]',concat(now(),' completed by $_SESSION[user]\n') from streq_item as t1,streq as t2,st as t3 where t2.id=t1.streq and t3.id=t1.st and t2.id='$id' ";
		//print $q.'<br>';
		qexe($q);
		$q="insert into stcard (date,fleet,st,in_qty,cost,ref_no,record_by,logs) select t2.date,$dr[to_fleet],t1.st,t1.qty,t3.cost,concat('TRI-',t2.book_no,'/',t2.number),'$_SESSION[user]',concat(now(),' completed by $_SESSION[user]\n') from streq_item as t1,streq as t2,st as t3 where t2.id=t1.streq and t3.id=t1.st  and t2.id='$id' ";
		//print $q.'<br>';
		qexe($q);
		
	}
	if($dr[type]=='request'){
		$q="insert into stcard (date,fleet,st,out_qty,cost,vehicle,ref_no,record_by,logs) select t2.date,t2.fleet,t1.st,t1.qty,t3.cost,t2.vehicle,concat('REQ-',t2.book_no,'/',t2.number),'$_SESSION[user]',concat(now(),' completed by $_SESSION[user]\n') from streq_item as t1,streq as t2,st as t3 where t2.id=t1.streq and t3.id=t1.st   and t2.id='$id' ";
		qexe($q);
		//print $q.'<br>';
	}
	if($dr[type]=='receive'){
		
		$q="insert into stcard (date,fleet,st,in_qty,cost,ref_no,record_by,logs) select t2.date,t2.fleet,t1.st,t1.qty,t1.cost, concat('REC-',t2.book_no,'/',t2.number),'$_SESSION[user]',concat(now(),' completed by $_SESSION[user]\n') from streq_item as t1,streq as t2,st as t3 where t2.id=t1.streq and t3.id=t1.st   and t2.id='$id' ";
		qexe($q);
	}
	$q="update streq set status='complete',logs=concat(logs,now(),' completed by $_SESSION[user]\n') where id='$id' ";
	//print $q.'<br>';
	qexe($q);
	$q="select st from streq_item where streq='$id' ";
	$dt=qdt($q); //print $q;//print_r($dt);
	while(list(,$dr1)=each($dt)){
		//print "st $dr[st] <br>";
		balcal($dr1[st],$dr[fleet]);
		if($dr[type]=='transfer') balcal($dr1[st],$dr[to_fleet]);
	}
	$action='stock-request';$tb='streq';
}
if(($action=='stock-request')||($action=='stock-transfer')||($action=='stock-receive')){
	$info=explode('-',$action);
	$type=$info[1];
	
	monthnav();
	//$q="select * from streq order by date ";
	$tb='streq';
	$cond.=" and type='$type' and fleet='$_SESSION[fleet]' and date_format(date,'%Y%m')='$yr$mo' ";
	if(($type=='transfer')&&($_SESSION[fleet]!=0)) $cond=" and type='$type' and to_fleet='$_SESSION[fleet]' and date_format(date,'%Y%m')='$yr$mo' ";
	$action='browse';
	if($type=='request') $hiddenflds=array('to_fleet');
	if($type=='transfer') $hiddenflds=array('vehicle');
	if($type=='receive') $hiddenflds=array('to_fleet','vehicle');
	//browse($q,'');
if($tb=='streq'){
	if($_SESSION[fleet]==0) $hiddenflds=array('vehicle');
	else $hiddenflds=array('to_fleet');
}}
/*
if($action=='stock-transfer'){
	$amount=$tr_qty*$cost;
	$tofname=qval("select name from fleet where id='$tofleet' ");
	$fromfname=qval("select name from fleet where id='$_SESSION[fleet]' ");
	$q=" insert into stcard 
	(fleet,st,date,ref_no,out_qty,cost,amount,note,record_by) 
	values
	('$_SESSION[fleet]','$st','$date','TRout $tofleet $ref_no','$tr_qty', '$cost','$amount','TR->$tofname $note','$_SESSION[user]') 
	";
	qexe($q);
	balcal($st,$_SESSION[fleet]);
	
	
	$q=" insert into stcard 
	(fleet,st,date,ref_no,in_qty,cost,amount,note,record_by) 
	values
	('$tofleet','$st','$date','TRin $ref_no','$tr_qty','$cost','$amount','TR < $fromfname $note','$_SESSION[user]') 
	";
	qexe($q);
	balcal($st,$tofleet);
	$action='stock-card';
}
*/
if($action=='stock-card-delete'){
	$q="delete from stcard where id='$iid' ";
	qexe($q);
	balcal($st,$_SESSION[fleet]);
	$action='stock-card';
	$id=$st;
	
}
if($action=='stock-card-record'){
	$bal=$balance+$in_qty-$out_qty;
	$amount=$bal*$cost;
	$q=" insert into stcard 
	(fleet,st,date,ref_no,in_qty,out_qty,vehicle,balance,cost,amount,note,record_by,logs) 
	values
	('$_SESSION[fleet]','$st','$date','$ref_no','$in_qty', '$out_qty', '$_POST[vehicle]','$bal','$cost','$amount','$note','$_SESSION[user]',concat(now(),' by $_SESSION[user]\n') ) 
	";
	qexe($q); //print $q;
	balcal($st,$_SESSION[fleet]);
	$action='stock-card';
	$id=$st;
}
if($action=='stock-card'){
	
	if($_GET[id]>0){ 
		$st=$id;
		$partno=qval("select part_no from st where id='$id' ");
	}
	if($_POST[partno]){
		$st=qval("select id from st where part_no='$_POST[partno]' ");
	}
	print "<h3>Stock Card </h3><form action=?action=$action  method=post><input type=text value='$partno' name=partno size=4 onchange=$('#st').val(this.val());><select name=st id=st onchange=this.form.submit();><option value=''>select part ".qoptions("select id,concat(part_no,' ',name,'/',unit) from st order by part_no",$st)."</select> date ".daterange4()."<input type=submit value=Go></form>";
	balcal($st,$_SESSION[fleet]);
	$q="select sum(in_qty-out_qty) from stcard where st='$st' and fleet='$_SESSION[fleet]' and date<'$date1' ";
	$balance=qval($q);
	if(!$balance) $balance=0;
	$q="select sum(in_qty*cost)/sum(in_qty) from stcard where st='$st'  and fleet='$_SESSION[fleet]' and date<'$date1' ";
	$cost=round(qval($q),2);
	$amount=round($cost*$balance,2);
	print "<table class=rpt><thead><tr class=hd><td>Date</td><td>Ref No.</td><td>In</td><td>Out</td><td>Vehicle</td><td>Cost</td><td>Balance</td><td>Amount</td><td>Note</td><td>By</td><td></td></tr>
	<tr class=bd><td colspan=5> Open Balance</td><td class=real>$cost</td><td class=int>$balance</td><td class=real>$amount</td><td colspan=3> </td></tr>	</thead><tbody>";
	
	
	$q="select * from stcard where st='$st'  and fleet='$_SESSION[fleet]' and date between '$date1' and '$date2' order by date ";
	$ck=mysql_query($q);
	while($dr=mysql_fetch_assoc($ck)){
		$balance+=$dr[in_qty];
		$balance-=$dr[out_qty];
		$dr[amount]=$dr[cost]*$balance;
		$vcode=qval("select code from vehicle where id='$dr[vehicle]' ");
		print "<tr class=bd><td>$dr[date]</td><td>$dr[ref_no]</td><td class=int>$dr[in_qty]</td><td class=int>$dr[out_qty]</td><td>$vcode</td>
		<td class=real>$dr[cost]</td><td class=int>$balance</td><td class=real>$dr[amount]</td>
		<td>$dr[note]</td>
		<td>$dr[record_by]</td>
		<td>
		<a href=?action=$action-delete&iid=$dr[id]&st=$st&date1=$date1&date2=$date2 onclick=\"return confirm('confirm delete?');\">x</a> | 
		<a href=?action=edit&tb=stcard&id=$dr[id]&st=$st&date1=$date1&date2=$date2 >edit</a>
		</td></tr>";
		
	}
	//balupdate($st,$balance,$cost,$lastdate);
	$date=date("Y-m-d");
	$cost=qval("select cost from stbal where st='$st' and fleet='$_SESSION[fleet]' ");
	print "</tbody>
	<tfoot>
	<form action=?action=$action-record&st=$st&balance=$balance method=post>
	<tr>
	<td><input name=date type=text class=date value=$date></td>
	<td><input name=ref_no type=text placeholder='ref no.'></td>
	<td><input size=2 name=in_qty type=text placeholder='in'></td>
	<td><input size=2 name=out_qty type=text placeholder='out'></td>
	<td><select name=vehicle ><option value=''>".qoptions("select id,code from vehicle where fleet='$_SESSION[fleet]' order by code ",'')."</select></td>
	<td><input size=3 name=cost type=text value=$cost ></td>
	<td colspan=3><input size=40 name=note type=text placeholder='note' ></td>
	<td colspan=2><input type=submit value=Record></td></tr></form>
	<form action=?action=stock-transfer&st=$st method=post><tr>
	<td><input name=date type=text class=date value=$date></td>
	<td><input name=ref_no type=text placeholder='ref no.'></td>
	<td colspan=2> to <select name=tofleet><option value=''>".qoptions("select id,name from fleet where id<>'$_SESSION[fleet]' ")."</select></td>
	<td><input size=5 type=text name=tr_qty placeholder=qty></td>
	<td><input size=3 name=cost type=text value=$cost ></td>
	<td colspan=3><input size=40 name=note type=text placeholder='note' ></td>
	<td colspan=2><input type=submit value=Transfer></td>
	</tr></form>
	</tfoot>
	</table>";
	
}
function balall(){
	for($f=0;$f<4;$f++){
		$q="select id from st ";
		$dt=qdt($q);
		while(list(,$dr)=each($dt)){
			//print "F $f $dr[id]<br>";
			balcal($dr[id],$f);
		}
	}
}
function balcal($st,$f){
	$q="select * from stbal where st='$st' and fleet='$f' ";
	$ck=mysql_query($q);
	if(mysql_num_rows($ck)==0){
		$q=" insert into stbal (st,fleet) values ('$st','$f' ) ";
		qexe($q);
	}
	$balance=qval("select sum(in_qty-out_qty) from stcard where st='$st' and fleet='$f' ");
	$abalance=qval("select sum(in_qty-out_qty) from stcard where st='$st' ");
	if(!$balance){ 
		$balance=0;
		
	}
	$cost=qval("select sum(in_qty*cost)/sum(in_qty) from stcard where st='$st' and in_qty>0 ");
	if(!$cost)  $cost=0;
	$date=qval("select date from stcard where st='$st' and in_qty>0 order by date desc limit 1 ");
	if(!$date) $date=date("Y-m-d");
	$q=" update stbal set cost='$cost',date='$date' where st='$st'  ";
	qexe($q); //print '<br>'.$q;
	$q=" update stbal set balance='$balance',amount=$balance*$cost where st='$st' and fleet='$f' ";
	qexe($q); //print '<br>'.$q;
	if($cost>0){
		$q="update st set cost='$cost' where id='$st' ";
		qexe($q);
	}
	//print "<li> st $st fl $f balance $balance abalance $abalance cost $cost ";
}
if($action=='stock-list'){
	print "<a href=?action=new&tb=st> + + New Stock SKU + + </a>";
	$q="select id,part_no,name,unit,supplier,min_stock,cost,note from st ";
	$toaction='stock-edit&tb=$st';
	
	browse($q,'st');
}
if($action=='stock-edit') edit('st',$id);
if($action=='stock-balance'){
	$q=" insert into stbal (fleet,st,date) select '$_SESSION[fleet]',id,now() from st where id not in (select st from stbal where fleet='$_SESSION[fleet]') and part_no<>'' order by st.part_no ";
	qexe($q);
	$q="select t2.id,t2.part_no,t2.barcode,t2.name,t1.balance,t2.unit,t2.cost,t1.amount from stbal as t1,st as t2 where t2.id=t1.st and t1.fleet='$_SESSION[fleet]' ";
	$toaction='stock-card';
	browse($q,'st');
}
if($action=='stock-balance-all'){
	$q="select t1.st,t2.part_no,t2.name,t2.unit,t2.cost
	,cast(sum(if(t1.fleet=0,t1.balance,0)) as UNSIGNED INTEGER) 'store'
	,cast(sum(if(t1.fleet=1,t1.balance,0)) as UNSIGNED INTEGER) 'chonburi'
	,cast(sum(if(t1.fleet=2,t1.balance,0)) as UNSIGNED INTEGER) 'maptaphut'
	,cast(sum(if(t1.fleet=3,t1.balance,0)) as UNSIGNED INTEGER) 'bangpra'
	,cast(sum(t1.balance) as UNSIGNED INTEGER) 'total'
	from stbal as t1,st as t2 where t2.id=t1.st group by t1.st";
	$toaction='stock-card-all';
	browse($q,'stbal');
}
if($action=='stock-card-all'){
	$q="select t1.st,t3.name,t2.part_no,t2.name,t1.balance,t1.cost,t1.balance*t1.cost 'amount' from stbal as t1,st as t2,fleet as t3 where t2.id=t1.st and t3.id=t1.fleet and t1.st='$id' ";
	$toaction='stock-card';
	browse($q,'st');
}
if($action=='stock-balall') balall();
if($action=='stock-reset'){
	$q=" truncate table st ";
	qexe($q);
	$q=" truncate table stcard ";
	qexe($q);
	$q=" truncate table stbal ";
	qexe($q);
	$q=" insert into st (part_no,name,unit,supplier,min_stock) 
	select distinct(part_no),part_name,unit,supplier,min_stock from stsrc group by part_no order by part_no ";
	qexe($q);
	$q=" insert into stcard (fleet,st,date,in_qty,cost,amount,ref_no,note)
	select t1.fleet,t2.id,'2016-12-29',t1.balance,t1.cost,t1.balance*t1.cost,'open','migrate' from stsrc as t1,st as t2 where t2.part_no=t1.part_no order by t1.fleet,t1.part_no ";
	qexe($q);
	$q=" truncate table streq ";
	qexe($q);
	$q=" truncate table streq_item ";
	qexe($q);
	balall();
}

?>