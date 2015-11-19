function add_settings(subject){
	$.ajax(OC.linkTo('user_group_admin','ajax/settingactions.php'), {
		 type:'POST',
		  data:{
			 'action': 'addsubject', 'mailsubject': subject
		 },
		 dataType:'json',
		 success: function(data){

		 },
		error:function(data){
			alert("Unexpected error!");
		}
	});
}



$(document).ready(function() {
 	$('#mailsubmit').click(function() {
		subject = $('#mailsubject').val();
		add_settings(subject);
	});
});	
