<?php


    $groups = OC_User_Group_Admin_Util::getUserGroups(OC_User::getUser());
   //ioanna

        foreach ($groups as $group) {

            echo "<li data-group=\"$group\"><i class=\"fa fa-users\"></i>".$group."
                <span class=\"group-actions\">
                    <a href=# class='action export group' original-title=" . $l->t('Export') . "><i class=\"fa fa-cloud-download\"></i></a>
                    <a href=# class='action leave group' original-title=" . $l->t('Remove') . "><i class=\"fa fa-times\"></i></a>
<<<<<<< HEAD
                </span></li>";
		if (isset($_GET['code']) && $group != 'dtu.dk') {
       			echo "<script type='text/javascript'>
				var r = window.confirm(\"You have been invited to the following group:  $group\");
				if (r == true) {

}

 

			</script>";
			$result=OC_User_Group_Admin_Util::acceptInvitation($_GET['code']);

		}
=======
                </span></li>" ;
>>>>>>> eec41da0891f40b301882d2865ac1ddfbfc1359e
        }

        // patch //////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ( OCP\App::isEnabled('group_virtual') and OC_Group::inGroup(OC_User::getUser(),'admin') ){
            foreach ( \OC_Group_Virtual::getGroups() as $group) {
                echo "<li data-group=\"$group\" ><img src=" . OCP\Util::imagePath( 'user_group_admin', 'group.png' ) . ">$group</li>" ;
            }
        }
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



