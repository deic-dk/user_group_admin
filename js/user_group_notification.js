$(document).ready(function() {
	
	$(document).on ("click", ".invite_div .accept", function () {
		var group = $(this).attr('group');
		var activityId = $(this).parent().attr('activity_id');
		var user = $(this).attr('user');
		var userDisplayName = $(this).attr('userdisplayname');
		$(this).parent().html('<span group="' + group + '"><strong>'+t('user_group_admin', 'Accepted')+'</strong></span>');
		markSeen(activityId);
		$.ajax({
			url:OC.linkTo('user_group_admin', 'ajax/acceptinvitation.php'),
			type: 'post',
			data: { 'group': $(this).attr('group'), 'user':  user, 'accept': 'yes'},
			success: function(data, status) {
				var msg = t('user_group_admin', "Congratulations! You've joined the group")+' '+group;
				if(typeof user != 'undefined' && user != OC.currentUser){
					msg = userDisplayName+' '+t('user_group_admin', 'has joined the group')+' '+group+'.';
				}
				OC.dialogs.alert(msg, t('user_group_admin', 'New member of group'), function(el){
					OC.redirect(OC.generateUrl('apps/user_group_admin'));
				}, true);
			}
		});
	});
	
	$(document).on ("click", ".invite_div .decline", function () {
		var group = $(this).attr('group');
		var activityId = $(this).parent().attr('activity_id');
		$(this).parent().html('<span group="' + group + '"><strong>'+t('user_group_admin', 'Rejected')+'</strong></span>');
		markSeen(activityId);
		$.ajax({
			url:OC.linkTo('user_group_admin', 'ajax/acceptinvitation.php'),
			type: 'post',
			data: {'group': $(this).attr('group'), 'user':  $(this).attr('user'), 'accept': 'no'},
			success: function(data, status) {
				OC.dialogs.alert(t('user_group_admin', 'Declining membership of')+' '+group+'.', t('user_group_admin', 'Decline confirmation'), null, true);
			}
		});
	});
	
	function getMasterURL(callback, group, user){
		$.ajax(OC.linkTo('files_sharding', 'ajax/get_master_url.php'), {
			type: 'GET',
			success: function(data){
				if(data) {
					callback(data.url, group, user);
				}
			},
			error: function(data) {
				alert("Unexpected error!");
			}
		});
	}
	
	function redirectToMaster(masterUrl, group, user){
		OC.redirect(masterUrl+'/apps/user_group_admin/external_collaborator_verify.php' +
				'?group=' + group+  '&user=' + user);
	}
	
	$(document).on ("click", ".invite_div .verify", function () {
		var group = $(this).attr('group');
		var user = $(this).attr('user');
		var activityId = $(this).parent().attr('activity_id');
		markSeen(activityId);
		getMasterURL(redirectToMaster, group, user);
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

