OC.GroupCustom = {

    groupSelected : '' ,
    groupMember : [] ,

    newGroup : function ( ) {

        var group = $("#new_group").val().trim();

        $('#new_group_dialog').dialog('destroy').remove();

        OC.GroupCustom.groupSelected = group ;

        $.post(OC.filePath('group_custom', 'ajax', 'addgroup.php'), { group : group } , function ( jsondata ){
            if(jsondata.status == 'success' ) {

                $('#.roup_left').html(jsondata.data.page)

            }else{
                OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
            }           
        });

    } ,

    doExport:function( group ) {
            document.location.href = OC.linkTo('group_custom', 'ajax/export.php') + '?group=' + group;
    },

    initDropDown : function() {

        OC.GroupCustom.groupMember[OC.Share.SHARE_TYPE_USER]  = [];
        OC.GroupCustom.groupMember[OC.Share.SHARE_TYPE_GROUP] = [];

        $('#mkgroup').autocomplete({
            minLength : 2,
            source : function(search, response) {
                $.get(OC.filePath('group_custom', 'ajax', 'members.php'), {
                    fetch : 'getShareWith',
                    search : search.term,
                    itemShares : [OC.GroupCustom.groupMember[OC.Share.SHARE_TYPE_USER], OC.GroupCustom.groupMember[OC.Share.SHARE_TYPE_GROUP]]
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
            $.post(OC.filePath('group_custom', 'ajax', 'addmember.php'), { member : member , group : OC.GroupCustom.groupSelected } , function ( jsondata ){
              if(jsondata.status == 'success' ) {
                $('#mkgroup').val('');
                OC.GroupCustom.groupMember[OC.Share.SHARE_TYPE_USER].push(member);
                $('#group_right').html(jsondata.data.page);
                OC.GroupCustom.initDropDown() ;
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


  $('#create_group').click(function() {
    $('#group_custom_holder').show();
    $('#newgroup').focus();
  });

  $('#newgroup').on('focusout', function() {  
    if( $('#newgroup').val() != "") { 
      // make the following an ajax call
      $.post(OC.filePath('group_custom', 'ajax', 'addgroup.php'), { group : $('#newgroup').val() } , function ( jsondata ){
        if(jsondata.status == 'success' ) {

          $('#own').html(jsondata.data.page)
        $('#newgroup').val("");
      $('#group_custom_holder').hide();

        }else{
          OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
        }           
      });
    } else {
      $('#group_custom_holder').hide();
    }
  });     





  $('#import_group_file').change(function() {
    $('#import_group_form').submit();
  });



  $('.group_left ul li').live('click', function() {  
    OC.GroupCustom.groupSelected = $(this).data('group') ;

    $.getJSON(OC.filePath('group_custom', 'ajax', 'group.php'),{ group: OC.GroupCustom.groupSelected },function(jsondata) {
      if(jsondata.status == 'success') {
        $('#group_right').html(jsondata.data.page)
        OC.GroupCustom.initDropDown() ;
        for (var i = 0 ; i <= jsondata.data.members.length - 1 ; i++ ) {
          OC.GroupCustom.groupMember[ OC.Share.SHARE_TYPE_USER ].push( jsondata.data.members[i] ) ;
        };
      }
    }) ;

  });    






  $('.member-actions > .remove.member').live('click', function() {   

    var container = $(this).parents('li').first();
    var member    = container.data('member');

    $.post(OC.filePath('group_custom', 'ajax', 'delmember.php'), { member : member , group : OC.GroupCustom.groupSelected } , function ( jsondata ){
      if(jsondata.status == 'success' ) {
        container.remove();
        var index = OC.GroupCustom.groupMember[OC.Share.SHARE_TYPE_USER].indexOf(member);
        OC.GroupCustom.groupMember[OC.Share.SHARE_TYPE_USER].splice(index, 1);
      }else{
        OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
      }           
    });

    $('.tipsy').remove();

  });

  $('.group-actions > .remove.group').live('click', function( event ) {   

    var container = $(this).parents('li').first();

    var group     = container.data('group');
    event.stopPropagation();

    $.post(OC.filePath('group_custom', 'ajax', 'delgroup.php'), { group : group } , function ( jsondata ){
      if(jsondata.status == 'success' ) {
        container.remove();
        $('#group_right').html('');
      }else{
        OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
      }           
    });

    $('.tipsy').remove();

  });

  // Function added by Christian Brinch

  $('.group-actions > .leave.group').live('click', function( event ) {   
    var container = $(this).parents('li').first();

    var group     = container.data('group');
    event.stopPropagation();

    $.post(OC.filePath('group_custom', 'ajax', 'leavegroup.php'), { group : group } , function ( jsondata ){
      if(jsondata.status == 'success' ) {
        container.remove();
        $('#group_right').html('');
      }else{
        OC.dialogs.alert( jsondata.data.message , jsondata.data.title ) ;
      }           
    });

    $('.tipsy').remove();

  });

  $('.group-actions > .export.group').live('click', function( event ) {   

    $('.tipsy').remove();

    var container = $(this).parents('li').first();
    var group     = container.data('group');

    OC.GroupCustom.doExport( group ) ;
    return false;

  });

  $('#add_member').live('click', function(){
    $('#mkgroup_li').show();
    $('#mkgroup').focus();
  });

  $('#mkgroup').live('blur', function() {
    if( $('#mkgroup').val() == ""){
      $('#mkgroup_li').hide();
    }
  });

});
