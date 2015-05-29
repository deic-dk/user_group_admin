$(document).ready(function() {
	$(document).on ("click", "#accept", function () {
		$.ajax({
                     url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                     type: 'post',
                     data: { 'group': $(this).attr('value'), 'action': 'acceptinvitation'},
                     success: function(data, status) {
			$("#accept").parent().parent().html('You joined group <a class="filename" href="/index.php/apps/user_group_admin">'+$("#accept").attr('value')+'</a>');
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
			$("#decline").parent().parent().html('You rejected an invitation to group <a class="filename" href="/index.php/apps/user_group_admin">'+$("#decline").attr('value') + '</a>');
                        $("#decline").parent().text("");
                     }
                });
	});
})
