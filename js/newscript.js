/**
 * This applies to the new version of My Groups
 */

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
				var intnew = parseInt($(".memberscount").html(),10)
                                intnew++;
                                $(".memberscount").text(intnew);
                            OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER].push(member);
			    $.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {group: OC.UserGroup.groupSelected, action : "showmembers"} ,
                function ( jsondata ){
                        if(jsondata.status == 'success' ) {
                                $('.dropnew').css('display', 'block');
                                $('.dropmembers').html(jsondata.data.page);

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

	$('#ok').on('click', function() {
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

	$('#cancel').click(function() {
		$('#newgroup').slideToggle();
	});



	$(".dropdown-toggle").live('click', function() {
		var groupSelected = $(this).closest('td').attr('id') ;
		$("[id='"+groupSelected+"']").find(" div.fileactions").find("ul").toggle();
		$("[id='"+groupSelected+"']").find(' div.fileactions').find("ul").hover(
				function () {
					$(this).show();
				},
				function () {
					$(this).hide();
				}
		);
	});

	$("#groupstable td #removegroup").live('click', function() {
		var status = $(this).closest('tr').attr('id') ;
		var groupSelected = $(this).closest('td').attr('id') ;
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

	$("#groupstable td #exportgroup").live('click', function() {
		var groupSelected = $(this).closest('td').attr('id') ;
		document.location.href = OC.linkTo('user_group_admin', 'ajax/export.php') + '?group=' + groupSelected;
	});

	$("#invite").live('click', function(event) {
		OC.UserGroup.groupSelected = $(".oc-dialog-title").children('span').attr('id') ;
		//$("div[class='"+OC.UserGroup.groupSelected+"']").toggle();
		$(".userselect").css("display", "block");
		OC.UserGroup.initDropDown() ;
		event.stopPropagation();
		$('html').click(function(event) {
			if ( !$(event.target).closest("div[class='userselect']").length)  {
				if (!$(event.target).closest(".ui-corner-all").length) {
					$("div[class='userselect']").show();
				}else {
					$("div[class='userselect']").hide();
				}
			}
		});
	});

$(" .name").live('click', function() {
		 var group = $(this).closest('td').attr('id') ;
		var itext = '<div class="itext">Select a group</div>';
		var number = $("td[class='"+group+"']").find("span#nomembers").html();
	var html = '<div><span id="tagid" class=""><h3 class="oc-dialog-title">Team <span id=\''+ group+'\'>\''+ group+'\'</span></h3></span><a class="oc-dialog-close close svg"></a><div id="meta_data_container">\
				<span class="memberscount">'+number+'</span> members<div id="emptysearch"></div><ul id="meta_data_keys"></ul></div><div class="dropmembers" style="text-align:center;"></div>\
          <div style="position:absolute;bottom:1;left:0;" ><button id="invite" class="invite btn btn-primary btn-flat"><i class="icon-user"></i>Invite user</button></div><div class="userselect" style="width:50%; margin: 0 auto; display:none;"><input id="mkgroup" type="text" placeholder="Invite user ..." class="ui-autocomplete-input" autocomplete="off">\
                        <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span></div>\
                        </div>';
			

			$(html).dialog({
			  dialogClass: "oc-dialog",
			  resizeable: false,
			  draggable: false,
			  height: 600,
			  width: 720 
			});
			 $('.oc-dialog-close').live('click', function() {
				$(".oc-dialog").hide();
        });
		$('.ui-helper-clearfix').css("display", "none");
	if ($(this).closest('tr').attr('id')=='owner'){ 
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {group: group, action : "showmembers"} ,
                function ( jsondata ){
                        if(jsondata.status == 'success' ) {
				$('.dropnew').css('display', 'block');
				$('.dropmembers').html(jsondata.data.page);

                        }else{
                                OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
                        }
		});
	}
	else if ($(this).closest('tr').attr('id')=='member') {
		 $.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), {group: group, action : "showmemberships"} ,
                function ( jsondata ){
                        if(jsondata.status == 'success' ) {
                                $('.dropnew').css('display', 'block');
                                $('.dropmembers').html(jsondata.data.page);
                        }else{
                                OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
                        }
                });
		$('.invite').hide();
	
	}
                $('html').click(function(event) {
        if ( !$(event.target).closest(".dropnew").length && !$(event.target).closest(".text-right").length && !$(event.target).closest(".ui-corner-all").length) {
		$('.dropnew').html(itext);
        }
                });
	});	


	$(' .removemember').live('click', function() {
		group = $(".oc-dialog-title").children('span').attr('id') ; 
		var container = $(this).parents('li').first();
		var member    = container.data('member');
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { member : member , group : group , action : "delmember"} , function ( jsondata ){
			if(jsondata.status == 'success' ) {
				container.remove();
				var theint = parseInt($(".memberscount").html(),10)
				theint--;
				$(".memberscount").text(theint);
				var int2 = parseInt($("td[class='"+group+"']").find("span#nomembers").html(),10)
                            	int2--;
                            	$("td[class='"+group+"']").find("span#nomembers").text(int2);
			//	var index = OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER].indexOf(member);
				//OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER].splice(index, 1);				
			}else{
				OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
			} 
		});

		$('.tipsy').remove();

	});
	$('#import_group_file').change(function() {
		$('#import_group_form').submit();
	});

	$('.oc-dialog-close').live('click', function() {
		//$( this ).dialog( "close" );
	});

});

