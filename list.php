<?php

// Check if we are a user
 OCP\User::checkLoggedIn();

 $tmpl = new OCP\Template('user_group_admin', 'list', '');

 //OCP\Util::addScript('user_group_admin', 'filelist');

 $tmpl->printPage();

