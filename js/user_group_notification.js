$(document).ready(function() {
	$(document).on ("click", "#accept", function () {
		$.ajax({
                     url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                     type: 'post',
                     data: { 'group': $(this).attr('value'), 'action': 'acceptinvitation'},
                     success: function(data, status) {
			$("#accept").parent().parent().html('You joined group <b>'+$("#accept").attr('value')+'</b>');
			$("#accept").parent().text("");
                     }
                });
    });	
	$(document).on ("click", "#decline", function () {
		$.ajax({
                     url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                     type: 'post',
                     data: {'group': $(this).attr('value'), 'action': 'declineinvitation'},
                     success: function(data, status) {
			$("#decline").parent().parent().html('You rejected an invitation to group <b>'+$("#decline").attr('value') + '</b>');
                        $("#decline").parent().text("");
                     }
                });
	});
})
