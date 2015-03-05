<?php

OC_Util::checkAdminUser();

OCP\Util::addScript('user_group_admin', 'settings');

$tmpl = new OCP\Template( 'user_group_admin', 'settings.tpl');


return $tmpl->fetchPage();

