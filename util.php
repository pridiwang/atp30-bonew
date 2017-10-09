<?php //>
$fleets=array(0=>'web',1=>'chonburi',2=>'maptaphut',3=>'bangpra');
//print "action $action";
if($action=='util-vdoc-dup'){
	$q="select id from vehicle order by id ";
	print $q;
	$dt=qdt($q);
	//print_r($dt);
	while(list(,$dr)=each($dt)){
		print "<br>$dr[id] ";
		vdocdup('vehicle',$dr[id]);
	}
}
if($action=='util-emp-oid'){
	$q="delete from atp30_bonew.training where fleet>0 ";
	qexe($q);
	$q="delete from atp30_bonew.history where fleet>0 ";
	qexe($q);		
	for($f=1;$f<=3;$f++){
		$db2='atp30_'.$fleets[$f];
		$q="update $db2.employee set name=replace(name,'  ',' ') ";
		qexe($q);
		$q="update atp30_bonew.employee as t1 , $db2.employee as t2  set t1.oid=t2.id WHERE t2.name=t1.name";	
		print "$q <br>";
		qexe($q);
		
		$q="insert into atp30_bonew.training (fleet,oemp,date,course,note) select $f,employee,date,course,note from $db2.training 	";
		print "$q <br>";
		qexe($q);
		
		$q="insert into atp30_bonew.history (fleet,oemp,date,history,note) select $f,employee,date,history,note from $db2.history ";
		print "$q <br>";
		qexe($q);
		
		
	}
	$q="update training as t1,employee as t2 set t1.employee=t2.id  where t2.fleet=t1.fleet and t2.oid=t1.oemp ";
	qexe($q);
	$q="update history as t1,employee as t2 set t1.employee=t2.id  where t2.fleet=t1.fleet and t2.oid=t1.oemp ";
	qexe($q);

}
if($action=='util-dump-hr-train'){
	$oemp=187;
	$nemp=44;
	$odb='atp30_chonburi';
	$q="select $nemp,date,course,note from $odb.training where employee=$oemp ";
}
if($action=='util-dumpstock'){
	$q="truncate table st ";
	qexe($q);
	$q="truncate table stcard ";
	qexe($q);
	$q="update stbal set balance=0,cost=0,amount=0 ";
	qexe($q);
	while(list($f,$fl)=each($fleets)){
		//if($f==0) continue;
		$q="
		insert into atp30_bonew.st (part_no,name,unit,supplier,min_stock,cost,amount)
		select part_no,part_name,unit,supplier,min_stock,cost,amount from $dbn.stock where part_no<>'' and part_no not in (select part_no from atp30_bonew.st ) ";
		qexe($q);
		print "<li>$f $fl ";
		$dbn='atp30_'.$fl;
		$q="
		insert into atp30_bonew.stcard (fleet,date,ref_no,st,in_qty,cost,amount,note,record_by,logs)
		select $f,'2016-12-13','ย้ายจาก bo $fl ',t2.id,t1.balance,t1.cost,t1.amount,'Migrate In','$_SESSION[user]',concat(now(),'  by ','$_SESSION[user]\n') from $dbn.stock as t1,atp30_bonew.st as t2 where t2.part_no=t1.part_no order by t1.part_no ";
		print '<br>'.$q;
		qexe($q);
		//browse($q,'');
		
		
	}
	balall();
	
}
if($action=='clearfleets'){
	$tblist=array('vehicle','customer','employee','stock','mtn','contractor','ws_alcohol', 'ws_busweek', 'ws_busmonth','ws_drug','ws_talk','milage','fuel','tire','battery','sparepart','workorder','accident','breakdown', 'route', 'trip', 'plan','ac_prevention','ws_busweekitem','ws_busmonthitem','ws_talkitem', 'max_speed', 'suggest', 'suggest_action','history','training','timetable','work_table');
	while(list(,$tb)=each($tblist)){
		$q="truncate table atp30_bonew.$tb ";
		qexe($q);
	}
	unlink("images/employee/*");
	
}
if($action=='dumptb2fleets'){
	
	$tblist=array('stock');
	while(list($f,$fl)=each($fleets)){
		print "<li>$f $fl ";
		$dbn='atp30_'.$fl;
		reset($tblist);
		
		while(list(,$tb)=each($tblist)){
			$q="insert into atp30_bonew.$tb select id+$f*10000,$f,t1.* from $dbn.$tb as t1 ";
			print "<li> $q";
			qexe($q);
			/*if($tb=='stock'){
				$tb1=$tb.'card';
				$q="insert into atp_bonew.$tb1 select '',stock+$f*10000,vehicle+$f*10000,description,in_out,qty,record_by,logs from $dbn,$tb1  ";
				//print "<li> $q";
				//qexe($q);
			}
			*/
		}
	}
}
if($action=='dumpfleets'){
	$tblist=array('vehicle','customer','employee','stock','user','mtn','contractor','ws_alcohol', 'ws_busweek', 'ws_busmonth','ws_drug','ws_talk','milage','fuel','tire','battery','sparepart','workorder','accident','breakdown', 'route', 'trip', 'plan','ac_prevention','ws_busweekitem','ws_busmonthitem','ws_talkitem', 'max_speed', 'suggest', 'suggest_action','history','training','timetable','work_table');
	$newid=array('customer','vehicle','driver','route','trip','ws_talk','ws_busweek','ws_busmonth','accident','workorder','suggest','employee','timetable');
	$tblist=array('ta');
	$newid=array('employee');
	while(list(,$tb)=each($tblist)){
		$q="truncate table atp30_bonew.$tb ";
		qexe($q);
	}
	while(list($f,$fl)=each($fleets)){

		print "<li>$f $fl ";
		$dbn='atp30_'.$fl;
		reset($tblist);
		while(list(,$tb)=each($tblist)){

			$q="insert into atp30_bonew.$tb select id+$f*10000,$f,t1.* from $dbn.$tb as t1 ";
			qexe($q);
			print "<li>$q";
			reset($newid);
			while(list(,$fld)=each($newid)){
				$q=" update atp30_bonew.$tb set $fld=$fld+$f*10000 where $fld<10000 ";
				qexe($q);print "<li>$q";	
			}
			
			

		}

		$q="truncate table atp30_bonew.ac_prevention ";
		qexe($q);
		$q=" insert into atp30_bonew.ac_prevention (accident,actions,status,logs) select accident+$f*10000,actions,status,logs from $dbn.ac_prevention ";
		qexe($q); print "<li>".$q;
		$dir="../bo".$fleets[$f]."/images/employee";
		$dh=opendir($dir);
		while($file=readdir($dh)){
			iF($file=='.')continue;
			iF($file=='..')continue;
			$src="$dir/$file";
			$target="images/employee/".$f."0".$file;
			print "<br>$src $target ";
			copy($src,$target);
			
		}
	
	}
	$q="
	update `vehicle` set type=left(code,1);
	update `vehicle` set type=left(code,2) where code like 'M%%' ;
	update `vehicle` set type=left(code,3) where code like 'C%%' ;
	update vehicle set seq=mid(code,3,3);
	update vehicle set seq=mid(code,4,2) where type like 'M%%';
	update vehicle set seq=mid(code,5,2) where type like 'C%%';
	delete from vehicle where code='';
	delete from employee where name IS NULL;
	";
	qexe($q);
	$q="delete from atp30_bonew.employee where type='office' ";
	qexe($q);
	$q=" insert into atp30_bonew.employee select id,0,t1.* from atp30_web.employee  as t1 where t1.type='office' ";
	qexe($q); print "<li>".$q;
	$q=" insert into atp30_bonew.timetable select id,0,t1.* from atp30_web.timetable  as t1  ";
	qexe($q); print "<li>".$q;
	$q=" insert into atp30_bonew.work_table select id,0,t1.* from atp30_web.work_table  as t1  ";
	qexe($q); print "<li>".$q;
	$q=" truncate table atp30_bonew.ta";
	qexe($q); print "<li>".$q;
	$q="insert into atp30_bonew.ta select id,0,t1.* from atp30_web.ta as t1  ";
	qexe($q); print "<li>".$q;
	
	
	$dir="../backoffice/images/employee";
	$dh=opendir($dir);
	while($file=readdir($dh)){
		iF($file=='.')continue;
		iF($file=='..')continue;
		$src="$dir/$file";
		$target="images/employee/$file";
		print "<br>$src $target ";
		copy($src,$target);
		
	}
	
}
if($action=='empphoto'){
	$dir="images/employee";
	$dh=opendir($dir);
	while($file=readdir($dh)){
		if($fild==".") continue;
		if($fild=="..") continue;
		print "<img src=$dir/$file width=300><br>$file<hr>";
	}
	closedir($dh);
}
if($action=='getsup'){
	$ck=mysql_query("select id,name from supplier ");
	while(list($i,$t)=mysql_fetch_array($ck)){
		print "$i $t <br>";
		$q=" update stockpart set supplier=$i where supplier_name like '%%$t%%' ";
		mysql_query($q);
	}
}
if($action=='fixzero'){
	$ck=mysql_query("select id,date,time,vehicle,milage from fuel where last_milage=0 ");
	while(list($fid,$date,$time,$vehicle,$m)=mysql_fetch_array($ck)){
		$q="select milage from fuel where vehicle='$vehicle' and ( date < '$date' or (date='$date' and time< '$time') )  order by date desc,time desc limit 1 ";
		$ck2=mysql_query($q); //print $q;
		list($lm)=mysql_fetch_array($ck2);
		print "$m v $vehicle fid $fid d $date t $time lm $lm<br>";
		if(($lm<$m)&&($lm!=0)) mysql_query("update fuel set last_milage='$lm' where id='$fid' ");
	}
}
if($action=='util-checknobadge'){
	print "check employee no badge <br>";
	$ck=mysql_query("select id,name from employee where badgenumber='' and type='office' and resign=0 ");
	while(list($i,$n)=mysql_fetch_array($ck)){
		print "<a href=?action=edit&id=$i&tb=employee>$i $n</a><br>";

	}

}


?>
