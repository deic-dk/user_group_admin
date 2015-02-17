<?php
    $groups = OC_User_Group_Admin_Util::getUserGroups(OC_User::getUser());
   //ioanna
        foreach ($groups as $group) {

	    $verified = OC_User_Group_Admin_Util::searchUser($group, OC_User::getUser(), '0' );	
            echo "<li data-group=\"$group\"><i class=\"fa fa-users\"></i>".$group."
                <span class=\"group-actions\">
                    <a href=# class='action export group' original-title=" . $l->t('Export') . "><i class=\"fa fa-cloud-download\"></i></a>
                    <a href=# class='action leave group' original-title=" . $l->t('Remove') . "><i class=\"fa fa-times\"></i></a>
                </span></li>";
		if (isset($_GET['code']) && $group != 'dtu.dk' && $verified) {
       			echo "<script type='text/javascript'>
				window.alert(\"You have accepted the invitation to the following group:  $group\");
 
			</script>";
			$result=OC_User_Group_Admin_Util::acceptInvitation();

                }elseif (isset($_GET['coded']) && $group != 'dtu.dk' && $verified) {
                        echo "<script type='text/javascript'>
                                window.alert(\"You have declined the invitation to the following group:  $group\");

                        </script>";
                        $result=OC_User_Group_Admin_Util::declineInvitation(OCP\USER::getUser(), $group);
		} 
	}
        // patch //////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ( OCP\App::isEnabled('group_virtual') and OC_Group::inGroup(OC_User::getUser(),'admin') ){
            foreach ( \OC_Group_Virtual::getGroups() as $group) {
                echo "<li data-group=\"$group\" ><img src=" . OCP\Util::imagePath( 'user_group_admin', 'group.png' ) . ">$group</li>" ;
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

