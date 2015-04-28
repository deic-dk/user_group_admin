$(document).ready(function() {
	$(document).on ("click", "#accept", function () {
        	window.alert($(this).attr('value') );
		$.ajax({
                     url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                     type: 'post',
                     data: { 'group': $(this).attr('value'), 'action': 'acceptinvitation'},
                     success: function(data, status) {
                        location.reload();
                     }
                });
    });	
	$(document).on ("click", "#decline", function () {
		$.ajax({
                     url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                     type: 'post',
                     data: {'group': $(this).attr('value'), 'action': 'declineinvitation'},
                     success: function(data, status) {
			location.reload();
                     }
                });
	});
})
