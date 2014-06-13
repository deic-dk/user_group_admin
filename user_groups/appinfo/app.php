<?php

\OCP\App::addNavigationEntry(array(

       'id' => 'groups',
       'order' => 74,
       'href' => OCP\Util::linkTo("user_groups", "index.php"),                                                                                                                                                                                      
//       'icon' => OCP\Util::imagePath("core", "places/files.svg"),
       'name' => 'Groups'
      ));
?>
