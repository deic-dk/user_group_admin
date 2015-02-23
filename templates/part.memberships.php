<?php
    $groups = OC_User_Group_Admin_Util::getUserGroups(OC_User::getUser());
   //ioanna
        foreach ($groups as $group) {
	   $ingroup  = OC_User_Group_Admin_Util::searchUser($group, OC_User::getUser(), '1' );	
		$notification = OC_User_Group_Admin_Util::isNotified($group, OC_User::getUser(), '0', '0' );
		$verified = OC_User_Group_Admin_Util::acceptedUser($group, OC_User::getUser(), '0', $_GET['code'], '0' );
		$declined = OC_User_Group_Admin_Util::declinedUser($group, OC_User::getUser(), '0', $_GET['code'], '0' );
		$checkagain = OC_User_Group_Admin_Util::acceptedUser($group, OC_User::getUser(), '2', $_GET['code'], '0' );

	if ($ingroup) {

            echo "<li data-group=\"$group\"><i class=\"fa fa-users\"></i>".$group."
                <span class=\"group-actions\">
                    <a href=# class='action export group' original-title=" . $l->t('Export') . "><i class=\"fa fa-cloud-download\">-></i></a>
                    <a href=# class='action leave group' original-title=" . $l->t('Remove') . "><i class=\"fa fa-times\">x</i></a>
                </span></li>";}

		if ( $group != 'dtu.dk' ) {
			if ( $verified || $checkagain) {
       				echo "<script type='text/javascript'>
					window.alert(\"You have accepted the invitation to the following group:  $group\");

				</script>";
				$result=OC_User_Group_Admin_Util::acceptInvitation($group, OCP\USER::getUser());
				$status = OC_User_Group_Admin_Util::Notification(OCP\USER::getUser(), $group, $_GET['code']);
			
                	}elseif ( $declined  ) {
                        	echo "<script type='text/javascript'>
                                	window.alert(\"You have declined the invitation to the following group:  $group\");
                        	</script>";
                        	$result=OC_User_Group_Admin_Util::declineInvitation(OCP\USER::getUser(), $group);
		          	$status = OC_User_Group_Admin_Util::Notification(OCP\USER::getUser(), $group, $_GET['code']);

			}elseif ( $notification == true && isset($_GET['code']) == false) {
                                echo "<div id='dialog' title='Group Invitation'>
  <p>\"You have been invited to the following group: <div id = 'group' value = $group> $group</div> Press Accept to accept the invitation or Decline to reject it\"</p>
</div>";
                                 echo "<script type='text/javascript'>
                                         $( '#dialog' ).dialog({ buttons: [ { id:'test','data-test':'data test', text: 'Accept', click: function() {
                                        $.ajax({
                                        url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                                        type: 'post',
                                        data: { 'group': $('#group').attr('value'), 'action': 'acceptinvitation'},
                                        success: function(data, status) {
                                        }
                                        });
                                         $(this).dialog( 'close' ); } },

                                        { id:'test2','data-test':'data test', text: 'Decline', click: function() {
                                        $.ajax({
                                        url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                                        type: 'post',
                                        data: {'group': $('#group').attr('value'), 'action': 'declineinvitation'},
                                        success: function(data, status) {
                                        }
                                        });
                                        $(this).dialog( 'close' ); } } ] });
                                </script>";
                                $status = OC_User_Group_Admin_Util::Notification(OCP\USER::getUser(), $group);

                        }


                }





	}






        // patch //////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ( OCP\App::isEnabled('group_virtual') and OC_Group::inGroup(OC_User::getUser(),'admin') ){
            foreach ( \OC_Group_Virtual::getGroups() as $group) {
                echo "<li data-group=\"$group\" ><img src=" . OCP\Util::imagePath( 'user_group_admin', 'group.png' ) . ">$group</li>" ;
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

