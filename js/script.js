OC.UserGroup = {
	groupSelected : '' ,
	groupMember : [] ,
	initDropDown : function() {
		OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER]  = [];
		OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_GROUP] = [];

		$('.userselect .ui-autocomplete-input').autocomplete({
			minLength : 2,
			source : function(search, response) {
				$.get(OC.filePath('user_group_admin', 'ajax', 'members.php'), {
					search : search.term,
					itemShares : [OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER], OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_GROUP]]},
					function(result) {
						if(result.status == 'success' && result.data.length > 0) {
							response(result.data);
						}
					});
			},
			focus : function(event, focused) {
				event.preventDefault();
			},
			select : function(event, selected) {
				var member = selected.item.value.shareWith;
				$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {member : member, group : OC.UserGroup.groupSelected, action : "addmember"} ,
					function ( jsondata ){
						if(jsondata.status == 'success' ) {
							$('.userselect .ui-autocomplete-input').val('');
							var theint = parseInt($("tr[group='"+OC.UserGroup.groupSelected+"']").find("span#nomembers").html(),10);
							theint++;
							$("tr[group='"+OC.UserGroup.groupSelected+"']").find("span#nomembers").text(theint);
							$("tr[group='"+OC.UserGroup.groupSelected+"']").find('.dropmembers').html(jsondata.data.page);
							var intnew = getMembersCount(OC.UserGroup.groupSelected);
							intnew++;
							setMembersCount(OC.UserGroup.groupSelected, intnew);
							$("div[class='userselect']").show();
							OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER].push(member);
							$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {group: OC.UserGroup.groupSelected, action : "showmembers"} ,
							function ( jsondata ){
								if(jsondata.status == 'success' ) {
									$('.dropmembers').html(jsondata.data.page);
									$("div[class='userselect']").show();
									$('.avatar').each(function() {
										var element = $(this);
										element.avatar(element.data('user'), 28);
									});
								}
								else{
									OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
									}
							});
							OC.UserGroup.initDropDown() ;
						}
						else{
							OC.dialogs.alert( jsondata.data.message , jsondata.data.title );
						}
					});
					return false;
			}
		});
	},
	
	initGroupDropDown : function() {
		$('.ui-autocomplete-group').autocomplete({
			minLength : 1,
			source : function(search, response) {
				$.get(OC.filePath('user_group_admin', 'ajax', 'searchgroups.php'), {
					search : search.term},
					function(result) {
						var data = [];
						$.each(result, function(key, value){
							data.push({'label': value.gid, 'value': value});
						});
						if(data.length > 0) {
							response(data);
						}
					});
			},
			focus : function(event, focused) {
				event.preventDefault();
			},
			select : function(event, selected) {
				var gid = selected.item.value.gid;
				$('#joingroup .editgroup').val(selected.item.value.gid);
				$('.group-info').remove();
				$('#joingroup .editgroup').after('<a href=# class="group-info" group="'+selected.item.value.gid+'" owner="'+
						selected.item.value.owner+'" members="'+selected.item.value.members+'" ownerDisplayName="'+
						selected.item.value.ownerDisplayName+'">info</a>')
				return false;
			}
		});
	},
	
	joinGroup: function(gid){
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), 
				{member : OC.currentUser, group : gid, action : "addmember"} ,
				function ( jsondata ){
					if(jsondata.status == 'success' ) {
						$('#joingroup .ui-autocomplete-input').val('');
						var theint = parseInt($("tr[group='"+gid+"']").find("span#nomembers").html(),10);
						theint++;
						$("tr[group='"+gid+"']").find("span#nomembers").text(theint);
						var intnew = getMembersCount(gid);
						intnew++;
						setMembersCount(gid, intnew);
						$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {group: gid, action : "showmembers"} ,
						function ( jsondata ){
							if(jsondata.status == 'success' ) {
								$('#joingroup .editgroup').val("");
								$('.group-info').remove();
								location.reload();
							}
							else{
								OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
								}
						});
					}
					else{
						OC.dialogs.alert( jsondata.data.message , jsondata.data.title );
					}
			});
	},
	
	sendInvitationEmail: function(email, group){
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), 
				{email: email, group: group, action: "addmember"} ,
				function ( jsondata ){
					if(jsondata.status != 'success' ) {
						OC.dialogs.alert( jsondata , 'Problem' );
					}
					else{
						// Inform about success
						$('.group .invitemembers .emailaddresses input').val('');
						var oldPlaceholder = $('.group .invitemembers .emailaddresses input').attr('placeholder');
						$('.group .invitemembers .emailaddresses input').attr('placeholder', 'Email was sent!');
						$('.group .invitemembers .emailaddresses input').click(function(ev){
							$(ev.target).attr('placeholder', oldPlaceholder);
						});
					}
			});
	},
	
	onFreeQuotaSelect: function(ev) {
		var $select = $(ev.target);
		var group = $select.attr('group');
		var freeQuota = $select.val();
		OC.UserGroup._updateFreeQuota(group, freeQuota, function(returnedFreeQuota){
			if (freeQuota !== returnedFreeQuota) {
				$select.find(':selected').text(returnedFreeQuota);
				}
			});
		},
	_updateFreeQuota: function(group, freeQuota, ready) {
		$.post(
				OC.filePath('files_accounting', 'ajax', 'setFreeQuota.php'),
				{group: group, freequota: freeQuota},
				function (result) {
					if (ready) {
						ready(result.freequota);
						}
					}
				);
		}
};

function getMembersCount(group){
	return parseInt($("div[group='"+group+"']").find(".memberscount").attr('members'),10);
}

function setMembersCount(group, n){
	$("div[group='"+group+"']").find(".memberscount").attr("members", n);
			$("div[group='"+group+"']").find(".memberscount").text(
				''+n+' member'+(n==1?'':'s')
			);
}

function showMembers(group, role, info){
	var html = '<div class="group"><div class="grouptitle" group="'+group+'">'+ group+'</div>\
			<a class="oc-dialog-close close svg"></a><div class="memberlist">\
			<div class="dropmembers" group=\''+ group+'\'></div>'+
			(!(role=='owner' || role=='admin')?'':
			'<div class="invitemembers">\
				<button id="invite" class="btn btn-primary btn-flat">\
			<i class="icon-user"></i>Invite user</button>&nbsp\
				<button id="invite-guests" class="btn btn-default btn-flat">\
				Invite via email</button><br />\
				<div class="userselect">\
				<input type="text" placeholder="Search users" class="ui-autocomplete-input" autocomplete="off">\
				<span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>\
				</div>\
			<div class="emailaddresses">\
				<input type="text" placeholder="Email of person who has never logged in">\
				<button id="send-invite" class="btn btn-default btn-flat" group=\"'+ group+'\">Send</button>\
				</div>\
				<br />\
			<button id="export-group" class="btn btn-default btn-flat">\
			<i class="icon-export-alt"></i>Export</button>\
			<div class="freequota"></div>\
			</div>')+
			'</div>';

	$(html).dialog({
		dialogClass: "oc-dialog",
		resizable: true,
		draggable: true,
		height: (info?300:600),
		width: (info?400:720)
	});

	$('body').append('<div class="modalOverlay">');

	$('.oc-dialog-close').live('click', function(ev) {
		if($('.ui-dialog .group .invitemembers').length && $('.group textarea.description').length &&
				!$('.group textarea.description').is('[readonly]')){
			saveDescription();
		}
		ev.target.closest(".oc-dialog").remove();
		$('.modalOverlay').remove();
	});

	$('.ui-helper-clearfix').css("display", "none");
	if (role=='owner' || role=='admin' || role=='member' || role=='pending'){
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'),
			{group: group, action : "showmembers"} ,
			function ( jsondata ){
				if(jsondata.status == 'success' ) {
					$('.dropmembers').html(jsondata.data.page);
					if(role=='owner' || role=='admin'){
						$('.dropmembers textarea').removeAttr('readonly');
					}
					$('.freequota').html(jsondata.data.freequota);
					$('#setfreequota').singleSelect().on('change', OC.UserGroup.onFreeQuotaSelect);
					$('.avatar').each(function() {
						var element = $(this);
						element.avatar(element.data('user'), 28);
					});
					if (role=='member') {
						$('.removemember').hide();
					}
				}
				else{
					OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
				}
		});
	}
	else if(info){
		$('.dropmembers').append(info);
	}
	if (role=='member') {
		$('.invite').hide();
		$('.freequota').hide();
	}
}

function saveDescription(){
	$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'),
			{ group : $('.group .grouptitle').attr('group'), action: "setdescription", description : $('.group textarea.description').val() } ,
			function ( jsondata ){
		if(jsondata.status != 'success' ) {
			OC.dialogs.alert( jsondata) ;
		}
	});
}

function sendInvite(group){
	var myEmails = $('.invitemembers .emailaddresses input').val();
	var myEmailList = myEmails.match(/[a-zA-z0-9_.]+@[a-zA-Z0-9_.]+\.(.*)/g);
	if(myEmailList == null || myEmailList.length == 0) {
		OC.dialogs.alert('Cannot parse this input.' , 'Bad email address(es)');
		$('.grouptitle').parent().parent().css('z-index', '100');
		$('#send_emails').parent().parent().parent().css('z-index', '200');
		return false;
	}
	var myEmailListString = myEmailList.join(', ');
	var textHtml = "Inviting via email is intended to allow sharing with users who have not yet signed in.  " +
			"When clicking on the received link, they will first be asked to sign in, then added to the group.<br /><br />" +
			"Emails will now be sent to the following recipients:<br /><br />"+myEmailListString;
	$('#dialogalert' ).html(textHtml);
	// This gets hidden when showing alerts...
	$( '#dialogalert' ).parent().find('.ui-dialog-titlebar').show();
	$( '#dialogalert' ).parent().find('.ui-dialog-buttonpane').show();
	$( '#dialogalert' ).dialog({
		buttons: [ 
	{ id:'send_emails', text: 'Send', click: function() {
		$.each(myEmailList, function(key, value){
			OC.UserGroup.sendInvitationEmail(value, group);
		});
		$(this).dialog( 'close' );
	}},
	{id:'send_emails_cancel', text: 'Cancel',
		click: function() {
			$(this).dialog( 'close' );
		}
	}]});
	$('.grouptitle').parent().parent().css('z-index', '100');
	$('#send_emails').parent().parent().parent().css('z-index', '200');
}

$(document).ready(function() {
	
	$('a#create').click(function() {
		$('#newgroup').slideToggle();
	});
	
	$('a#join').click(function() {
		$('#joingroup').slideToggle();
	});
	
	$('a#importgroup').click(function() {
		$('#importnew').slideToggle();
	});

	$('#newgroup #ok').on('click', function() {
		if($('#newgroup .editgroup').val() != "") {
			$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : $('#newgroup .editgroup').val(), action: "addgroup" } , function ( jsondata ){
				if(jsondata.status == 'success' ) {
					$('#newgroup').slideToggle();
					$('#newgroup .editgroup').val("");
					$('#user_group_admin_holder').hide();
					location.reload();
				}else{
					OC.dialogs.alert( jsondata.data.message , jsondata.data.title );
				}
			});
		} else {
			$('#user_group_admin_holder').hide();
		}
	});
	
	$('#joingroup #join_group').on('click', function() {
		
		if( $('#joingroup .editgroup').val() == "") {
			$('#user_group_admin_holder').hide();
			return false;
		}
		var textHtml = "A request for membership will now be sent to the group owner. Click 'Join' to proceed.";
		$( '#dialogalert' ).html(textHtml);
		// This gets hidden when showing alerts...
		$('.ui-dialog-titlebar').show();
		$('.ui-dialog-buttonpane').show();
		$('#dialogalert').dialog({ buttons: [ { id:'join_group', text: 'Join', click: function() {
			OC.UserGroup.joinGroup($('#joingroup .editgroup').val());
			$(this).dialog( 'close' );
		}},
		{id:'join_group_cancel', text: 'Cancel',
			click: function() {
				$(this).dialog( 'close' );
			}
		}]});
		
	});

	$('#newgroup #cancel').click(function() {
		$('#newgroup').slideToggle();
	});
	
	$('#joingroup #cancel_join').click(function() {
		$('#joingroup .editgroup').val("");
		$('.group-info').remove();
		$('#joingroup').slideToggle();
	});

	$("#groupstable td .delete-group").live('click', function(ev) {
		ev.stopPropagation();
		var role = $(this).closest('tr').attr('role') ;
		var groupSelected = $(this).closest('tr').attr('group') ;
		var textHtml = $( '#dialogalert' ).html().replace( role == 'owner'?'leave':'delete', role == 'owner'?'delete':'leave');
		 $('#dialogalert').html(textHtml);
		$('#dialogalert').dialog({ buttons: [ { id:'delete_leave_group', text: role == 'owner'?'Delete':'Leave', click: function() {
			if (role == 'owner' || role == 'admin') {
				$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : groupSelected , action : "delgroup"} , function ( jsondata){
					if(jsondata.status == 'success' ) {
						location.reload();
					}
					else{
						OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
						}
					});
			}
			else{
				$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : groupSelected , action : "leavegroup"} , function ( jsondata){
					if(jsondata.status == 'success' ) {
						location.reload();
					}
					else{
						OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
					}
				});
			}
			$(this).dialog( 'close' );
		}},
		{id:'delete_leave_group_cancel', text: 'Cancel',
			click: function() {
				$(this).dialog( 'close' );
			}
		}]});
	});	

	$("#export-group").live('click', function(e) {
		e.preventDefault();
		var groupSelected = $('.grouptitle').attr('group');
		OC.redirect(OC.linkTo('user_group_admin', 'ajax/export.php') + '?group=' + groupSelected);
	});

	$("#invite").live('click', function(event) {
		OC.UserGroup.groupSelected = $('.grouptitle').attr('group');
		$(".userselect").css("display", "block");
		OC.UserGroup.initDropDown() ;
		$('.userselect .ui-autocomplete-input').focus();
		event.stopPropagation();
		$('html').click(function(event) {
			if ( !$(event.target).closest("div[class='userselect']").length )  {
					$("div[class='userselect']").hide();
			}
		});
	});
	
	$("#invite-guests").live('click', function(event) {
		OC.UserGroup.groupSelected = $('.grouptitle').attr('group');
		$(".emailaddresses").css("display", "block");
		event.stopPropagation();
		$('html').click(function(event) {
			if($(event.target).closest(".oc-dialog").find('.group').length &&
					!$(event.target).closest("div[class='emailaddresses']").length)  {
					$("div[class='emailaddresses']").hide();
			}
		});	});


	$(document).click(function(e){
		if ($(".oc-dialog").length &&
				!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.ui-dialog').length &&
				!$(e.target).parents().filter('.name').length ) {
			if($('.ui-dialog .group .invitemembers').length && $('.group textarea.description').length &&
					!$('.group textarea.description').is('[readonly]')){
				saveDescription();
			}
			$(".oc-dialog").remove();
			$('.modalOverlay').remove();
			$('#dialogalert').closest('.ui-dialog').remove();
		}
		else if($(e.target).attr('group') && $(e.target).hasClass('group-info')){
			showMembers($(e.target).attr('group'), '',
					'<div class="info">Description: '+($(e.target).attr('description')||'')+'</div>'+
					'<div class="info">Owner: '+$(e.target).attr('ownerDisplayName')+'</div>'+
					'<div class="info">Members: '+$(e.target).attr('members')+'</div>');
		}
		else if($(e.target).prop('id') && $(e.target).prop('id')=='send-invite'){
			sendInvite($(e.target).attr('group'));
		}
	});

	$("#groupstable .nametext").live('click', function() {
		var group = $(this).closest('tr').attr('group') ;
		var role = $(this).closest('tr').attr('role');
		var hidden = $(this).closest('tr').attr('hiddenGroup');
		if(hidden && role!='owner'){
			showMembers(group, '',
					'<div class="info">Description: <i>This is a system group</i></div>'+
					'<div class="info">Owner: <i>hidden</i></div>');
		}
		else{
			showMembers(group, role);
		}
	});

	$(' .removemember').live('click', function() {
		group = $('.grouptitle').attr('group');
		var container = $(this).parents('li').first();
		var member    = container.data('member');
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { member : member , group : group , action : "delmember"} , function ( jsondata ){
			if(jsondata.status == 'success' ) {
				container.remove();
				var theint = getMembersCount(group);
				theint--;
				setMembersCount(group, theint);
				var int2 = parseInt($("tr[group='"+group+"']").find("span#nomembers").html(),10)
				int2--;
				$("tr[group='"+group+"']").find("span#nomembers").text(int2);
			}
			else{
				OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
			}
		});
		$('.tipsy').remove();
	});

	$('#importnew #import_group_file').change(function() {
		$('#import_group_form').submit();
	});
	
	OC.UserGroup.initGroupDropDown() ;
	
	$('#revokeinvitation').live('click', function(ev) {
		var group = $(ev.target).attr('group');
		var user = $(ev.target).attr('user');
		OC.dialogs.confirm('Are you sure you want to revoke access for the user '+$(ev.target).attr('user')+'?', 'Confirm revoke',
        function(res){
  				if(res){
  					$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { user : user , group : group , action : "disableuser"} , function ( jsondata ){
  						if(jsondata.status == 'success' ) {
  							OC.dialogs.alert('Revoked access for user '+user ,'Success') ;
  						}
  						else{
  							OC.dialogs.alert(jsondata.data.message , jsondata.data.title) ;
  						}
  					});
  				}
        }
     );
	});
	
});

