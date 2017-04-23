function add_settings(freequota, subject, sender){
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
	$.ajax(OC.linkTo('user_group_admin','ajax/settingactions.php'), {
		 type:'POST',
		  data:{ 'action': 'setgroupdefaultfreequota', 'freequota': freequota},
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
 		freequota = $('#default_group_freequota').val();
		subject = $('#mailsubject').val();
		sender = $('#mailsender').val(); 
		add_settings(freequota, subject, sender);
	});
});		

