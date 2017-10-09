console.log('started 1');
function checkcost(st){
	url='ajax.php?action=checkval&fld=cost&tb=st&id='+st;
	console.log('url:'+url);
	$.getJSON(url,function(r){
		console.dir(r);
		$('#ifcost').val(r.data);	
	});
	
}

$(function(){
	console.log('started 2');
	$('#request_date').attr('disabled','disabled');
	$('.date').datepicker({dateFormat:'yy-mm-dd'});
	$('.real').number(true,2);
	$('.int').number(true,0);
	
	
});
function alllist(tb,val){
	url='ajax.php?action=alllist&tb='+tb+'&val='+val;
	console.log('url:'+url);
	$('#'+tb).load(url);
}
