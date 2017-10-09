function distog(obj){
	var el = document.getElementById(obj);
	if ( el.style.display != 'none' ) {
		el.style.display = 'none';
	}
	else {
		el.style.display = '';
	}

}
function driverlist(vehicle,contract){
	//alert('driver list v '+vehicle+' c '+contract);
	$('#driver').load('ajax.php?action=driverlist&contract='+contract+'&vehicle='+vehicle);
}
$(function() {
	$(".date" ).datepicker({dateFormat:'yy-mm-dd'});
	$('#plan_date').change(function(){
		$('#status-plan').click();
	});
	console.log('check number format');
	$('.real').number(true,2);
	
});