$(document).ready(function() {
	$(document).on ("click", ".accept", function () {
		var group = $(this).attr('group');
		$(this).parent().html('<span group="' + group + '">Accepted</span>');
		$.ajax({
			url:OC.linkTo('user_group_admin', 'ajax/acceptinvitation.php'),
			type: 'post',
			data: { 'group': $(this).attr('group'), 'accept': 'yes'},
			success: function(data, status) {
				}
			});
		});
	
	$(document).on ("click", ".decline", function () {
		var group = $(this).attr('group');
		$(this).parent().html('<span group="' + group + '">Rejected</span>');
		$.ajax({
			url:OC.linkTo('user_group_admin', 'ajax/acceptinvitation.php'),
			type: 'post',
			data: {'group': $(this).attr('group'), 'accept': 'no'},
			success: function(data, status) {
			}
		});
	});
	
})

/**
 * Check if accept/decline dialogs in notifications dropdown have been answered. If not, reset priority to high and have them reappear.
 * We call this from the theme (deic-data.js), as binding to bootstrap events from here apparentlyt doesn't work.
 */
function checkAccept(){

	var acceptArr = $('.invite_div a.accept').map(function() {
    return $(this).parent().attr('activity_id');
 }).get();
		
	if(acceptArr && acceptArr.length>0){
		var AcceptJson = JSON.stringify(acceptArr);
		$.ajax({
			type: 'POST',
			url: OC.filePath('user_notification', 'ajax', 'unseen.php'),
			data: {activity_ids : AcceptJson}, 
			cache: false,
			success: function(){
				$('.bell').addClass('ringing');
				}
			});
	}
	
}

