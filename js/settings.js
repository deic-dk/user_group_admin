function add_settings(subject, sender, url){
	$.ajax(OC.linkTo('user_group_admin','ajax/settingactions.php'), {
		 type:'POST',
		  data:{
			 'action': 'addsubject', 'mailsubject': subject, 'mailsender': sender, 'accepturl': url
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
		sender = $('#mailsender').val(); 
		url = $('#accepturl').val();
		add_settings(subject, sender, url );
	});
});		

