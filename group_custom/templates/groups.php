<div  id="controls">

	<button class="button" id="create_group"><?php echo $l->t('Create Group');?></button>
    <button class="button" id="import_group"><?php echo $l->t('Import Group');?></button>
    <form  id="import_group_form" class="file_upload_form" action="<?php echo OCP\Util::linkTo('group_custom', 'ajax/import.php'); ?>" method="post" enctype="multipart/form-data">
        <input class="float" id="import_group_file" type="file" name="import_group_file" />
	</form>

</div>
	
<ul id="leftcontent">
  <div id="titlebar">
     My groups
  </div>

    <?php
        echo $this->inc('part.group');
    ?>

  <div id="titlebar">
     Group memberships
  </div>

    <?php
        echo $this->inc('part.memberships');
    ?>


</ul>

<div id="rightcontent">

</div>

<div id="group_custom_holder">

</div>
