function add_settings(subject, sender){
	$.ajax(OC.linkTo('user_group_admin','ajax/settingactions.php'), {
		 type:'POST',
		  data:{ 'action': 'addsubject', 'mailsubject': subject, 'mailsender': sender},
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
		sender = $('#mailsender').val(); 
		add_settings(subject, sender);
	});
});		

