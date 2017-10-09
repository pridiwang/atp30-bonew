<?php
$sid=session_id();
$now=date("Y-m-d H:i");
require "flddict.php";
if(!$date1) $date1=date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
if(!$date2) $date2=date("Y-m-d",mktime(0,0,0,date("m")+1,0,date("Y")));
$picmax=500;
$picmaxsize=100; //k
$skipflds=array('action','iid','btn');
$vdoclist=array('reg'=>'รายการจดทะเบียน','prb'=>'พรบ','insure'=>'ประกัน','receipt'=>'ใบเสร็จป้ายวงกลม');
if($action=='vdoc-upload'){
	$dir="doc/$tb/$id";
	
	while(list($c,$f)=each($vdoclist)){
		
		if($_FILES['userfile-'.$c]){
			$file=$dir."/$c.pdf";
			$rs=move_uploaded_file($_FILES['userfile-'.$c][tmp_name],$file);
			if($rs) print "<br>$f uploaded ";
			
		}
	}
	$action='edit';
}
function vdocdup($tb,$id){
	global $vdoclist;
	$dir="doc/$tb";
	if(!file_exists($dir)) mkdir($dir);
	$dir="doc/$tb/$id";
	if(!file_exists($dir)) mkdir($dir);
	
	reset($vdoclist);
	$attlist=array('จ'=>'reg','พ'=>'prb','ป'=>'insure','บ'=>'receipt');
	$adir="attach/$tb/$id";
	$dh=opendir($adir);
	while($file=readdir($dh)){
		if($file=='.')continue;
		if($file=='..')continue;
		$prefix=substr($file,0,3);
		$c=$attlist[$prefix];
		$sfile=$adir.'/'.$file;
		$tfile=$dir.'/'.$c.".pdf";
		print "<br> $prefix $c $sfile -> $tfile";
		copy($sfile,$tfile);
	}
}
function vdoc($tb,$id){
	global $vdoclist;
	//vdocdup($tb,$id);
	$dir="doc/$tb";
	if(!file_exists($dir)) mkdir($dir);
	$dir="doc/$tb/$id";
	if(!file_exists($dir)) mkdir($dir);
	
	reset($vdoclist);
	while(list($c,$f)=each($vdoclist)){
		$file=$dir."/$c.pdf";
		$th.="<td>$f</td>";
		if(file_exists($file)){
			$docs.= "<td><a href=$file target=_blank><img src=images/pdf.png > download</a></td>";
			
		}else{
			$docs.="<td></td>";
		}
		$form.="<td><input type=file name=userfile-$c ></td>";
	}
	print "<div class=photos><b>เอกสาร</b><br><table class=tb4><thead><tr>$th</tr></thead><tbody><tr>$docs <td></td></tr><form action=?action=vdoc-upload&tb=$tb&id=$id method=post enctype=multipart/form-data ><tr>$form </tr></tbody><tfoot><tr><td colspan=4 align=right><input type=submit value=อัพโหลด></td></tr></tfoot></form></table></div>";
}

if($action=='streqitem-add'){
	$dr=qdr("select * from st where part_no='$partno' ");
	if($dr){
		$q="insert into streq_item (streq,st,qty,cost) values ('$id','$dr[id]','$qty','$dr[cost]') ";
		qexe($q);
	}else{
		$msg.= "part no $partno not found ";
	}
	$action='edit';
	
}
if($action=='attach-delete'){
	$fpath=$dir.'/'.$file;
	unlink($fpath);
	$msg.="$file deleted";
	if(!file_exists) $msg.=" success";
	else $msg .=" failed";
	$action='edit';
}
if($action=='attach-upload'){
	$fname=$_FILES[userfile][name];
	$fname=str_replace(' ','_',$fname);
	$fname=str_replace('/','_',$fname);
	
	$file=$dir.'/'.$fname;
	$msg= "uploading  $file ";
	if($_FILES[userfile]){
		//print "tmp file ".$_FILES[userfile][tmp_name];
		$rs=move_uploaded_file($_FILES[userfile][tmp_name],$file);
		if($rs) $msg.= " uploaded";
		else $msg.= " failed";
		if($tb=='form'){
			$q="update $tb set updated=now() where id='$id' ";
			qexe($q);
		}
	}
	$action='edit';
}
if($action=='doc-upload'){
	print "uploading $file ";
	print_r($_FILES);
	if($_FILES[userfile]){
		print "tmp file ".$_FILES[userfile][tmp_name];
		$rs=move_uploaded_file($_FILES[userfile][tmp_name],$file);
		if($rs) print " uploaded";
		else print " failed";
	}
	$action='history';
}
if($action=='doc-delete'){
	unlink($file);
	$action='history';
}
if($action=='photo-delete'){
	unlink($file);
	$action='edit';
}
if($action=='photo-upload'){
	$src=$_FILES[userfile][tmp_name];
	//print "src $src ";
	$info=getimagesize($src);
	//print_r($info);
	$max=640;
	$sw=$info[0];$dw=$sw;
	$sh=$info[1];$dh=$dh;
	
	if($sw>$sh){
		if($sw>$max){
			$dw=$max;
			$dh=$max/$sw*$sh;
		}
	}else{
		if($sh>$max){
			$dh=$max;
			$dw=$max/$sh*$sw;
		}
	}
	$dst=$dir."/".str_replace(" ","_",$_FILES[userfile][name]);
	//print "dst $dst ";
	if($sw<>$dw){
		$dest = imagecreatetruecolor($dw, $dh);
		if($info[mime]=='image/png') $source = imagecreatefrompng($src);
		if($info[mime]=='image/jpeg') $source = imagecreatefromjpeg($src);

		// Resize
		imagecopyresized($dest, $source, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
		$dst=str_replace('png','jpg',$dst);
		imagejpeg($dest,$dst);
		//print "desc $desc dst $dst";
		$msg=" $dst converted and uploaded ";
	}else{
		//print "upload src $src  dst $dst";
		$rs=move_uploaded_file($src,$dst);
		if($rs) $msg ="photo uploaded to $dst ";
		else $msg="failed upload $dst  ";
	}
	
	$action='edit';
	
}
//print "action $action ";
if($action=='item-delete'){
	$q=" delete from $itb where id='$iid' ";
	mysql_query($q); //print $q;
	$action='edit';
}
if($action=='item-update'){
	$q="update $itb set logs=concat(logs, '$now updated by $_SESSION[user]\n') ";
	while(list($fld,$val)=each($_POST)){
		if(in_array($fld,$skipflds)) continue;
		$q .=",$fld='$val' ";
	}
	$q .=" where id='$iid'  ";
	mysql_query($q); //print $q;
	$action='edit';
}
if($action=='item-add'){
	$vars="$tb, logs ";
	$vals="$id, '$now added by $_SESSION[user]\n' ";
	while(list($fld,$val)=each($_POST)){
		if(in_array($fld,$skipflds)) continue;
		$vars .=",$fld ";
		$vals .=", '$val' ";
	}
	$q=" insert into $itb ( $vars ) values ( $vals ) ";
	mysql_query($q); //print $q;
	$action='edit';
}
if($action=='upload'){
		$rs=move_uploaded_file($_FILES[userfile][tmp_name],$file);
		if($rs) $msg .= " successfully ";
		else $msg .= "failed $rs";
		$action=$fromaction;
}
if($action=='addcourse'){
	$q=" insert into training (employee,course,date) values ('$employee','$course','0000-00-00') ";
	mysql_query($q);
	$action='edit';$tb='employee';$id=$employee;
}
if($action=='picture-upload'){
	$fname=$_FILES[userfile][tmp_name];
	$fsize=filesize($_FILES[userfile][tmp_name])/1000;
	$info=getimagesize($fname);
	$w1=$info[0];$h1=$info[1];
	if(($fsize>$picmaxsize)||($w1>$picmax)||($h1>$picmax)){
		$msg .= " > file size $fsize k , width $w1 height $h1 , picture will be resized ";
		if($w1>=$h1){ $w2=$picmax;$h2=floor($w2/$w1*$h1);}
		if($w1<$h1) { $h2=$picmax;$w2=floor($h2/$h1*$w1);}
		$msg.= " > resize from $w1 * $h1 to $w2 x $h2 ";
		$image = new SimpleImage();
		$image->load($fname);
		$image->resize($w2,$h2);
		$image->save($fname);
	}
		$msg .= "uploading ".$_FILES[userfile][tmp_name]." > $file ";
		$rs=move_uploaded_file($_FILES[userfile][tmp_name],$file);
		if($rs) $msg .= " successfully ";
		else $msg .= "failed $rs";
	$deftab='picture';
	$action='edit';
}
if($action=='picture-delete'){
	$msg= " deleteting $file ";
	$rs=unlink($file);
	if($rs) $msg .= " successfully ";
	else $msg .= "failed $rs";
	$deftab='picture';
	$action='edit';
}
if($action=='stockcard-add'){
	if($qty_in>0){ $in_out='in'; $qty=$qty_in;}
	else{ $in_out='out'; $qty=$qty_out;}
	$bal=qval("select balance from stock where id='$stock'");
	$bal2=$bal+$qty_in-$qty_out;
	if($bal2<0) $action='stockcard-error';
}	
if($action=='stockcard-error'){
	$action='edit';$tb='stock';$id=$stock;
	$msg= "Can not Stock under 0 ";
}
if($action=='stockcard-add'){
	
	$q=" insert into stockcard (stock,date,vehicle,description,in_out,qty,record_by,logs) values ('$stock','$date','$vehicle','$description','$in_out','$qty','$_SESSION[user]','$now added by $_SESSION[user]\n') ";
	mysql_query($q); //print $q;
	if($in_out=='out'){
		$q=" insert into sparepart (vehicle,date,parts,qty,cost,note,logs) select $vehicle,'$date',part_name,$qty,cost,'อะไหล่รอใช้','$now auto added by $_SESSION[user]\n' from stock where id='$stock' ";
		mysql_query($q); //print $q;
	}

	$action='edit';$tb='stock';$id=$stock;
}
if($action=='delete'){
	$action='browse';
	if($tb=='plan'){
		$action=$tb;
		$type=qval(" select type from $tb where id='$id' ");
		if($type=='extra') $action='job';
	}

	if($tb=='stockcard') {
		$q=" select stock from stockcard where id='$id' ";
		$ck=mysql_query($q);
		list($stock)=mysql_fetch_array($ck);
	}
	$q=" delete from $tb where id='$id' ";
	mysql_query($q);
	if($tb=='form'){
		unlink("attac/$tb/$id/*");
		unlink("attac/$tb/$id");
	}
	if(in_array($tb,$vehicletbs)) {$action='history';}
	if(in_array($tb,$employeetbs)) {$action='edit';$tb='employee';$id=$employee;}
	if($tb=='fuel') $action='fuel';
	if($tb=='stockcard'){$action='edit';$tb='stock';$id=$stock;}
	if($tb=='trip') $action=$tb;
	if($tb=='max_speed') $action=$tb;
	if($tb=='st'){
		$q="delete from stcard where st='$id' ";
		qexe($q);
		$q="delete from stbal where st='$id' ";
		qexe($q);
		$action='stock-balance';
	}
	if($tb=='12yim'){
		$action=$tb;
	}
}
if(($action=='update')&&(!$id)){
	if(($tb=='ws_busweek')||($tb=='ws_busmonth')){
		$q="delete from $tb where date='$date' and vehicle='$vehicle' ";
		qexe($q);
	}
	$q=" insert into $tb (logs) values ('$_SESSION[user]\n') ";
	if(substr($tb,0,3)=='ws_'){
		$q="delete from $tb where date='0000-00-00' ";
		qexe($q);
		$q=" insert into $tb (logs,opstaff) values ('$_SESSION[user]\n','$_SESSION[user]') ";
	}
	if(in_array($tb,$codecontrol)){
		$q=" insert into $tb (code,logs) values ('$code','$_SESSION[user]\n') ";
	}
	$rs=qexe($q); //print $q;
	if($rs){
		$id=mysql_insert_id();
		if(in_array($tb,$fleettbs)){
			$q=" update $tb set fleet='$_SESSION[fleet]' where id='$id' ";
			qexe($q); print $q;
		}
	}else{
		$msg .=" can not create new record, may be duplicated code, please go back and fix <input type=button value=Back onclick=history.back(); >";
		$action='';
	}
}
if($action=='update'){
	if($tb=='fuel'){
		$q="select milage from $tb where vehicle='$vehicle' and ( date < '$date' or (date='$date' and time< '$time') )  order by date desc,time desc limit 1 ";
		$ck=mysql_query($q); //print $q;	
		list($lm)=mysql_fetch_array($ck);
		print "lm $lm ";
		$_POST[last_milage]=$lm;
		$_POST[total_milage]=$_POST[milage]-$_POST[last_milage];
		$_POST[consumption]=$_POST[total_milage]/$_POST[liter];
	}
	if($tb=='nc'){
		if(($_POST[status]=='report')&&$_POST[report_detail]) $_POST[status]='analyse';
		if(($_POST[status]=='analyse')&&$_POST[analyse_detail]) $_POST[status]='corrective';
		if(($_POST[status]=='corrective')&&$_POST[correctiv_detail]) $_POST[status]='preventive';
		if(($_POST[status]=='preventive')&&$_POST[preventive_detail]) $_POST[status]='follow1';
		if(($_POST[status]=='follow1')&&$_POST[follow1_detail]) $_POST[status]='follow2';
		if(($_POST[status]=='follow2')&&$_POST[follow2_detail]) $_POST[status]='complete';
	}
	$q=" update $tb set logs=concat(logs,'$now updated by $_SESSION[user]\n') ";
	while(list($var,$val)=each($_POST)){
		if($var=='plan_by') $val=$_SESSION[user];
		if(substr($var,0,5)=='item-') continue;
		if(($tb=='ws_talk')&&($var=='driver'))continue;
		if($var=='userfile') continue;
		if(($var=='date')&&($val=='')) continue;
		$val=addslashes($val);
		$q .=" , $var='$val' ";
		if(in_array($var,$sessflds)) $_SESSION[$var]=$val;
	}
	$q .=" where id='$id' ";
	$rs=qexe($q); //print $q;
	if(substr($tb,0,6)=='ws_bus'){
		$type=substr($tb,6,1);
		$q="select id from ws_busmaster where type='$type' ";
		$ck=mysql_query($q); //print $q;
		$tbitem=$tb.'item';
		while(list($bm)=mysql_fetch_array($ck)){
			$result=$_POST['item-result-'.$bm];
			$comment=$_POST['item-comment-'.$bm];
			$q="insert into $tbitem 
			($tb,ws_busmaster,result,comment) values
			($id,'$bm','$result','$comment' )
			
			";
			mysql_query($q); //print $q.'<br>';
		}
	}
	if($tb=='ws_talk'){
		$tbitem=$tb.'item';
		$q=" update $tbitem set $tb='$id',logs=concat(now(),' added by $_SESSION[user]\n') where $tb=0 and logs='$sid' ";
		mysql_query($q);
	}
	if($tb=='workorder'){
		$jid=qval("select id from mtn where workorder='$id' ");
		if(!$jid){
			$q=" insert into mtn (workorder,request_date,vehicle) values ('$id','$request_date', '$vehicle') ";
			mysql_query($q);
			$jid=mysql_insert_id();
		}
		$q=" update mtn set request_description='$description',request_date='$request_date', logs=concat(logs,now(),' updated by $_SESSION[user]\n') where workorder='$id' ";
		mysql_query($q); print $q;
	}
	if($tb=='mtn'){
		$workorder=qval("select workorder from $tb where id='$id' ");
		$st=$_POST[status];
		if($st=='plan') $st='progress';
		if($st=='done') $st='job done';
		$q=" update workorder set status='$st',logs=concat(logs,now(),' $st by $_SESSION[user]\n') where id='$workorder' ";
		mysql_query($q); //print $q;
	}
	$action='browse';
	if(in_array($tb,$vehicletbs)) {$action='history';$deftab=$tb;}
	if(in_array($tb,$employeetbs)) {$action='edit';$id=$employee;$deftab=$tb;$tb='employee';}
	if($tb=='fuel'){
		 $action='fuel';
	}
	if($tb=='stockcard'){
		$action='edit';$tb='stock';$id=$stock;
	}
	if($tb=='trip') $action=$tb;
	if($tb=='plan'){
		$type=qval("select type from $tb where id='$id' ");
		if($type=='extra'){
			$mo=qval("select date_format(date,'%m') from $tb where id='$id' ");
			$yr=qval("select date_format(date,'%Y') from $tb where id='$id' ");
			$action='job';
		}else{
			$action=$tb;
		}
	}
	
	if($tb=='mtn'){
		$wo=qval("select workorder from mtn where id='$id' ");
		if($status=='new') $wstatus='new';
		if($status=='plan') $wstatus='progress';
		if($status=='done') $wstatus='job done';
		if($status=='complete') $wstatus='complete';
		
		$q=" update workorder set status='$wstatus' where id='$wo' ";
		mysql_query($q);
		$action="mtn-plan";
	}
	if($tb=='job') $action=$tb;
	if(($tb=='employee')&&($type=='office')) $action=$tb;
	if(($tb=='employee')&&($type=='driver')) $action='drivers';
	if($tb=='ta'){ $action='report';$report='ta4';}
	//print_r($_FILES);
	if($_FILES){
		if($tb=='form'){
			$dir="attach/$tb/$id";
			if(!file_exists($dir)) mkdir($dir);
			$tfile=$dir."/".$_FILES[userfile][name];
			$rs=move_uploaded_file($_FILES[userfile][tmp_name],$tfile);
			print "upload $tfile ";
			if($rs) print " ok  <br>";
			else print " failed <br>";
			$q="update $tb set updated=now() where id='$id' ";
			qexe($q);
		}else{
			$dir="images/$tb";
			if(!file_exists($dir)) mkdir($dir);
			$img="images/$tb/$id.jpg";
			//print " uploading $_FILES[userfile][tmp_name] -> $img ";
			$rs=move_uploaded_file($_FILES[userfile][tmp_name],$img);
			if($rs) print " . ";
			else print " x ";
		}
	}
	if(substr($tb,0,3)=='ws_'){
		$msg= "บันทึกเรียบร้อย $id "; 
		$action='ws';
		$ws=str_replace('ws_','',$tb);
		$id='';
		//print "<pre>";print_r($_SESSION);print "</pre>";
		unset($_SESSION[customer]);
		
		
	}
	if($tb=='st') $action='stock-list';
	
	if($tb=='streq'){
		$type=qval("select type from $tb where id='$id' ");
		$action='stock-'.$type;
	} 
	if($tb=='stcard'){
		$q="update $tb set amount=(in_qty-out_qty)*cost where id ='$id' ";
		qexe($q);
		$st=qval("select st from stcard where id='$id' ");
		$action='stock-card';
		$id=$st;
		$msg.=" stock card udpated";		
	}
	if(($tb=='suggest')&&($_SESSION[utype]=='customer')){
		
	}
	print $_SESSION[customer];
	//print $q;
}
function driveroptions($contract,$val){
	global $tb;
	
	if($contract==1) $cond="  and type='cdriver' ";
	elseif($contract==2) $cond=" and (type='cdriver' or type='driver') ";
	else $cond=" and type='driver' ";
	$q=" select id,name from employee where resign=0 and fleet='$_SESSION[fleet]' and type in ('driver','cdriver') order by name ";
	
	
	if($_SESSION[customer]&&(substr($tb,0,3)=='ws_')) $q=" select t1.id,t1.name from vehicle as t0,employee as t1 where t1.id=t0.driver and t1.resign=0 and t1.fleet='$_SESSION[fleet]' and t1.type='driver' and t0.customer='$_SESSION[customer]' order by t1.name ";
	$ck=mysql_query($q); //print $q;
	$out .="<option c=$contract q=\"$q\">";
	while(list($i,$t)=mysql_fetch_array($ck)){
		if($i==$val) $out .="<option value=$i selected >$t ";
		else $out .="<option value=$i >$t ";
	}
	//print $q;
	return $out;
	
}
function datenav(){
	global $date,$action,$report,$tb;
	if($action=='report') $action="report&report=$report";
	if(!$date) $date=date("Y-m-d");
	$q="select '$date'-interval 1 day,'$date'+interval 1 day,date_format('$date','%a') ";
	$ck=mysql_query($q);// print $q;
	list($pdate,$ndate,$W)=mysql_fetch_array($ck);
	print "<form action=?action=$action&tb=$tb method=post > Date 
	<a href=?action=$action&date=$pdate&tb=$tb class=btn><</a>
	$W<input type=text name=date id=date class=date size=10 onchange=this.form.submit(); value=$date>
	<a href=?action=$action&date=$ndate&tb=$tb class=btn>></a>
	
	</form><script>
	$('#date').datepicker({dateFormat:'yy-mm-dd'});
	</script>";
}
function formdoc($tb,$id){
	if(!$id)return;
	$dir="attach/$tb/$id";
	if(!file_exists($dir)) mkdir($dir);
	$dh=opendir($dir);
	while(($f=readdir($dh))&&(!$fl)){
		if($f=='.')continue;
		if($f=='..')continue;
		$fl=$f;
	}
	if($fl) $out="<a href=dl.php?dir=$dir&file=$fl&filename=$fl>Download</a> ";
	if(in_array($_SESSION[udept],array('dcc','admin')))  $out.=" | <a href=?action=edit&tb=$tb&id=$id>Upload</a>
	| <a href=?action=edit&tb=$tb&id=$id >edit</a>
	| <a href=?action=delete&tb=$tb&id=$id onclick=\"return Confirm('Confirm Delete?);\">delete</a>
	";
	
	return $out;
}
function browse($q,$tid){
	global $controlflds, $tbflds,$tbname,$tbcode,$tbno, $toaction, $hiddenflds, $skipbrowse,$tb,$customer,$baterry_life, $csv, $strflds,$type;
	
	$ck=mysql_query($q); //print $q;
	if(!$ck) print $q;
	//print $q;
	if($tb=='training'){
		$hiddenflds=array('fleet','oemp');
	}
	if($tb=='breakdown'){
		$hiddenflds=array();
		//print $q;
	}
	if($tb=='battery') {
		
	}
//	if($tb=='vehicle') print "<a href=?action=new&tb=$tb>Add new $tb </a>";
	if(($tb=='vehicle')&&($_SESSION[udept]=='operation')){
		$hiddenflds=array('driver','class1_3','register','prb','installment');
	}
	if(($tb=='trip')&&($_SESSION[udept]=='operation')){
		$hiddenflds=array('price','cost','allowance');
	}
	if($tb=='max_speed'){
		$hiddenflds=array('logs'); $toaction="edit&tb=$tb";
	}
	if($tb=='vehicle'){
		$hiddenflds=array('logs','drivers'); 
	}
	if($tb=='streq'){
		if($type=='request') $hiddenflds=array('to_fleet');
		if($type=='transfer') $hiddenflds=array('vehicle');
		if($type=='receive') $hiddenflds=array('to_fleet','vehicle');
	}
	print "<table id=$tid class='tb1 dt'><thead><tr>";
	for($j=1;$j< mysql_num_fields($ck);$j++){
		$fld=mysql_field_name($ck,$j);
		$ftype=mysql_field_type($ck,$j);
		if(in_array($fld,$controlflds)) continue;
		if(in_array($fld,$hiddenflds)) continue;
		if(in_array($fld,$skipbrowse)) continue;
		$add="";
		if($fld=='date') $add="width=100";
		if($fld=='milage') $add="width=100";
		if(($tid=='tbfuel')&&($fld=='note')) $add="width=350";
		print "<th $add class='$fld ' >".flddict($fld)."</th>";
		$csv.=",".flddict($fld);
	}
	$csv.="\n";
	if($tb=='form') print "<th>ดาวน์โหลด</th>";
	print "</tr></thead><tbody>";
	$next90=date("Y-m-d",mktime(0,0,0,date('m'),date('d')+90,date('Y')));
	$next30=date("Y-m-d",mktime(0,0,0,date('m'),date('d')+30,date('Y')));
	for($i=0;$i< mysql_num_rows($ck);$i++){
		$id=mysql_result($ck,$i,0);
		$onclick='';
		
		if($_SESSION[uauth][edit]) $onclick="window.location.href='?action=$toaction&id=$id';";
		if($_SESSION[uauth][view]) $onclick="window.location.href='?action=$toaction&id=$id';";
		if($tb=='form') $onclick='';
		
		if($tb=='stock'){
			$addtoname="";
			if($bal<$min) $addtoname="style=color:red";
		}
		$trclass='bd';
		for($j=1;$j< mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			if($fld=='trclass'){
				$trclass=mysql_result($ck,$i,$j);
			}
		}
		
		print "<tr class='$trclass'  onclick=$onclick>";
		for($j=1;$j< mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			if(in_array($fld,$controlflds)) continue;
			if(in_array($fld,$hiddenflds)) continue;
			if(in_array($fld,$skipbrowse)) continue;
			$ftype=mysql_field_type($ck,$j);
			$flen=mysql_field_len($ck,$j);
			$fflags=mysql_field_flags($ck,$j);
			$fflags=trim(str_replace("not_null","",$fflags));
			$val=mysql_result($ck,$i,$j);
			${$fld}=$val;
			if(in_array($fld,$tbflds)){
				if(in_array($fld,$tbcode)) $val=tbcode($fld,$val);
				if(in_array($fld,$tbname)) $val=tbval($fld,"name",$val);
				if(in_array($fld,$tbno)) $val=tbval($fld,"no",$val);
				if($fld=='to_fleet') $val=tbval('fleet','name',$val);
				$ftype='string';
			}
			if($fld=='driver'){ $val=tbval('employee','name',$val); $ftype='string';}
			if($ftype=='time') $val=substr($val,0,5);

			if($ftype=='blob') $val=nl2br($val);
			if(($ftype=='date')&&($val=='0000-00-00')) $val='';
			//if(($ftype=='time')&&($val=='00:00')) $val='';

			$add21='';$add22='';$aclass='';
			if($ftype=='date') {$add21="<nobr>"; $add22="</nobr>";}
			$add3='';
			if(($tb=='stock')&&($fld=='part_name')){
				$bal=mysql_result($ck,$i,'balance');
				$min=mysql_result($ck,$i,'min_stock');
				if($bal<=$min){ $add21="<font color=red>";$add22="</font>";}
			}
			if(($tid=='tbsparepart')&&($fld=='parts')){
				$note=mysql_result($ck,$i,'note');
				if($note=='อะไหล่รอใช้'){ $add21="<font color=red>";$add22="</font>";}
			}
			if(($tb=='max_speed')&&($fld=='max_speed')){
				$cspeed=tbval('vehicle','control_speed',$vehicle);
				if($val>$cspeed) { $add21="<font color=red>";$add22="</font>";}
			}
			if( ($tb=='employee') && ($fld=='license_expire_date') &&($val<=$next90) )  $aclss="alert";
			if( (strpos($fld,'_expire')) && ($val<=$next30) ) $aclass='alert';
			if($fflags=='set') $val=flddict($val);
			if($ftype=='int'){
				if(in_array($fld,$strflds)) $ftype='string';
			}
			if(($ftype=='int')&&($val==0)){ $val=''; $ftype=''; }
			if($fld=='อายุงาน'){
				$ftype='string';
				$yr=$val/365;
				$yr=floor($val/365);
				$mo=floor(($val-$yr*365)/30);
				
				$dd=$val-$yr*365-$mo*30;
				$val=$yr.'ปี '.$mo.'เดือน '.$dd.'วัน';
			}
			//if(($tb=='streq')&&($fld=='type')) $val=flddict($val);
			if(($tb=='form')&&($type!='form')&&($fld=='name')&&(mysql_result($ck,$i,'is_new')==1)){
				$add22='<img src=images/new02.gif>';
			}
			print "<td class='$ftype $aclass' $add3>$add21 $val $add22 </td>";
			$csv.=',"'.$val."\"";
		}
		if($tb=='form'){
			print "<td>".formdoc($tb,$id)."</td>";
		}
		print "</tr>";
		$csv.="\n";
		
	}
	print "</tbody></table>";
	print "<script>
$(document).ready(function() {
	$('#$tid').dataTable({bPaginate: true
, bStateSave: true
, bLengthChange:true
, iDisplayLength:100
, aaSorting: [[0,'desc']]
, sPaginationType:'full_numbers'
, oLanguage:{
	sLengthMenu:'_MENU_ /page',
	sZeroRecords:'not found',
	sInfo:'showing _START_ - _END_ / _TOTAL_ ',
	sInfoEmpty:'empty',
	sInfoFiltered:'(filtered from _MAX_ items )',
	oPaginate: {
		sFirst: '|< ',
		sLast : ' >|',
		sNext : ' > ',
		sPrevious: '< '
	}}
});
});
</script>";
} 
function recal($stock){
	$q=" select sum(if(in_out='in',qty,-qty)) from stockcard where stock='$stock' group by stock ";
	$ck=mysql_query($q); //print $q;
	list($bal)=mysql_fetch_array($ck);
	$q=" update stock set balance='$bal' where id='$stock' ";
	mysql_query($q);  //print $q;
}
function array_clip($ar,$val){
	while(list($i,$v)=each($ar)){
		if($v==$val) unset($ar[$i]);
	}
	return $ar;
}
function tbprint($tb,$id){
	global $controlflds,$hiddenflds,$tbflds,$tbcode,$itemtbs;
	$q="select * from $tb where id='$id' ";
	$ck=mysql_query($q);
	
	$cols=2;
	$allcols=$cols*2;
	$col=1;
	$doctitle=$tb;
	$type=mysql_result($ck,0,'type');
	if($tb=='streq') $doctitle=$type;
	print "<span class=noprint onclick=window.print();> print to printer </span>
	<table class=tbdoc><tbody><tr><td colspan=$allcols>
	<img src=../images/logo450x240.jpg align=left width=100>
	<h1 style=text-align:right;font-size:24pt;>".flddict($doctitle)."</h1>
	</td></tr><tr>";
	if($tb=='streq'){
		if($type=='transfer') $hiddenflds=array('type','vehicle');
		if($type=='request') $hiddenflds=array('type','to_fleet');
		if($type=='receive') $hiddenflds=array('type','to_fleet','vehicle');
	}
	for($j=1;$j< mysql_num_fields($ck);$j++){
		$fld=mysql_field_name($ck,$j);
		if(in_array($fld,$controlflds)) continue;
		if(in_array($fld,$hiddenflds)) continue;
		$ftype=mysql_field_type($ck,$j);
		$flen=mysql_field_len($ck,$j);
		$fflags=mysql_field_flags($ck,$j);
		$fflags=trim(str_replace("not_null","",$fflags));
		$val=mysql_result($ck,$i,$j);
		$dr[$fld]=$val;
		if(in_array($fld,$tbflds)){
			$rtb=$fld;$rfld='name';
			if($fld=='to_fleet')$rtb='fleet';
			if(in_array($fld,$tbcode)) $rfld='code';
			$val=qval("select $rfld from $rtb where id='$val' ");
		}
		print "<td width=10% class=fld>".flddict($fld)."</td><td width=40% class=val>".$val."</td>";
		if($col==$cols){ print "</tr><tr>";$col=1;}
		else $col++;
	}
	print "<tr><td colspan=$allcols>";
	if(in_array($tb,$itemtbs)){
		$itb=$tb.'_item';		
		itemprint($tb,$id,$itb);
	}
	$ptitle=flddict($tb).':'.$dr[book_no].'/'.$dr[number].'-'.$id;
	print "</td></tr>
	<tr>
	<td colspan=$cols><br><br><br>__________________________________<br>ผู้เบิก</td>
	<td colspan=$cols><br><br><br>__________________________________<br>ผู้อนุมัติ</td>
	</tr>
	<tr>
	<td colspan=$cols><br><br><br>__________________________________<br>ผู้ตรวจสอบ</td>
	<td colspan=$cols><br><br><br>__________________________________<br>ผู้บันทึก</td>
	</tr>
	</table><script>
	document.title='$ptitle';
	</script>";
	
}
function itemprint($tb,$id,$itb){
	global $controlflds,$hiddenflds,$tbflds,$tbcode,$itemtbs,$sumflds;
	$q="select * from $itb where $tb='$id' ";
	$ck=mysql_query($q);
	for($i=0;$i< mysql_num_rows($ck);$i++){
		$k=$i+1;
		$tbody.="<tr><td>$k</td>";
		for($j=1;$j< mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			if($fld==$tb)continue;
			if(in_array($fld,$controlflds)) continue;
			if(in_array($fld,$hiddenflds)) continue;
			$ftype=mysql_field_type($ck,$j);
			if($i==0){
				$thead.="<td>".flddict($fld)."</td>";
			}
			$val=mysql_result($ck,$i,$j);
			if(in_array($fld,$sumflds))$sum[$fld]+=$val;
			if(in_array($fld,$tbflds)){
				$rtb=$fld;
				if($fld=='to_fleet')$rtb='fleet';
				$val=qval("select name from $rtb where id='$val' ");
				$ftype='string';
			}
		
			$tbody.="<td class='$ftype'>$val</td>";
		}
		$tbody.="</tr>";
	}
	$maxlines=15;
	for($i=mysql_num_rows($ck);$i<$maxlines;$i++){
		$tbody.="<tr><td></td>";
		for($j=1;$j< mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			if($fld==$tb)continue;
			if(in_array($fld,$controlflds)) continue;
			if(in_array($fld,$hiddenflds)) continue;
			$tbody.="<td>&nbsp;</td>";
		}
		$tbody.="</tr>";
	}
	$tfoot="<tr><td></td>";
	for($j=1;$j< mysql_num_fields($ck);$j++){
		for($j=1;$j< mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			if($fld==$tb)continue;
			if(in_array($fld,$controlflds)) continue;
			if(in_array($fld,$hiddenflds)) continue;
			$ftype=mysql_field_type($ck,$j);
			$val='';$ftype='string';
			if(in_array($fld,$sumflds)){ $val=$sum[$fld];$ftype='int';}
			$tfoot.="<td class='$ftype'>$val</td>";
		}
	}
	$tfoot.="<tr>";
	print "<table class=itemdoc><thead><tr><td>No</td>$thead</tr></thead><tbody>$tbody</tbody><tfoot>$tfoot</tfoot></table>";
}
function edit($tb,$id,$vo){
	global $controlflds,$hiddenflds, $tbflds, $tbcode,$tbcodefleet,$tbname,$tbno, $toaction, $vehicletbs, $vehicle, $calflds,$employee,$employeeflds,$type,$phototbs,$fleettbs,$fchangtbs,$rqflds,$attachtbs,$employeetbs, $printtbs;
	
	if($tb=='streq') $type=qval("select type from $tb where id='$id' ");
	if(in_array($tb,$fchangtbs))	$controlflds=array_clip($controlflds,'fleet');
	
	$passresult=array('ไม่ผ่าน','ผ่าน');
	if(substr($tb,0,3)=='ws_'){
		$hiddenflds=array();
	}
	if($tb=='mtn'){
		$aHeader=qval("select t2.code from $tb as t1,vehicle as t2 where t2.id=t1.vehicle and t1.id='$id' ");
		
	}
	if($tb=='plan'){
	global $customer;
		$type=qval("select type from $tb where id='$id' ");
		$q="select customer from $tb where id='$id' ";
		$customer=qval($q); //print $q;
		
		//$_SESSION[customer]=$customer;
		
		if($type=='extra'){
		 $hiddenflds=array('trip','route','in_out','max_speed','type');
		}else{
		 $hiddenflds=array('in_out','trip','type','customer_name','description','price','cost','status','max_speed','plan_start');
		}
	}
	if(($tb=='vehicle')&&($_SESSION[udept]=='operation')){
		$hiddenflds=array('class1_3','register','prb','installment');
	}
	if(($tb=='trip')&&($_SESSION[udept]=='operation')){
		$hiddenflds=array('price','cost','allowance');
	}
	if(in_array($tb,$employeetbs)) $hiddenflds=array('employee');
	//if($tb=='training') $hiddenflds=array('employee');
//	print "tb $tb type $type";
	if($tb=='streq'){
		print "type $type ";
		if($type=='request') $hiddenflds=array('to_fleet');
		if($type=='transfer') $hiddenflds=array('vehicle');
		if($type=='receive') $hiddenflds=array('to_fleet','vehicle');
	} 
	if($tb=='nc'){
		$hiddenflds=array('subject');
		array_push($controlflds,'follow1_closed','follow2_closed');
	} 
	if($tb=='stock') recal($id);
	$q="select * from $tb where id='$id' ";
	$ck=mysql_query($q);
	if($tb=='employee'){
		$resign=mysql_result($ck,0,'resign');
		if($resign==0) array_push($controlflds,'resign_date','resign_interview');
		$type=mysql_result($ck,0,'type');
		if($type=='office')array_push($controlflds,'license_detail','license_expire_date');
	}
	$col=1;$cols=2;
	$allcols=$cols*2;
	$checked=array('','checked');
	$unchecked=array('checked','');
	if($_SESSION[udept]=='admin') $calflds=array();
	print "<table id=tb2 class=tb2><thead><tr><th colspan=$allcols> - ".flddict($tb)." - $id | $aHeader </th></tr></thead><tbody>
<tr ><td colspan=$allcols align=right>";
	if(in_array($tb,$printtbs)) print "<a href=?action=print&tb=$tb&id=$id> Print </a> ";
	$date=qval("select date from $tb where id='$id' ");
	$candel=0;
	if(in_array($_SESSION[ulevel],array('manager','md','admin'))) $candel=1;
	if($candel==1) print "| <a class='delete' href=?action=delete&tb=$tb&id=$id&vehicle=$vehicle&employee=$employee&date=$date onclick=\"return confirm('confirm delete?')\">Delete</a> ";
	if($tb=='nc') print "| <a href=?action=printform&tb=$tb&id=$id > Print Form </a> ";
	print " | </td></tr>
<form action=?action=update&tb=$tb&id=$id method=post name=form1 enctype=multipart/form-data ><tr>";
	if($vo==1) $add="disabled";
	$add='';
	if($tb=='accident') $hiddenflds=array();
	
	for($j=1;$j< mysql_num_fields($ck);$j++){
		$add='';
		$fld=mysql_field_name($ck,$j);
		if(in_array($fld,$controlflds)) continue;
		$ftype=mysql_field_type($ck,$j);
		$flen=mysql_field_len($ck,$j);
		$fflags=mysql_field_flags($ck,$j);
		$fflags=trim(str_replace("not_null","",$fflags));
		$val=mysql_result($ck,$i,$j);
		if(!$id){
			$val=$_SESSION[$fld];
		}
		if(in_array($fld,$hiddenflds)){ print "<input type=hidden name=$fld id=$fld value=$val>"; continue;}
		if(in_array($fld,$calflds)) $add .=" readonly ";
		
		if($ftype=='time') $val=substr($val,0,5);
		if(($ftype=='date')&&($val=='0000-00-00')) $val='';
		if($fld=='level') $add.=' disabled';
		$iclass='';
		if(in_array($fld,$rqflds)){
			$iclass.=" required ";
		}
		if($tb=='nc'){
			if(substr($fld,0,4)=='dcc_') $iclass.=" dcc";
		}
		if($fld=='status'){
			$status=$val;
			$curkey=array_search($status,$stlist);
		} 
		if($tb=='nc'){
			$stlist=array('report','analyse','corrective','preventive','follow1','follow2');
			$add='';
			while(list($stkey,$st)=each($stlist)){
				$fldpf=$st.'_';
				if($st==$status){
						//$add.=' required ';
						continue;
				}else{ 
			
					
					if(substr($fld,0,strlen($fldpf))==$fldpf){
						if($stkey>$curkey) $add=" disabled ";
						else $add='required';
						continue;
					} 
				
				}
			}
		}
		
		$input="<input type=text class='$ftype $iclass ' id=$fld name=$fld value=\"$val\" size=40 $add>"; 
		if(in_array($fld,$tbflds)){
			$ftype='';
			if(in_array($fld,$tbcode)) $input=tboptions($fld,$val);
			if(in_array($fld,$tbname)) $input="<select name=$fld id=$fld >".qoptions("select id,name from $fld order by name ",$val)."</select>";
			if(in_array($fld,$tbno)) $input="<select name=$fld id='$fld'  >".tboptions2($fld,$val)."</select>";
			if(in_array($fld,$fleettbs)) $input="<select name=$fld id='$fld'  ><option>".qoptions("select id,code from $fld  where fleet='$_SESSION[fleet]' and code<>'' order by code ",$val)."</select> <span style=cursor:hand; onclick=alllist('$fld','$val');> List All<span>";
		}
		if(($tb=='nc') && strpos($fld,'_dept')){
			$input="<select name=$fld ><option value=''>".qoptions("select name,name from nc_dept order by rank ",$val)."</select>";
		}
		if($fld=='driver'){
			$ftype='';
			if($tb=='vehicle') $contract=tbval('vehicle','contract',$id);
			if($tb=='plan') $contract=2;//tbval('vehicle','contract',mysql_result($ck,$i,'vehicle'));
			if($tb=='accident') $contract=tbval('vehicle','contract',mysql_result($ck,$i,'vehicle'));
			if(substr($tb,0,3)=='ws_') $contract=2;
			
			$input="<select name=$fld id=$fld>".driveroptions($contract,$val)."</select> <span onclick=alllist('$fld','$val');>List All</span>";
		}
		if($fld=='supervisor') $input="<select name=$fld id=$fld><option value=''>".qoptions("select name,name from employee where type='office' and name<>'' and resign=0  order by name ",$val)."</select> <span onclick=alllist('$fld','$val');>List All</span>";
		if($ftype=='blob') $input="<textarea name=$fld $id=fld cols=40 rows=5 $add >$val</textarea>";
		if($fflags=='set') $input=tbsets($tb,$fld,$val,$vo);
		if(($ftype=='int')&&($flen==1)){
			if(($tb=='vehicle')&&($fld=='contract')) $add2="onclick=driverlist('',this.checked);";
			$input="<input type=checkbox name=$fld value=1 $checked[$val] $add2 >";
			if($val==1) $input="<input type=radio name=$fld value=1  $add2 checked>Yes / <input type=radio name=$fld value=0  $add2 >No";
			else  $input="<input type=radio name=$fld value=1  $add2 >Yes / <input type=radio name=$fld value=0  $add2 checked>No";
			
		}
		//if(($ftype=='date')&&($fld!='request_date')) $addjs .="$('#$fld').datepicker({dateFormat:'yy-mm-dd'});";
		if($ftype=='datetime') $addjs .="$('#$fld').datetimepicker({dateFormat:'yy-mm-dd',timeFormat:'hh:mm'});";
		if($ftype=='time'){
			
			 $addjs .="$('#$fld').timepicker({timeFormat:'hh:mm'});";
		}
		$addclass='';
		$fld1=$fld;
		if( ($tb=='mtn')&& strpos($fld,'_')){
			$pref=substr($fld,0,strpos($fld,'_'));
			$suff=substr($fld,strpos($fld,'_')+1,strlen($fld));
			$fld1=$suff;
		}
		if($fld=='result'){
			//if(!$val){ $checked=array();$unchecked=array();}
			$input="<input type=radio name=$fld $checked[$val]  value=1 onclick=$('#level').val('').attr('disabled','disabled');>ผ่าน / <input type=radio name=$fld $unchecked[$val]  value=0  onclick=$('#level').removeAttr('disabled').focus(); >ไม่ผ่าน  ";
			if($vo) $input=$passresult[$val];
		}
		if($vo==1){
			if($fld=='driver')$input=tbval('employee','name',$val);
			if($fld=='customer')$input=tbval($fld,'code',$val);
			
		}
		if($fld=='to_fleet'){
			$input="<select name=$fld><option>".qoptions("select id,name from fleet ",$val)."</select>";
		}
		if(in_array($fld,$rqflds)){
			$input.="<font color=red>*</font>";
		}
		//if(($tb=='streq')&&($fld=='status')&&($val!='complete')) $input.="<input type=button value=Complete onclick=\"if(confirm('Confirm Complete?')){window.location.href='?action=stock-request-complete&id=$id';\"} >";
		$cspan=1;
		$cwidth='35%';
		if(($tb=='nc')&&($fld=='status'||strpos($fld,'_detail'))){
			$cspan=3;
			$cwidth='85%';
			$col++;
			if(strpos($fld,'_detail')){
				$input="<textarea type=text name=$fld id=$fld cols=100 rows=2 $add >$val</textarea>";
				
			}
		}
		$showfld=1;
		if(($tb=='nc')&&($fld=='type')) $showfld=0;
		if(in_array($fld,array('follow1_date','follow2_date'))){
			$fld2=substr($fld,0,8).'closed';
			$chk0=array('checked','');
			$chk1=array('','checked');
			$v=qval("select $fld2 from $tb where id='$id' ");
			$input .=" สถานะ <input type=radio name=$fld2 $chk1[$v] value=1> ปิด <input type=radio name=$fld2 value=0 $chk0[$v] > ยังไม่ปิด";
		}
		if($showfld) print "<td width=15% class='$tb-$pref $addclass'  $addattr >".flddict($fld)."</td><td colspan=$cspan width=$cwidth class='$tb-$pref $addclass' >$input</td>";
		else  print "<td colspan=2 width=$cwidth class='$tb-$pref $addclass' >$input</td>";
		
		if($col==$cols){ print "</tr><tr>"; $col=1; }
		else {$col++;}
	}
	print "</tr>";
	
	if(substr($tb,0,6)=='ws_bus'){
		print "<tr><td colspan=$allcols>";
		wsbus_item($tb,$id,$vo);
		print "</td></tr>";
	}
	if($tb=='ws_talk'){
		print "<tr><td colspan=$allcols>";
		if(!$vo){
			
			wstalk_item($tb);

		}else{
			$tbitem=$tb.'item';
			
			print "<table class=tb2><thead><tr><td>นับขับที่เข้าร่วม</td></tr></thead><tbody>";
			$q="select t2.name from $tbitem as t1,employee as t2 where t2.id=t1.driver and t1.$tb='$id' ";
			$ck2=mysql_query($q); 
			while(list($dname)=mysql_fetch_array($ck2)){
				print "<tr><td>$dname</td></tr>";
			}
			print "</tbody></table>";
		}
		print "</td></tr>";
	}
	//	$alist=array('edit','workorder','history','milage');
//	while(list(,$a)=each($alist)) $links.="<a href=?action=$a&tb=$tb&id=$id>$a</a> ";
	
//	if($vo) $btn="<input type=button value=Close onclick=window.location.href='?action=history';>";
	$btn="<input type=submit value=".flddict("update"). " >";
	if(($tb=='ws_alcohol')||($tb=='ws_drug')||($tb=='ws_talk')){
		$addjs.="$('#customer').change(function(){
			url='ajax.php?action=driverlist&customer='+$(this).val();
			console.log('url:'+url);
			$('#driver').load(url);
			
		}).change();";
	}
	if(in_array($tb,$phototbs)){
		print "<tr><td colspan=$allcols>photo";
		tbphoto($tb,$id);
		print "</td></tr>";
	}
	if(in_array($tb,$doctbs)) {
		print "<tr><td colspan=$allcols>photo";
		tbdoc($tb,$id);	
		print "</td></tr>";
	}
	if($tb=='form'){
		print "<tr><td colspan=$allcols>Upload file <input type=file name=userfile></td></tr>";
	}
	if($tb=='plan') $btn ='<input type=button value=back onclick=history.back() >'.$btn;
	else $btn ="<input type=button value=Close onclick=window.location.href='?action=$tb'; style=float:left; > ".$btn;
	if($tb=='streq'){
		$status=qval("select status from $tb where id='$id' ");
		if($status<>'complete') $btn ="<a href=?action=stock-request-complete&id=$id onclick=\"return confirm('Confirm Complete? ตัดสต๊อค ?');\"><input type=button value=ตัดสต๊อค-เข้า/ออก></a>".$btn;
	}
	
	print "<tr><td colspan=$allcols align=right>$btn</td></tr>";
	if($vo==1){}
	$logs=qval("select logs from $tb where id='$id' ");
	print "<tr><td colspan=$allcols align=left style=color:#666;><span onclick=$('.logs').toggle(); >Logs </span><div class=logs style=display:none;>".nl2br($logs)."</div><a href=# onclick=history.back(); style=color:blue;>&lt;&lt; Back </a></td></tr>";
	
	print "</tbody><tfoot></tfoot></form></table>$msg
<script>
function addnote(t){
	form1.note.value+=t;
	form1.note.value+='\\n';
}
</script>
	";
	if($tb=='workorder'){
		$ck=mysql_query("select id,name from woitem ");
		while(list($i,$t)=mysql_fetch_array($ck)){
			print "<div class=woitem onclick=\"addnote('$t');\" >$t</div>";
		}
		print "<script></script>";
	}
	global $vo;
	if($tb=='stock') stockcard($id);
	if($tb=='employee') employee($id);
	if($tb=='breakdown') items('prevention',$tb,$id);
	if($tb=='accident') items('ac_prevention',$tb,$id);
	if($tb=='suggest') items('suggest_action',$tb,$id);
	if($tb=='streq') items('streq_item',$tb,$id);
	if($tb=='mtn') photo($tb,$id);
	if($tb=='vehicle') photo($tb,$id);
	if(in_array($tb,$attachtbs)) attach($tb,$id);
	if($tb=='vehicle') vdoc($tb,$id);
	if($vo==1){
		print "<script>
		$('input[type=text],select,textarea').attr('disabled','disabled');
		$('input[type=submit]').hide();
		</script>";
	}
	if(($tb=='suggest')&&($_SESSION[cid])){
		print "<script>
		$('select').attr('disabled','disabled');
		</script>";
	}
	if($tb=='nc'){
		//print_r($_SESSION);
		if(!in_array($_SESSION[udept],array('dcc'))){
			print "<script>
		$('.dcc').attr('disabled','disabled');
		</script>";
		}
	}
	
	
}
function attach($tb,$id){
	if(!$id) return;
	$isupload=1;
	if($tb=='form'){
		$isupload=0;
		if($_SESSION[udept]=='dcc') $isupload=1;
		if($_SESSION[udept]=='admin') $isupload=1;
		
	} 
	$dir="attach/$tb";
	if(!file_exists($dir)) mkdir($dir);
	$dir="attach/$tb/$id";
	if(!file_exists($dir)) mkdir($dir);
	$dh=opendir($dir);
	print "<div class=attach style=><h2>Attachment</h2><table class=tb4 cellspacing=1 cellpadding=5><thead><tr><td>ไฟล์</td><td></td></tr></thead><tbody>";
	while($file=readdir($dh)){
		if($file=='.') continue;
		if($file=='..') continue;
		$floc=$dir.'/'.$file;
		$floc2=urlencode($floc);
		$file2=urlencode($file);
	
		print "<tr><td> $file </td><td><a target=_blank href=$dir/$file>download</a>";
		if($isupload) print "| <a href=?action=attach-delete&file=$file2&dir=$dir&tb=$tb&id=$id&dir=$dir onclick=\"return confirm('Confirm Delete file $file ?');\">x</a>";
		print "</td></tr> ";
	}
	if($isupload)print "<form action=?action=attach-upload&tb=$tb&id=$id&dir=$dir method=post enctype=multipart/form-data><tr>
	<td colspan=2>Upload New Attachment <input type=file name=userfile><input type=submit value=Upload></td></tr></form>";
	print "</table></div>";
}
function tbdoc($tb,$id){
	print "Doc:<br>";
	if($id){
		$img="doc/$tb/$id.pdf";
		if(file_exists($img))print "<br><a href=$img ><img src=images/pdf.png></a><br>";
		print "-";
	}else{
	print " upload .jpg file <input type=file name=userfile> width < 500px ";
	}
}
function tbdocs($tb,$id){
	$dir="doc/$tb/$id";
	if(!file_exists($dir)) mkdir($dir);
	$dh=opendir($dir);
	while($file=readdir($dh)){
		$floc=$dir.'/'.$file;
		print "<li><img src=$floc><br>$file ";
	}
	print " upload .jpg file <input type=file name=userfile> width < 500px ";
	
}
function tbphoto($tb,$id){
	
	if($id){
		$img="images/$tb/$id.jpg";
		if(file_exists($img))print "<br><img src=$img width=500><br>";
		print "-";
	}else{
	print " upload .jpg file <input type=file name=userfile> width < 500px ";
	}
}
function wstalk_item($tb){
	print "<table id=drivers class=tb2 width=20%><thead><tr><td>นักขับเข้าร่วม</td></tr></thead><tbody></tbody></table> นักขับ <select id=driver name=driver >".driveroptions(3,'')."</select><input type=button value=เพิ่มนักขับ onclick=adddriver($('#driver').val());><script>
	function adddriver(d){
		name=$('#driver').find('option:selected').text();
		d=$('#driver').val();
		$('#d'+d).remove();
		$('#drivers').append('<tr id=d'+d+'><td>'+name+'</td><td width=30 onclick=rmdriver('+d+');>x</span></td></tr>');
		url='ajax.php?action=adddriver&tb=$tb&driver='+d;
		console.log('url:'+url);
		$.getJSON(url,function(data){
			
		});
	}
	$(function(){
		url='ajax.php?action=rmalldriver&tb=$tb';
		console.log('url:'+url);
		$.getJSON(url,function(data){
		});
		$('.trrm').click(function(){
			$('this').find('tr').remove();
		});
	});
	function rmdriver(d){
		$('#d'+d).remove();
		url='ajax.php?action=rmdriver&tb=$tb&driver='+d;
		console.log('url:'+url);
		$.getJSON(url,function(data){
			
		});
	}
	</script>";
	
}
function wsbus_item($tb,$id,$vo){
	$type=substr($tb,6,1);
	$tbitem=$tb.'item';
	$q="select id,rank,name from ws_busmaster where type='$type' ";
	$ck=mysql_query($q); //print $q;
	print "<table class=tb4><thead><tr><td></td>
	<td>รายการ</td>
	<td>ผ่าน</td>
	<td>ไม่ผ่าน</td>
	<td>หมายเหตุ</td>
	
	</tr></thead><tbody>";
	$checked=array('','checked');
	$unchecked=array('checked','');
	while(list($bm,$i,$tt)=mysql_fetch_array($ck)){
			if($id){
				$q="select result,comment from $tbitem where $tb='$id' and ws_busmaster='$bm' ";
				$ck2=mysql_query($q);
				list($rs,$cm)=mysql_fetch_array($ck2);
			}
			if($vo==1){
				$rs1='';$rs0='';
				if($rs==1 ) $rs1="<img src=images/icon-green.png>";
				if($rs==0 ) $rs0="<img src=images/icon-red.png>";
			}else{
				$rs1="<input type=radio name=item-result-$bm value=1 $checked[$rs] >";
				$rs0="<input type=radio name=item-result-$bm value=0 $unchecked[$rs] >";
			}
			print "<tr><td>$i</td><td>$tt</td><input type=hidden name=item-busmaster-$bm value=$bm>
			<td align=center>$rs1</td>
			<td align=center>$rs0</td>
			<td><input type=text name=item-comment-$bm  value='$cm'></td>
			</tr>";
			
	}
	print "</tbody></table>";
}
function stockcard($stock){
	global $yr,$action,$prmt;
	$today=date("Y-m-d");
	$bal=qval("select balance from stock where id='$stock' ");
	$prmt="tb=stock&id=$stock&stock=$stock&bal=$bal";
	yearnav();
	$ck=mysql_query("select code from vehicle where active=1 order by code ");
	while(list($c)=mysql_fetch_array($ck)){
		$vlist .="<span class=vlist onclick=descadd('$c');>$c</span>";
	}
	$q="select id,date,vehicle,description,if(in_out='in',qty,0),if(in_out='out',qty,0),record_by from stockcard where stock='$stock' and year(date)='$yr' order by date  ";
	$ck=mysql_query($q); //print $q;
	print "<table class=tb1><thead><tr><th> </th><th>Date</th><th>Description</th><th>Vehicle</th><th>In</th><th>Out</td><th>Balance</th><th>By</th>
</tr></thead><tbody>";
//<span onclick=distog('vlist'); class=vlist>vehicle</span><br><div id=vlist style=display:none;width:648;>$vlist</div>
	$voptions=qoptions("select id,code from vehicle where fleet='$_SESSION[fleet]' order by code ","");
	print "<form action=?action=stockcard-add&stock=$stock method=post name=form2>
	<input type=hidden name=bal value=$bal>
	<tr class=bd><td></td>
<td><input type=text name=date id=date size=8 style=width:100; value=$today></td>
<td><input type=text name=description size=60 style=width:500;></td>
<td><select name=vehicle><option>$voptions</select></td>
<td><input type=text name=qty_in  size=2 style=width:50; onclick=form2.qty_out.value=''; ></td>
<td><input type=text name=qty_out size=2 style=width:50; onclick=form2.qty_in.value=''; ></td>
<td colspan=2><input type=button value='New Record' onclick=balcheck(this.form);></td>
</tr></form><script>function balcheck(frm){
	if(frm.qty_out.value>frm.bal.value){
		alert('Can not out > balance ');
		return false;
	}
	frm.submit();
}
</script>";

	$k=1;
	$q="select sum(if(in_out='in',qty,-qty)) from stockcard where stock='$stock' and year(date) < $yr group by stock ";
	$ck2=mysql_query($q); //print $q;
	list($bal)=mysql_fetch_array($ck2);
	print "<tr class=bd onmouseover=this.className='bda'; onmouseout=this.className='bd'; onclick=$onclick><td class=int></td><td></td><td>Open Balance</td><td></td><td class=int></td><td class=int></td><td class=int><b>$bal</b></td><td></td>	</tr>";
	
	while(list($i,$date,$vh,$desc,$in,$out,$by)=mysql_fetch_array($ck)){
		$bal +=$in;
		$bal -=$out;
		if($in==0) $in='';
		if($out==0) $out='';
		$v=qval("select code from vehicle where id='$vh' ");
		$onclick="window.location.href='?action=edit&tb=stockcard&id=$i&stock=$stock';";
		print "<tr class=bd onmouseover=this.className='bda'; onmouseout=this.className='bd'; onclick=$onclick><td class=int>$k</td><td>$date</td><td>$desc</td><td>$v</td><td class=int>$in</td><td class=int>$out</td><td class=int>$bal</td><td>$by</td>
		</tr>";
		$k++;
	}
	print "</tbody></table><script>

$(function() {
$('#date').datepicker({dateFormat:'yy-mm-dd'});
});
function descadd(v){
	d=form2.description;
	if(d.value.length>0 ) d.value+=',';
	d.value+=v;
}
</script>";
}


function yearnav(){
	global $yr,$action,$prmt,$tb,$report;
	if(!$yr) $yr=date("Y");
	$pyr=$yr-1;
	$nyr=$yr+1;
	$prmt.="&tb=$tb";
	print "<table ><form action=?action=$action&$prmt&report=$report method=post><tr>
	<td><input type=button onclick=window.location.href='?action=$action&$prmt&report=$report&yr=$pyr'; value=&lt;&lt;></a></td>

	<td><select name=yr onclick=this.form.submit(); >";
	for($i=($yr-5);$i<($yr+6); $i++){
		if($yr==$i) print "<option value=$i selected>$i";
		else print "<option value=$i>$i";
	}
	print "</select></td>
	<td><input type=button onclick=window.location.href='?action=$action&$prmt&report=$report&yr=$nyr'; value=&gt;&gt;></a></td>
	</tr></form></table>";
}
function qval($q){
	$ck=mysql_query($q);
	list($out)=mysql_fetch_array($ck);
	return $out;
}
function qdr($q){
	$ck=mysql_query($q);
	$out=mysql_fetch_assoc($ck);
	return $out;
}
function qdt($q){
	$ck=mysql_query($q);
	while($dr=mysql_fetch_assoc($ck)){
		$out[]=$dr;
	}
	return $out;
}
function tbval($tb,$fld,$id){
	$q=" select $fld from $tb where id='$id' ";
	return qval($q);
}
function voptions(){
	global $action, $vehicle,$report,$date1,$date2,$date;
	if($vehicle) $vinfo=qval("select concat(plate,'/',drivers,'-',mobile) from vehicle where id='$vehicle' order by code  ");
	print "<table><form action=?action=$action&report=$report method=post>
<input type=hidden name=date1 value=$date1>
<input type=hidden name=date2 value=$date2>
<input type=hidden name=date value=$date>
<tr><td><select name=vehicle onchange=this.form.submit();><option value=\"\">...".qoptions("select id,code from vehicle where code<>'' order by code ",$vehicle)."</select></td><td>$vinfo</td></tr></form></table>";
}
function voptions2(){
	global $action, $vehicle,$report,$date1,$date2,$date,$mo,$yr,$driver,$customer;
	print "<table><form action=?action=$action&report=$report method=post>
<input type=hidden name=date1 value=$date1>
<input type=hidden name=date2 value=$date2>
<input type=hidden name=date value=$date>
<input type=hidden name=mo value=$mo>
<input type=hidden name=yr value=$yr>
<input type=hidden name=driver value=$driver>

<tr><td><select name=vehicle onchange=this.form.submit();><option value=\"\">...".tboptions2('vehicle',$vehicle)."</select></td><td></td></tr></form></table>";
}
function doptions(){
	global $action, $vehicle,$report,$date1,$date2,$date,$mo,$yr,$driver,$customer;
	print "<table><form action=?action=$action&report=$report method=post>
<input type=hidden name=date1 value=$date1>
<input type=hidden name=date2 value=$date2>
<input type=hidden name=date value=$date>
<input type=hidden name=mo value=$mo>
<input type=hidden name=yr value=$yr>
<input type=hidden name=vehical value=$vehicle>

<tr><td><select name=driver onchange=this.form.submit();><option value=\"\">...".tboptions2('driver',$driver)."</select></td><td></td></tr></form></table>";
}
function coptions(){
	global $action, $vehicle,$report,$date1,$date2,$date,$mo,$yr,$driver,$customer;
	print "<table><form action=?action=$action&report=$report method=post>
<input type=hidden name=date1 value=$date1>
<input type=hidden name=date2 value=$date2>
<input type=hidden name=date value=$date>
<input type=hidden name=mo value=$mo>
<input type=hidden name=yr value=$yr>
<input type=hidden name=vehical value=$vehicle>

<tr><td><select name=driver onchange=this.form.submit();><option value=\"\">...".tboptions2('customer',$customer)."</select></td><td></td></tr></form></table>";
}
function fuel(){
	global $action,$controlflds,$hiddenflds, $tbflds, $toaction, $vehicletbs, $vehicle,$csv;
	if(!$vehicle) $vehicle=$_SESSION[vehicle];
	$drivername=qval("select concat(t2.name,' มือถือ ',t2.mobile) from vehicle as t1,employee as t2 where t2.id=t1.driver and t1.id='$vehicle' ");
	$fuelplan=qval("select fuel_plan from vehicle where id='$vehicle' ");
	$vname=tbval('vehicle','code',$vehicle);
	if($vehicle) $vinfo=$drivername.'|'.$fuelplan;
	
	$csv =$vname.",".$vinfo."\n";
	print "<table><form action=?action=$action method=post><tr><td> Select Vehicle<select name=vehicle onchange=this.form.submit();><option>...".qoptions("select id,code from  vehicle where fleet='$_SESSION[fleet]' order by code ",$vehicle)."</select></td><td>$vinfo ";
	$t="fuel";
	$q=" select * from $t where vehicle='$vehicle' order by date desc,time desc ";
	if($_SESSION[uauth][add]){
	
		 if($vehicle>0) print " <br><a href=?action=new&tb=$t&vehicle=$vehicle> + Record New ".ucfirst($t)." for $vname + </a>";
	}
	if($_SESSION[uauth][view])	$toaction="view&tb=$t&vehicle=$vehicle";
	if($_SESSION[uauth][edit])	$toaction="edit&tb=$t&vehicle=$vehicle";

	browse($q,"tb".$t);

}
function mysql_enum_values($tb, $fld){
   $sql = "SHOW COLUMNS FROM $tb LIKE '$fld'";
   $sql_res = mysql_query($sql) or die("Could not query:\n$sql");
   $row = mysql_fetch_assoc($sql_res);
   mysql_free_result($sql_res);
   return(explode("','",
       preg_replace("/.*\('(.*)'\)/", "\\1",
           $row["Type"])));
}

function tbsets($tb,$fld,$val,$viewonly){
	global $statuscontroltbs;
	if(in_array($tb,$statuscontroltbs)) return flddict($val);
	$input="";
	$disabled=array('','disabled');
	$disabled=array('','readonly');
	$sets=mysql_enum_values($tb, $fld);
	if($todo=='add') $val=$sets[0];
	while(list(,$set)=each($sets)){
		$check="";
		if($set==$val){ $check=' checked ';}
		if($fld=='status') $disable='disabled';
		else $diable=''; //>
		$set2=$set;
		$set2=flddict($set);
		if($tb=='vehicle')$set2=$set;
		$input .="<nobr><input type=radio name=$fld value=\"$set\" $check $disabled[$viewonly] vo $viewonly id=$fld-$set >".$set2."</nobr> ";
	}
	return $input;
}

function employee($employee){
	global $deftab,$toaction;
	$tlist=array('training','history','picture','work_table');
	if($_SESSION[ulevel]=='officer'){
	$_SESSION[uauth][view]=1;
	$_SESSION[uauth][edit]=0;
	}
	print "<div id=\"tabs\">";
	print "<ul>";
	while(list($i,$t)=each($tlist)){
		if(!$deftab) $deftab=$t;
		if($t==$deftab) $def=$i;
		print "<li ><a href=\"#$t\">".ucfirst($t)." </a></li>";
	}
	print "</ul>";
	reset($tlist);
	while(list(,$t)=each($tlist)){
		print "<div id=\"$t\" >";
		if($t!='picture'){
		if(($_SESSION[uauth][add])&&($t<>'picture')) print " <a href=?action=new&tb=$t&employee=$employee> + Record New ".ucfirst($t)." + </a>";
		if($t=='training'){
			print "<form action=?action=addcourse&employee=$employee method=post>Assign Course<select name=course>".qoptions('select id,name from course',$course)."</select><input type=submit value=Assign></form>";
		}
		$q=" select * from $t where employee='$employee' order by date desc";
		//print $q;
		$toaction="edit&tb=$t&employee=$employee";
		array_push($controlflds,array('fleet','oemp'));
		browse($q,'tb'.$t);
		}else{
			$file="images/employee/$employee.jpg";
			$rnd=rand(1000,9999);
			print "<img src=$file?rnd=$rnd><br><form action=?action=upload&file=$file&fromaction=edit&tb=employee&id=$employee method=post enctype=multipart/form-data><input type=file name=userfile><input type=submit value=upload ></form>";
		}
		print "</div>";
	}
	
	print "<script>$('#tabs').tabs();</script>";
}
function history(){
	global $action,$controlflds,$hiddenflds, $tbflds, $toaction, $vehicletbs, $vehicle,$def,$deftab,$config;
	if(!$def) $def=0;
	if(!$vehicle) $vehicle=$_SESSION[vehicle];
	if($vehicle) $vinfo=qval("select concat(plate,'/',drivers,'-',mobile) from vehicle where id='$vehicle' ");
	mysql_query(" update battery set used_months= period_diff(  date_format(now(),'%Y%m'), date_format(date,'%Y%m')) where vehicle='$vehicle' ");
	mysql_query(" update battery set remain_months= $config[battery_life]-used_months where vehicle='$vehicle' ");

	print "<table><form action=?action=$action method=get><input type=hidden name=action value=$action><tr><td> Select Vehicle<select name=vehicle onchange=this.form.submit();><option>...".qoptions("select id,code from  vehicle where fleet='$_SESSION[fleet]' and code<>''  order by code  ",$vehicle)."</select></td><td>$vinfo ";
	if($_SESSION[utype]!='customer') print " <a href=?action=edit&tb=vehicle&id=$vehicle>Edit </a>|<a href=?action=browse&tb=vehicle> List</a>";
	print "</td></tr></form></table>"; 
	if(!$vehicle) return;
	print "<div id=\"tabs\">";
	
		$tlist=$vehicletbs;
		print "<ul>";
		while(list($i,$t)=each($tlist)){
			if($t==$deftab) $def=$i;
			print "<li ><a href=\"#$t\">".ucfirst($t)." </a></li>";
		}
		print "<li><a href=#doc>".flddict('doc')."</a></li>";
		print "</ul>";
		reset($tlist);
		while(list(,$t)=each($tlist)){
			print "<div id=\"$t\" >";
			if($_SESSION[uauth][add]) print " <a href=?action=new&tb=$t&vehicle=$vehicle> + Record New ".ucfirst($t)." + </a>";
			$q=" select * from $t where vehicle='$vehicle' order by date desc ";
		if($_SESSION[uauth][view])	$toaction="view&tb=$t&vehicle=$vehicle";
		if($_SESSION[uauth][edit])	$toaction="edit&tb=$t&vehicle=$vehicle";
			if($t=='workorder') $q="select id,date,id 'wo#',request_by,request_date,milage,description,mechanic,note,status,ref,if(datediff(now(),request_date)>3 and status not in ('cancel','complete'),'red','') 'trclass'			from $t where vehicle='$vehicle' order by date desc ";
			if($t=='battery') $q=" select id,date,battery_for,used_months,remain_months,note from $t where vehicle='$vehicle' order by date desc ";

			browse($q,"tb".$t);
			print "</div>";
		}
		print "<div id=doc>";
		$file="doc/vehicle/$vehicle.pdf";
		if(file_exists($file)) print "<iframe src=$file width=100% height=500></iframe><a href=$file target=_blank><img src=images/pdf.png>download </a> <a href=?action=doc-delete&file=$file onclick=\"return confirm('confirm delete?');\" >.</a>";
		print "<form action=?action=doc-upload&file=doc/vehicle/$vehicle.pdf method=post enctype=multipart/form-data><input type=file name=userfile><input type=submit value=Upload></form></div>";

	print "</div>
<script>$(function() {
	$('#tabs').tabs({selected:$def});
});

</script>";

}

function daterange(){
	global $action,$report, $date1, $date2, $vehicle,$prmt;
	if(!$date1) $date1=date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
	if(!$date2) $date2=date("Y-m-d",mktime(0,0,0,date("m")+1,0,date("Y")));
	print "<table><form action=?action=$action&report=$report&$prmt method=post>
<input type=hidden name=vehicle value=$vehicle>
<tr><td>from <input type=text name=date1 id=date1 value=$date1> to <input type=text name=date2 id=date2 value=$date2><input type=submit value=Go></tr></form></table>";
	print "<script>
	$(function() {
		$('#date1').datepicker({dateFormat:'yy-mm-dd'});
		$('#date2').datepicker({dateFormat:'yy-mm-dd'});
	});
	</script>";

/*
	$(function() {
		var dates = $( '#date1, #date2' ).datepicker({
			defaultDate: '+1w',
			changeMonth: true,
			numberOfMonths: 2,
			dateFormat:'yy-mm-dd'
		});
	});
			onSelect: function( selectedDate ) {
				var option = this.id == 'date1' ? 'minDate' : 'maxDate',
					instance = $( this ).data( 'datepicker' ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( 'option', option, date );
			}

*/
}
function daterange2(){
	global $action,$report, $date1, $date2, $vehicle,$prmt;
	if(!$date1) $date1=date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
	if(!$date2) $date2=date("Y-m-d",mktime(0,0,0,date("m")+1,0,date("Y")));
	print "<table><form action=?action=$action&report=$report&$prmt method=post>
<input type=hidden name=vehicle value=$vehicle>
<tr><td>from <input type=text name=date1 id=date1 value=$date1> to <input type=text name=date2 id=date2 value=$date2><input type=submit value=Go></tr></form></table>";
	print "<script>
	$(function() {
		$('#date1').datepicker({dateFormat:'yy-mm-dd'});
		$('#date2').datepicker({dateFormat:'yy-mm-dd'});
	});
	</script>";

/*
	$(function() {
		var dates = $( '#date1, #date2' ).datepicker({
			defaultDate: '+1w',
			changeMonth: true,
			numberOfMonths: 2,
			dateFormat:'yy-mm-dd'
		});
	});
			onSelect: function( selectedDate ) {
				var option = this.id == 'date1' ? 'minDate' : 'maxDate',
					instance = $( this ).data( 'datepicker' ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( 'option', option, date );
			}

*/
}
function daterange3(){
	global $action,$report, $date1, $date2, $vehicle,$prmt;

	if(!$date1) $date1=date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
	if(!$date2) $date2=date("Y-m-d",mktime(0,0,0,date("m")+1,0,date("Y")));
	print "date <input type=text class=date name=date1 id=date1 value=$date1> to <input type=text class=date name=date2 id=date2 value=$date2>";
}
function daterange4(){
	global $action,$report, $date1, $date2, $vehicle,$prmt;

	if(!$date1) $date1=date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
	if(!$date2) $date2=date("Y-m-d",mktime(0,0,0,date("m")+1,0,date("Y")));
	return  "<input type=text class=date name=date1 id=date1 value=$date1> to <input type=text class=date name=date2 id=date2 value=$date2>";
}
function tboptions($tb,$id){
	global $global_options;
	$blank=1;
	
	if(($tb=='customer')&&($_SESSION[utype]==$tb)){ $cond .=" and id='$_SESSION[cid]' "; $blank=0;}
	if($tb=='vehicle') $cond .=" and active='1' ";
	$cond .=" and fleet='$_SESSION[fleet]' ";
	if(in_array($tb,$global_options)) $cond='';
	$q=" select id,code from $tb where 1 $cond order by code ";
	$ck=mysql_query($q); //print $q;
	if(!$ck) return '';
	$out ="<select name=$tb id=$tb >";
	if($blank) $out .="<option value=''>";
	while(list($i,$t)=mysql_fetch_array($ck)){
		if($id==$i) $out .="<option value=$i selected> $t";
		else $out .="<option value=$i>$t";
	}
	$out .="</select> ";
	return $out;
}
function qoptions($q,$val){
	$ck=mysql_query($q); 
	
	if(!$ck) print $q;
	//$out.=$q; //print $q;
	while(list($i,$t)=mysql_fetch_array($ck)){
		//print "<li> $i - $t ";
		if($val==$i) $out .="<option value=\"$i\" selected> $t";
		else $out .="<option value=\"$i\">$t";
		
	}
	//$out.=$q;
	return $out;
}
function tboptions2($tb,$val){
	global $tbcode,$tbname,$customer;
	if($tb=='route') $cond .=" and customer='$customer' ";
	$cond .=" and fleet='$_SESSION[fleet]' ";
	if(in_array($tb,$tbcode)) $q="select id,code from $tb where 1 $cond order by code ";
	if(in_array($tb,$tbname)) $q="select id,name from $tb where 1 $cond order by name ";
	
	if($tb=='vehicle') $q="select id,code from $tb where 1 $cond order by type,seq ";
	
	$ck=mysql_query($q); //print $q;
	while(list($i,$t)=mysql_fetch_array($ck)){
		if($i==$val) $out .="<option value=$i selected>$t";
		else $out .="<option value=$i>$t";
	}
	return $out;
}
function tboptions3($tb,$id){
	global $cond;
	//if($tb=='course') $cond='';
	$q=" select id,name from $tb where 1 $cond order by name ";
//	if($tb=='supplier') $q=" select id,concat(name,'-',type) from $tb where 1 $cond order by name ";
	$ck=mysql_query($q); //print $q;
	$out .="<select name=$tb onchange=chk$tb(this);><option value=''>";
	while(list($i,$t)=mysql_fetch_array($ck)){
		if($id==$i) $out .="<option value=$i selected> $t";
		else $out .="<option value=$i>$t";
	}
	$out .="<option value='new$tb' >..new $tb..</select><input id=new$tb type=text name=new$tb size=30 value='new $tb' style=display:none; onclick=this.select();><script>
	function chk$tb(obj){
		val=obj.options[obj.selectedIndex].value;
		if(val=='new$tb') {
			$('#new$tb').show();
		}else{
			$('#new$tb').hide();
			";
if($tb=='customer') $out.="obj.form.toaction.value='edit';obj.form.submit();";
		
	$out.="}
	}
	</script>";
	
	return $out;
}
function tbcode($tb,$id){
	$q=" select code from $tb where id='$id' ";
	$ck=mysql_query($q);
	list($out)=mysql_fetch_array($ck);
	return $out;
	
}
function dashboard(){
	$q="select t1.id,t1.code,t1.next_pm_mile,t1.plate,t1.driver from vehicle as t1 where  t1.contract=0 and t1.fleet='$_SESSION[fleet]' order by t1.code ";
	$ck=mysql_query($q); //print $q;
	print "<table class=tb1><thead><tr><th colspan=4>Vehicle</th><th>Next PM Mile</th><th>Curren Mile</th><th>Action</th><th>Mile Left</th></tr></thead><tbody>";
	while(list($v,$c,$npm,$plate,$drv)=mysql_fetch_array($ck)){
		//,$driver,$mobile
		$q="select max(t3.milage) from (SELECT vehicle,milage FROM milage as t1 union SELECT vehicle,milage FROM workorder as t2 union select vehicle,milage from fuel as t4 ) as t3 where t3.vehicle='$v' ";
		$ck2=mysql_query($q); //print $q;
		list($mile)=mysql_fetch_array($ck2);
		$alert="";
		if($mile>$npm) $alert="<span style=background:red;color:white;>Need PM</span>";
		$moremile=$npm-$mile;
		
	print "<tr class=bd onmouseover=this.className='bda'; onmouseout=this.className='bd' 
><td>$c</td><td>$plate</td><td>$driver</td><td>$mobile</td><td class=int>".number_format($npm,0)."</td><td class=int>".number_format($mile,0)."</td><td>$alert</td><td class=int>".number_format($moremile,0)."</td></tr>";
	}
	print "</tbody></table>";
}
function lastmile($vehicle){
	$ck=mysql_query("select milage from fuel where vehcicle order by milage desc limit 1 ");
	if($ck)	list($lm)=mysql_fetch_array($ck);
	else $lm=0;
	return $lm;
}
function monthnav(){
	global $mo,$yr,$action,$tb,$report,$cmprmt,$ws,$customer,$route;
	$cmrpmt.="&ws=$ws&customer=$customer&route=$route";
	if(!$yr) $yr=date("Y");
	if(!$mo) $mo=date("m");
	if($mo>12){$yr++; $mo='01';}
	
	$mon=array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$out .="
	<select name=mo onchange=this.form.submit();>";
	for($i=1;$i<13;$i++){
		$ii=$i;
		if($i<10) $ii="0".$i;
		if($mo==$i) $out .="<option value=$ii selected>$mon[$i]";
		else $out .="<option value=$ii>$mon[$i]";
	}
	$out .="</select><select name=yr onchange=this.form.submit();>";
	for($i=($yr-2); $i<($yr+10); $i++) {
		if($i==$yr) $out .="<option value=$i selected>$i";
		else $out .="<option value=$i>$i";
	}
	$out .="<select>";
	$pmo=$mo-1;$pyr=$yr;
	if($pmo<1){$pmo=12;$pyr--;}
	$nmo=$mo+1;$nyr=$yr;
	if($nmo>12){$nmo=1;$nyr++;}
	if($pmo<10) $pmo="0".$pmo;
	if($nmo<10) $nmo="0".$nmo;
	print "<div class=monthnav><form method=get action=?action=$action><input type=hidden name=report value=$report>
	<input type=hidden name=action value=$action><input type=hidden name=tb value=$tb><input type=hidden name=ws value=$ws>
	<input type=button value=&lt; onclick=window.location.href='?action=$action&mo=$pmo&yr=$pyr&tb=$tb&report=$report&ws=$ws&customer=$customer&route=$route'>$out<input type=button value=&gt; onclick=window.location.href='?action=$action&mo=$nmo&yr=$nyr&tb=$tb&report=$report&ws=$ws&customer=$customer&route=$route'> </form></div>";

}

function monthnav2(){
	global $mo,$yr,$action,$customer,$route;
	if(!$yr) $yr=date("Y");
	if(!$mo) $mo=date("m");
	if($mo>12){$yr++; $mo='01';}
	
	$mon=array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$out .="<select name=mo onchange=this.form.submit();>";
	for($i=1;$i<13;$i++){
		$ii=$i;
		if($i<10) $ii="0".$i;
		if($mo==$i) $out .="<option value=$ii selected>$mon[$i]";
		else $out .="<option value=$ii>$mon[$i]";
	}
	$out .="</select><select name=yr onchange=this.form.submit();>";
	for($i=($yr-2); $i<($yr+10); $i++) {
		if($i==$yr) $out .="<option value=$i selected>$i";
		else $out .="<option value=$i>$i";
	}
	$out .="<select>";
	$pmo=$mo-1;$pyr=$yr;
	if($pmo<1){$pmo=12;$pyr--;}
	$nmo=$mo+1;$nyr=$yr;
	if($nmo>12){$nmo=1;$nyr++;}
	$out ="<input type=button value=&lt; onclick=window.location.href='?action=$action&mo=$pmo&yr=$pyr&customer=$customer&route=$route'>".$out;
	$out =$out."<input type=button value=&gt; onclick=window.location.href='?action=$action&mo=$nmo&yr=$nyr&customer=$customer&route=$route'>";
	return $out;
}
function datejob($date){
	$q="select t1.id,date_format(t1.time,'%H:%i'),t1.customer,t1.customer_name,t1.vehicle,t1.driver,t1.description,t1.status
	from plan as t1,vehicle as t2 where t2.id=t1.vehicle and t2.fleet='$_SESSION[fleet]' and t1.type='extra' and t1.date='$date' order by time ";
	$ck=mysql_query($q); //print $q;
	while(list($id,$t,$cust,$custname,$v,$d,$desc,$status)=mysql_fetch_array($ck)){
		$cust=qval("select code from customer where id='$cust' ");
		if($cust=='|OTHER') $cust=$custname;
		$v=qval("select code from vehicle where id='$v' ");
		$d=qval("select name from employee where id='$d' ");
		$out .="<a href=?action=edit&tb=plan&id=$id&type=extra title='$d - $desc $status' alt='$d - $desc $status' class=$status onmouseover=this.className='jhover' onmouseout=this.className='$status'>$t/$cust/$v</a><br>";
	}
	return $out;
}

function job(){
	global $mo,$yr,$action;
	print "<form action=? method=get><input type=hidden name=action value=$action>".monthnav2()."</form>";
	print "<table cellspacing=1 cellpadding=3 width=99%><tr>
	<t
	</tr><tr class=cal1>";
	$dt=mktime(0,0,0,$mo,1,$yr);
	$dow1=date("w",$dt);
	for($d=(-1*$dow1);$d<0;$d++){
		$dt=mktime(0,0,0,$mo,$d+1,$yr);
		$d1=date("d",$dt);
		print "<td class=pmonth>$d1</td>";
	}
	for($d=1;$d< 32;$d++){
		if(!checkdate($mo,$d,$yr)) continue;
		$dt=mktime(0,0,0,$mo,$d,$yr);
		$dd=$d;
		if($d<10) $dd="0".$d;
		$date="$yr-$mo-$dd";
		$info=datejob($date);
		print "<td width=14% align=right class=caldate>$d<br>$info</td>";
		$dow=date("w",$dt);
		if($dow==6) print "</tr><tr class=cal1>";
	}
	$d=1;
	while($dow<6){
		$dt=mktime(0,0,0,$mo+1,$d,$yr);
		$dow=date("w",$dt);
		print "<td class=nmonth>$d</td>";	
		$d++;
	}
	print "</tr></table>";
}
function items($itb,$tb,$id){
	global $sumflds,$tbflds, $tbcode,$tbname,$tbno,$controlflds,$calflds,$type,$vo;
	$dr=qdr("select * from $tb where id='$id' ");
	$q=" select * from $itb where $tb='$id' ";
	if(($tb=='streq')&&($type!='receive')) array_push($controlflds,'cost');
	$ck=mysql_query($q); //print $q;
	if(($tb=='suggest')&&(mysql_num_rows($ck)==0)){
		//$q1=" insert into $itb ($tb) values ('$id') ";
		//mysql_query($q1); print $q1;
		$q=" select * from $itb where $tb='$id' ";
		$ck=mysql_query($q); //print $q;
	}
	print "<center><table class=tb4><thead><tr><td>#</td>";
	for($j=2;$j<mysql_num_fields($ck);$j++){
		$fld=mysql_field_name($ck,$j);
		
		if(in_array($fld,$controlflds)) continue;
		if($fld==$tb) continue;
		print "<td>".flddict($fld)."</td>";
	}
	print "<td></td></tr><thead><tbody>";
	$sum=array();
	$k=1;
	for($i=0;$i<mysql_num_rows($ck);$i++){
		$iid=mysql_result($ck,$i,0);
		$js4="";
		//onclick=window.location.href='?action=item-edit&tb=$tb&id=$id&iid=$iid';
		print "<tr ><td>$k</td>";
		for($j=2;$j<mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			if(in_array($fld,$controlflds)) continue;
			if($fld==$tb) continue;
			$ftype=mysql_field_type($ck,$j);
			$fflags=mysql_field_flags($ck,$j);
			$fflags=trim(str_replace("not_null","",$fflags));
			
			$val=mysql_result($ck,$i,$j);
			if($fflags=='set') $js4 .="$('#$val').attr('checked','checked');";
			else $js4.="itemform.$fld.value='$val';";
			if(in_array($fld,$sumflds)) $sum[$fld]+=$val;
			if(in_array($fld,$tbflds)){
				$ftype='string';
				
				
				if($fld=='st')$val=tbval($fld,"concat(part_no,' ',name,' - ',cost,'/',unit)",$val);
				else $val=tbval($fld,'name',$val);
			}
			if($ftype=='blob') $val=nl2br($val);
			print "<td class=$ftype>$val</td>";
		}
		print "<td>";
		if($vo!=1) print "<input type=button value=edit onclick=\"itemform.btn.value='Update';itemform.action.value='item-update';itemform.iid.value='$iid';$js4\" >
		<a href=?action=item-delete&tb=$tb&id=$id&itb=$itb&iid=$iid  onclick=\"return confirm('confirm delete?')\">x</a>";
		print "</td></tr>";
		$k++;
	}
	print "</tbody>";
	/*
	if(count($sum)>0){
		print "<tr>";
		for($j=1;$j<mysql_num_fields($ck);$j++){
			$fld=mysql_field_name($ck,$j);
			if(in_array($fld,$controlflds)) continue;
			$val='';
			if(in_array($fld,$sumflds)) $val=number_format($sum[$fld],2);
			print "<td class=real>$val</td>";
		}
		print "</tr>";
	}
	*/
	if($vo!=1){
		print "<form action=?tb=$tb&id=$id&itb=$itb method=post name=itemform><input type=hidden name=iid ><input type=hidden name=action value=item-add><tfoot><tr><td></td>";
		for($j=2;$j<mysql_num_fields($ck);$j++){
			$val='';
			$fld=mysql_field_name($ck,$j);
			$ftype=mysql_field_type($ck,$j);
			if(in_array($fld,$controlflds)) continue;
			if($fld==$tb) continue;
			$fflags=mysql_field_flags($ck,$j);
			$fflags=trim(str_replace("not_null","",$fflags));
			if($fld=='qty') $val=1;
			$input="<input type=text class='$ftype' name=$fld id=if$fld size=20 value='$val' >";
			if($fld=='st') $input ="<select name=$fld onchange=checkcost(this.value); id=if$fld style='max-width:250;'><option>".qoptions("select id,concat(part_no,' ',name,' / ',unit) from $fld order by part_no ")."</select>";
			$js5.="if(frm.$fld.value==''){alert('Please input data $fld');frm.$fld.focus();return false;}\n";
			if($fflags=='set') $input=tbsets($itb,$fld,$val,$vo);

			print "<td >$input</td>";
		}
		print "<td><input type=submit name=btn value=Add onclick=\"return ivld(this.form);\"></td></tr></form>";
	}
	print "</tfoot></table><script>function ivld(frm){
	$js5 
	}
	$(function(){
		$('#qinput').focus();		
	});

	</script>";
	if($tb=='streq'){
		print "<form action=?action=streqitem-add&tb=$tb&id=$id&itb=$itb method=post>
		Quick Input <input type=text id=qinput name=partno placeholder='Part no'>
		<input type=text name=qty size=5 value=1 placeholder='จำนวน'>
		<input type=submit value=Add>
		</form>";
	}
}
function itemform($itb,$tb,$id){
	print "<table class=tb2 ><thead><tr ><td colspan=2>Add New Item</td></thead><tbody><form action=?action=item-add&tb=$tb&id=$id&itb=$itb method=post>";
	global $sumflds,$tbflds, $tbcode,$tbname,$tbno,$controlflds,$calflds;
	
	$q=" select * from $itb where $tb='$id' ";
	$ck=mysql_query($q);
	for($j=2;$j<mysql_num_fields($ck);$j++){
		$fld=mysql_field_name($ck,$j);
		if(in_array($fld,$controlflds)) continue;
		if(in_array($fld,$calflds)) continue;
		$ftype=mysql_field_type($ck,$j);
		$input="<input type=text size=30 name=$fld >";
		if(in_array($fld,$tbflds)){
			if(in_array($fld,$tbcode)) $input=tboptions($fld,$val);
			if(in_array($fld,$tbname)) $input=tboptions3($fld,$val);
			if(in_array($fld,$tbno)) $input="<select name=$fld>".tboptions2($fld,$val)."</select><input type=text name=new_$fld size=30>";
		}
		if($ftype=='blob') $input="<textarea name=$fld cols=80 rows=3>$val</textarea>";
		print "<tr><td>".flddict($fld)."</td><td>$input</td></tr>";
	}
	print "<tr><td colspan=2><input type=submit value=Add></td></tr></tbody></table>";

	
}
function itemedit($tb,$id,$iid){
	print "<table class=tb2 ><thead><tr ><td colspan=2>Edit Item</td></thead><tbody><form action=?action=item-update&tb=$tb&id=$id&iid=$iid method=post>";
	global $sumflds,$tbflds, $tbcode,$tbname,$tbno,$controlflds,$calflds;
	$tbitem=$tb."item";
	$q=" select * from $tbitem where id='$iid'";
	$ck=mysql_query($q);
	for($j=2;$j<mysql_num_fields($ck);$j++){
		$fld=mysql_field_name($ck,$j);
		if(in_array($fld,$controlflds)) continue;
		if(in_array($fld,$calflds)) continue;
		$ftype=mysql_field_type($ck,$j);
		$val=mysql_result($ck,0,$j);
		$input="<input type=text size=30 name=$fld value=\"$val\">";
		if(in_array($fld,$tbflds)){
			if(in_array($fld,$tbcode)) $input=tboptions($fld,$val);
			if(in_array($fld,$tbname)) $input=tboptions3($fld,$val);
			if(in_array($fld,$tbno)) $input="<select name=$fld>".tboptions2($fld,$val)."<option >..new.. </select><input type=text name=new_$fld size=30>";
		}
		if($ftype=='blob') $input="<textarea name=$fld cols=80 rows=3>$val</textarea>";
		print "<tr><td>".flddict($fld)."</td><td>$input</td></tr>";
	}
	print "<tr><td colspan=2><input type=submit value=Update></td></tr></tbody></table>";

	
}
function amountcal($tb,$id){
	$tbitem=$tb."item";
	$q=" update $tb set amount = (select sum(amount) from $tbitem where $tb=$id ) where id='$id' ";
	mysql_query($q);
	
}
function newno($tb){
	$q=" SELECT date_format(now(),'%y%m'),if(max(right(no,3)) is null,0,max(right(no,3)))+1 FROM $tb WHERE date_format(date,'%Y-%m')=date_format(now(),'%Y-%m') ";
	$ck=mysql_query($q);
	list($yrmo,$no)=mysql_fetch_array($ck);
	if($no<10)  $no.="0".$no;
	if($no<100) $no.="0".$no;

	if($tb=='qtn'){
		
		return $yrmo."Q".$no;
		
	}
}
function newname($tb,$val){
	global $now;
	$q="select id from $tb where name='$val' ";
	if($tb=='contact') $q="select id from $tb where name='$val' and customer='$_POST[customer]' ";
	$ck=mysql_query($q); 
	if(mysql_num_rows($ck)>0) {
		list($id)=mysql_fetch_array($ck);

	}else{
		$q=" insert into $tb (name,logs) values ('$val','$now added by $_SESSION[user]\n') ";
		if($tb=='contact') $q=" insert into $tb (customer,name,logs) values ('$_POST[customer]','$val','$now added by $_SESSION[user]\n') ";
		mysql_query($q); print $q;
		$id=mysql_insert_id();
	}
	$_POST[$tb]=$id;
	return $id;
}
function datefmt($in){
	global $datefmt;
	$y=substr($in,0,4);
	$m=substr($in,5,2);
	$d=substr($in,8,2);
	$dt=mktime(0,0,0,$m,$d,$y);
	return date($datefmt,$dt);
}
function fmtdate($in){
	$y=substr($in,7,4);
	$m=substr($in,3,3);
	$d=substr($in,0,2);
	$mlist=array('Jan'=>'01','Feb'=>'02','Mar'=>'03','Apr'=>'04', 'May'=>'05', 'Jun'=>'06', 'Jul'=>'07', 'Aug'=>'08', 'Sep'=>'09', 'Oct'=>'10', 'Nov'=>'11', 'Dec'=>'12');
	return "$y-$mlist[$m]-$d";
}
function dump($date,$badge){
//	global $db1,$db2;
	$db1="atp30_web";
	$db2="atp30_zk";

	$w=qval("select date_format('$date','%w') ");
	if($badge){
		mysql_query(" delete from $db1.ta where date='$date' and badgenumber in ($badge) ");
		$cond .=" and t1.badgenumber in ( $badge ) ";
	}else{
		mysql_query(" delete from $db1.ta where date='$date' ");
	}
	$q="select t1.id,t1.badgenumber,t1.level,t3.in_time,t3.out_time
	from $db1.employee as t1, $db1.work_table as t2, $db1.timetable as t3
where t2.employee=t1.id and t3.id=t1.timetable
and t1.badgenumber >0  $cond
and '$date' between t2.date and t2.end_date 
	";
	$ck=mysql_query($q); //print $q;
	
	while(list($e,$b,$lv,$it,$ot)=mysql_fetch_array($ck)){
		$ov=0;
		$it=substr($it,0,5);
		$ot=substr($ot,0,5);
		if($ot<$it) $ov=1;
		
		$q="select min(date_format(t1.checktime,'%H:%i')) from $db2.checkinout as t1,$db2.userinfo as t2 where t2.userid=t1.userid and t2.badgenumber=$b and date_format(t1.checktime,'%Y-%m-%d')='$date' ";
		$in=qval($q); //print $q;
		$q="select max(date_format(t1.checktime,'%H:%i')) from $db2.checkinout as t1,$db2.userinfo as t2 where t2.userid=t1.userid and t2.badgenumber=$b and date_format(t1.checktime,'%Y-%m-%d')='$date' ";
		$out=qval($q); //print $q;
		if($ov==1){
			$q="select max(date_format(t1.checktime,'%H:%i')) from $db2.checkinout as t1,$db2.userinfo as t2 where t2.userid=t1.userid and t2.badgenumber=$b and date_format(t1.checktime,'%Y-%m-%d')='$date' ";
			$in=qval($q); //print $q;
			$q="select min(date_format(t1.checktime,'%H:%i')) from $db2.checkinout as t1,$db2.userinfo as t2 where t2.userid=t1.userid and t2.badgenumber=$b and date_format(t1.checktime-interval 2 hour ,'%Y-%m-%d')='$date' ";
			$out=qval($q); //print $q;
		}
		if(($in==$out)&&($out<'12:00')) $out='';
		if(($in==$out)&&($in>'12:00')) $in='';
		$r='';
		if($in>$it) $r='L';
		if($out<$ot) $r='E';
		if((!$in)&&($out)) $r='L';
		if(($in)&&(!$out)) $r='E';
		if((!$in)&&(!$out)) $r='A';
		if($lv!='officer') $r='';
		if(($w==0)||($w==6)){
			$r='';
			if((!$in)&&(!$out)) continue;
		}
		//print "$e,$b,$it,$ot - $in, $out : $r <br>";
		$q="insert into $db1.ta 
(employee,badgenumber,date,in_time,out_time,remark)
values
($e,$b,'$date','$in','$out','$r')
		";
		mysql_query($q);
	}
}
function photo($tb,$id){
	$dir="images/$tb";
	if(!file_exists($dir)) mkdir($dir);
	$dir="images/$tb/$id";
	if(!file_exists($dir)) mkdir($dir);
	$dh=opendir($dir);
	print "<div class=photos><b>รูปภาพ</b><br>";
	while($file=readdir($dh)){
		if($file=='.') continue;
		if($file=='..') continue;
		$img="$dir/$file";
		$file2=urlencode($file);
		print "<a href=$img target=_blank><img src=$img height=150></a>
		<a href=?action=photo-delete&file=$dir/$file2&tb=$tb&id=$id onclick=\"return confirm('Confirm Delete Photo');\">x</a>&nbsp;
		 ";
	}
	print "<form action=?action=photo-upload&tb=$tb&id=$id&dir=$dir method=post enctype=multipart/form-data> Add photo 
	<input type=file name=userfile><input type=submit value=Upload>
	</form></div>";
}
function qexe($q){
	//print $q.'<br>';
	$rs=mysql_query($q);
	if(!$rs){ 
		print 'ERROR-'.mysql_errno().' '.mysql_error()."\n<br>".$q;
	}
	return $rs;
}
?>
