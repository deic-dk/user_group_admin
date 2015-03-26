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
		            var theint = parseInt($("td[class='"+OC.UserGroup.groupSelected+"']").find("a#nomembers").html(),10)
			    theint++;
			    $("td[class='"+OC.UserGroup.groupSelected+"']").find("a#nomembers").text(theint);
			    $("td[class='"+OC.UserGroup.groupSelected+"']").find('#dropdown').html(jsondata.data.page);	
                            OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER].push(member);
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
      // make the following an ajax call
      $.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : $('.editgroup').val(), action: "addgroup" } , function ( jsondata ){
        if(jsondata.status == 'success' ) {
          //$('#own').html(jsondata.data.page)
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

//not useful right now	/////////////////////////////
$('#filestable tbody tr td .fileselect').live('click', function() {  
	var groupSelected = $(this).attr('id') ;
	var status = $(this).closest('tr').attr('id') ;
	window.alert(status);
	window.alert(groupSelected);
	$('span.selectedActions').toggle();	
	if (status == 'owner') {
	$("#removegroup").live('click', function() { 
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : groupSelected , action : "delgroup"} , function ( jsondata ){				if(jsondata.status == 'success' ) {
			 	window.reload();	
      			}else{
        			OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
      			}           
    		});

	});
	}else {
		$.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { group : groupSelected , action : "leavegroup"} , function ( jsondata ){
      if(jsondata.status == 'success' ) {
	window.reload();	
      }else{
        OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
      }           
    });
}
  }); 
////////////////////////////////////////////////////

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


$("#filestable td #removegroup").live('click', function() {
        var status = $(this).closest('tr').attr('id') ;
	var groupSelected = $(this).closest('td').attr('id') ;
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

});

$("#filestable td #exportgroup").live('click', function() { 
        var groupSelected = $(this).closest('td').attr('id') ;
	document.location.href = OC.linkTo('user_group_admin', 'ajax/export.php') + '?group=' + groupSelected;
});
 
$("#invite").live('click', function(event) {
	OC.UserGroup.groupSelected = $(this).closest('td').attr('id') ;
	//$(html).appendTo( $('#filestable tr').find('td#'+OC.UserGroup.groupSelected) );
	$("div[class='"+OC.UserGroup.groupSelected+"']").toggle();
	OC.UserGroup.initDropDown() ;
	event.stopPropagation();
	$('html').click(function(event) {
          if ( !$(event.target).closest("div[class='"+OC.UserGroup.groupSelected+"']").length ) {
		$("div[class='"+OC.UserGroup.groupSelected+"']").hide();
          }
     });

			  
} );

$(" .nomembers").live('click', function(event) {
	$('.drop', this).toggle();
	event.stopPropagation();
	var i=true;
	$('html').click(function(event) {
             //Hide the menus if visible
             //if (!$(event.target).closest('#dropdown').length) {
          if (i==true) {
             $(".drop").hide();
          }
     });

});

$('#filestable td .removemember').live('click', function() {   
    OC.UserGroup.groupSelected = $(this).closest('td').attr('class') ;
    var container = $(this).parents('li').first();
    var member    = container.data('member');
    $.post(OC.filePath('user_group_admin', 'ajax', 'actions.php'), { member : member , group : OC.UserGroup.groupSelected , action : "delmember"} , function ( jsondata ){
      if(jsondata.status == 'success' ) {
        container.remove();
		var theint = parseInt($("td[class='"+OC.UserGroup.groupSelected+"']").find(" a#nomembers").html(),10)
	    theint--;
		$("td[class='"+OC.UserGroup.groupSelected+"']").find("a#nomembers").text(theint);
        var index = OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER].indexOf(member);
        OC.UserGroup.groupMember[OC.Share.SHARE_TYPE_USER].splice(index, 1);
			var html = '<div><i>No members yet</i></div>';
//			$(html).append($('#filestable tr').find('td#'+OC.UserGroup.groupSelected));
      }else{
        OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
      }           
    });

    $('.tipsy').remove();

  });
$('#import_group_file').change(function() {
      $('#import_group_form').submit(); 
});
$("#browse").click(function () {
    $("#test").click();
})

$("#save").click(function () {
var formData = new FormData($('#testfile')[0]);
            $.ajax({
                url: OC.filePath('user_group_admin', 'ajax', 'import.php'),  //server script to process data
                type: 'POST',
                xhr: function() {  // custom xhr
                    myXhr = $.ajaxSettings.xhr();
                    if(myXhr.upload){ // if upload property exists
                      //  myXhr.upload.addEventListener('progress', progressHandlingFunction, false); // progressbar
                    }
                    return myXhr;
                },
                //Ajax events
                success:  function(jsondata) {
			window.alert('ok');
                },
                error:  function() {
                    alert("Något gick fel");
                },
                // Form data
                data: formData,
                //Options to tell JQuery not to process data or worry about content-type
                cache: false,
                contentType: false,
                processData: false
            }, 'json');

});

$("#clear").click(function () {
     $('#testfile').val('');
})

$('#test').change(function () {
    $('#testfile').val($(this).val());
})
	  

});
