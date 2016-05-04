$(document).ready(function() {
	$(document).on ("click", ".invite_div .accept", function () {
		var group = $(this).attr('group');
		var activityId = $(this).parent().attr('activity_id');
		$(this).parent().html('<span group="' + group + '"><strong>Accepted</strong></span>');
		markSeen(activityId);
		$.ajax({
			url:OC.linkTo('user_group_admin', 'ajax/acceptinvitation.php'),
			type: 'post',
			data: { 'group': $(this).attr('group'), 'accept': 'yes'},
			success: function(data, status) {
				OC.dialogs.alert('Congratulations! You\'ve joined the group '+group, 'Welcome to group', function(el){
					OC.redirect(OC.generateUrl('apps/user_group_admin'));
				}, true);
			}
		});
	});
	
	$(document).on ("click", ".invite_div .decline", function () {
		var group = $(this).attr('group');
		var activityId = $(this).parent().attr('activity_id');
		$(this).parent().html('<span group="' + group + '"><strong>Rejected</strong></span>');
		markSeen(activityId);
		$.ajax({
			url:OC.linkTo('user_group_admin', 'ajax/acceptinvitation.php'),
			type: 'post',
			data: {'group': $(this).attr('group'), 'accept': 'no'},
			success: function(data, status) {
				OC.dialogs.alert('OK, not joining '+group, 'Decline confirmation', null, true);
			}
		});
	});
	
})

function markSeen(activityId){
	$.ajax({
		type: 'GET',
		url: OC.filePath('user_notification', 'ajax', 'seen.php'),
		data: {activity_id : activityId}, 
		cache: false,
		success: function(){
		}
		});
}

/**
 * Check if accept/decline dialogs in notifications dropdown have been answered. If not, reset priority to veryhigh and have them reappear.
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
				$('.num-notifications').text(acceptArr.length);
				}
			});
	}
	
}

