<?php //>
if($action=='pwd-change'){
	if($_SESSION[utype]=='customer')$q=" update customer set password='$password2' where id='$_SESSION[cid]' ";
	else $q=" update user set password='$password2' where id='$_SESSION[uid]' ";
	$ck=mysql_query($q); //print $q;
	if($ck) print " Password changed, ";
	print " Please Login with New Password ";
	session_destroy();
	$action='login';
}
$sid=session_id();
if($action=='logout'){
	$q="update userlog set outtime=now() where sid='$sid' ";
	qexe($q);
	unset($_SESSION);
	session_destroy();
	$action='login';
}
if($action=='log'){
	
	$q="select id,department,level,location,fleet,all_fleets from user where upper(login)=upper('$login') and upper(password)=upper('$password') ";
	$ck=mysql_query($q); //print $q;
	
	if(mysql_num_rows($ck)>0){
		list($uid,$udept,$ulevel,$_SESSION[ulocation],$_SESSION[ufleet],$_SESSION[afleets])=mysql_fetch_array($ck);
		$_SESSION[uid]=$uid;
		$_SESSION[udept]=$udept;$_SESSION[ulevel]=$ulevel;
		$_SESSION[user]=strtolower($login);
		$_SESSION[utype]='user';
		$_SESSION[uauth][edit]=1;
		$_SESSION[uauth][add]=1;
		$_SESSION[uauth][view]=1;
		if($_SESSION[udept]=='operation'){
			$_SESSION[uauth][edit]=1;
			$_SESSION[uauth][add]=1;
			$_SESSION[uauth][view]=1;
		}
		$_SESSION[fleet]=$_SESSION[ufleet];
		mysql_query("insert into userlog (user,sid,login,datetime) values ('$_SESSION[uid]','$sid','$login',now() ) ");
		$action='';

	}else{
		$q="select id,code,name,fleet from customer where upper(login)=upper('$login') and password='$password' ";
		$ck=mysql_query($q); //print $q;
		if(mysql_num_rows($ck)>0){
			list($_SESSION[cid],$_SESSION[user],$_SESSION[cname],$fleet)=mysql_fetch_array($ck);
			$_SESSION[user]=strtolower($login);
			$_SESSION[uid]=$_SESSION[cid];
			$_SESSION[udept]='customer';
			$_SESSION[utype]='customer';
			$_SESSION[fleet]=$fleet;
		$_SESSION[uauth][edit]=0;
			$_SESSION[uauth][add]=1;
			$_SESSION[uauth][view]=1;
			mysql_query("insert into userlog (user,sid,,login,datetime) values ('$_SESSION[uid]','$sid','$login',now() ) ");
			
		}else{
			$action='login';
			$msg=" user/password $login $password not correct";
			
		}

	}

}

if(!$_SESSION[user]) $action='login';
if($action=='login'){
	print "<center><div style=width:300>$msg<table  class=tb1 style=\"margin-top:50;border-radius:5 5 5 5;border:2px solid #bbb;\"><form action=?action=log method=post>
<thead><tr class=hd><th colspan=2>$systemname</th></tr></thead><tbody>
<tr class=bd><td colspan=2><img src=images/logo450x240.jpg width=300></td></tr>
<tr class=bd><td>Username</td><td><input type=text name=login size=30></td></tr>
<tr class=bd><td>Password</td><td><input type=password name=password size=30></td></tr>
<tr class=ft><td></td><td><input type=submit value=Login>
V. $version
</td></tr></tbody>
</form></table></div>";
	mysql_close();exit;
}
if($action=='change-pwd'){
	print "<center><div style=width:300>$msg<table  class=tb1 style=\"margin-top:100;border-radius:5 5 5 5;border:2px solid #bbb;\"><form action=?action=pwd-change method=post>
<thead><tr class=hd><th colspan=2>Change Password</th></tr></thead><tbody>
<tr class=bd><td>New Password</td><td><input type=password name=password1 size=30></td></tr>
<tr class=bd><td>New Password (again) </td><td><input type=password name=password2 size=30></td></tr>
<tr class=ft><td></td><td><input type=button value=Change onclick=pwdvld(this.form);></td></tr></tbody>
</form></table></div><script>
function pwdvld(frm){
	if(frm.password1.value=='') { alert('please input New Password'); frm.password1.focus(); return false;}
	if(frm.password2.value=='') { alert('please input New Password again'); frm.password2.focus(); return false;}
	if(frm.password1.value!=frm.password2.value) { alert('Password not match, please input same New Password'); frm.password2.focus(); return false;}
	frm.submit();
}
</script>";
	mysql_close();exit;
}
?>
