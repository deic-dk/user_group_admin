<?php

// Look up other security checks in the docs!
 \OCP\User::checkLoggedIn();
 \OCP\App::checkAppEnabled('user_groups');

 $tpl = new OCP\Template("user_groups", "main", "user");
 $tpl->assign('msg', 'Hello World');
 $tpl->printPage();
