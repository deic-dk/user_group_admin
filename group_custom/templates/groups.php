

<div id="group_left">

  <div id="owngroups">
    <div id="titlebar">
       My groups
    </div>
    <ul id="own">
      <?php echo $this->inc('part.group'); ?>
    </ul>
    <ul>
      <div id="group_custom_holder"><input id="newgroup" placeholder="New group" val=""></div>
      <li id="create_group">+</li>
    </ul>
  </div>

  <div id="othergroups">
    <div id="titlebar">
       Group memberships
    </div>
    <ul id="other">
      <?php echo $this->inc('part.memberships'); ?>
    </ul>
  </div>


</div>



<div id="group_right">

</div>



<div id="group_buttons">

    <button class="button" id="import_group"><?php echo $l->t('Import Group');?></button>
    <form  id="import_group_form" class="file_upload_form" action="<?php echo OCP\Util::linkTo('group_custom', 'ajax/import.php'); ?>" method="post" enctype="multipart/form-data">
        <input class="float" id="import_group_file" type="file" name="import_group_file" />
    </form>
</div>

