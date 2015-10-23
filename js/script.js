OC.UserGroup = {
        groupSelected : '' ,
        groupMember : [] ,
        initDropDown : function() {
        OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER]  = [];
        OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_GROUP] = [];

        $('.ui-autocomplete-input').autocomplete({
            minLength : 2,
            source : function(search, response) {
                $.get(OC.filePath('user_group_admin', 'ajax', 'members.php'), {
                    fetch : 'getShareWith',
                    search : search.term,
                    itemShares : [OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER], OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_GROUP]]
                }, function(result) {
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
            $.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { member : member , group : OC.UserGroup.groupSelected , action : "addmember"} , function ( jsondata ){
              if(jsondata.status == 'success' ) {
                            $('.ui-autocomplete-input').val('');
                            var theint = parseInt($("td[class='"+OC.UserGroup.groupSelected+"']").find("span#nomembers").html(),10)
                            theint++;
                            $("td[class='"+OC.UserGroup.groupSelected+"']").find("span#nomembers").text(theint);
                            $("td[class='"+OC.UserGroup.groupSelected+"']").find('.dropmembers').html(jsondata.data.page);
				var intnew = parseInt($("div[class='"+OC.UserGroup.groupSelected+"']").find(".memberscount").html(),10)
                                intnew++;
                                $("div[class='"+OC.UserGroup.groupSelected+"']").find(".memberscount").text(intnew);
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


                        }else{
                                OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
                        }
                });
                            OC.UserGroup.initDropDown() ;
              }else{
                OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;

              }
            });

            return false;
          },
        });
    }
};

$(document).ready(function() {
	$('a#create').click(function() {
		$('#newgroup').slideToggle();
	});
	$('a#importgroup').click(function() {
		$('#importnew').slideToggle();
	});

	$('#newgroup #ok').on('click', function() {
		if( $('.editgroup').val() != "") {

			$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : $('.editgroup').val(), action: "addgroup" } , function ( jsondata ){
				if(jsondata.status == 'success' ) {
					$('#newgroup').slideToggle();
					$('#newgroup').val("");
					$('#user_group_admin_holder').hide();
					location.reload();
				}else{
					OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
				}
			});
		} else {
			$('#user_group_admin_holder').hide();
		}
	});

	$('#newgroup #cancel').click(function() {
		$('#newgroup').slideToggle();
	});

	$("#groupstable td #delete-group").live('click', function() {
		var status = $(this).closest('tr').attr('id') ;
                var groupSelected = $(this).closest('tr').attr('class') ;
                $( '#dialogalert' ).dialog({ buttons: [ { id:'test','data-test':'data test', text: 'Delete', click: function() {
                    if (status == 'owner') {
                        $.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : groupSelected , action : "delgroup"} , function ( jsondata){
                                if(jsondata.status == 'success' ) {
                                        location.reload();
                                }else{
                                        OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
                                }
                        });

                }else {
                        $.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : groupSelected , action : "leavegroup"} , function ( jsondata){

                                if(jsondata.status == 'success' ) {
                                        location.reload();
                                }else{
                                        OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
                                }
                        });
                }

                $(this).dialog( 'close' ); } },
                { id:'test2','data-test':'data test', text: 'Cancel', click: function() {
                $(this).dialog( 'close' ); } } ] });

        });	


	$("#export-group").live('click', function() {
		var groupSelected = $(this).parents('div').prev().attr('id');
		document.location.href = OC.linkTo('user_group_admin', 'ajax/export.php') + '?group=' + groupSelected;
	});

	$("#invite").live('click', function(event) {
		OC.UserGroup.groupSelected = $(this).parents('div').prev().attr('id');
		$(".userselect").css("display", "block");
		OC.UserGroup.initDropDown() ;
		event.stopPropagation();
		$('html').click(function(event) {
			if ( !$(event.target).closest("div[class='userselect']").length )  {
					$("div[class='userselect']").hide();
			}
		});
	});

	$(document).click(function(e){
          if (!$(e.target).parents().filter('.oc-dialog').length && !$(e.target).parents().filter('.name').length ) {
                $(".oc-dialog").hide();
		$('.modalOverlay').remove();
           }
        });

	$("#groupstable .name").live('click', function() {
		var group = $(this).closest('td').attr('id') ;
		var number = $("td[class='"+group+"']").find("span#nomembers").html();
		var html = '<div><span><h3 class="oc-dialog-title" style="padding-left:25px;">Team <span>\''+ group+'\'</span></h3></span><a class="oc-dialog-close close svg"></a><div id="meta_data_container" class=\''+ group+'\'>\
				<span class="memberscount" style="padding-left:25px;" >'+number+'</span> members <p></p><div class="dropmembers" id=\''+ group+'\' style="width:60%; margin: 0 auto;"></div>\
          <div style="position:absolute; bottom:50px; left:40px;" ><button id="invite" class="invite btn btn-primary btn-flat"><i class="icon-user"></i>Invite user</button>&nbsp<button id="export-group" class="btn btn-default btn-flat"><i class="icon-export-alt"></i>Export</button></div><div class="userselect" style="width:60%; padding-left:170px; display:none;"><input id="mkgroup" type="text" placeholder="Invite user ..." class="ui-autocomplete-input" autocomplete="off">\
                        <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span></div>\
                        </div>';

		$(html).dialog({
			  dialogClass: "oc-dialog",
			  resizeable: false,
			  draggable: false,
			  height: 600,
			  width: 720
			});

		$('body').append('<div class="modalOverlay">');

		$('.oc-dialog-close').live('click', function() {
			$(".oc-dialog").hide();
			$('.modalOverlay').remove();
        	});

		$('.ui-helper-clearfix').css("display", "none");
	    	if ($(this).closest('tr').attr('id')=='owner'){
			$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {group: group, action : "showmembers"} ,
                	function ( jsondata ){
                        	if(jsondata.status == 'success' ) {
					$('.dropmembers').html(jsondata.data.page);
					$('.avatar').each(function() {
                                		var element = $(this);
                                		element.avatar(element.data('user'), 28);
                        		});

                        	}else{
                                	OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
                        	}
			});
		}
		else if ($(this).closest('tr').attr('id')=='member') {
		 	$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {group: group, action : "showmemberships"} ,
                	function ( jsondata ){
                        	if(jsondata.status == 'success' ) {
                                	$('.dropmembers').html(jsondata.data.page);
					$('.avatar').each(function() {
                                        	var element = $(this);
                                        	element.avatar(element.data('user'), 28);
                                	});
                        	}else{
                                	OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
                        	}
                	});
			$('.invite').hide();

		}
	});


	$(' .removemember').live('click', function() {
		group = $(this).parents('div').attr('id');
		var container = $(this).parents('li').first();
		var member    = container.data('member');
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { member : member , group : group , action : "delmember"} , function ( jsondata ){
			if(jsondata.status == 'success' ) {
				container.remove();
				var theint = parseInt($("div[class='"+group+"']").find(".memberscount").html(),10)
				theint--;
				$("div[class='"+group+"']").find(".memberscount").text(theint);
				var int2 = parseInt($("td[class='"+group+"']").find("span#nomembers").html(),10)
                            	int2--;
                            	$("td[class='"+group+"']").find("span#nomembers").text(int2);
			}else{
				OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
			}
		});

		$('.tipsy').remove();

	});

	$('#importnew #import_group_file').change(function() {
		$('#import_group_form').submit();
	});


});

