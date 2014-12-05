<div id="group_custom_main">

<table id="group_custom_table">
<thead>
<tr>
<td id="own_head">My groups</td>                                                                                                   
<td id="other_head">Group Memberships</td>                                                                                                
<td id="edit_head" class="right"></td>                                                                                             
</tr>  
</thead>
<tbody>
<tr>
<td>
   <div id="owngroups" class="group_left scrollable">
     <ul id="own">
       <?php echo $this->inc('part.group'); ?>
     </ul>
     <ul>
       <li id="group_custom_holder"><i class="fa fa-users"></i><input class="groupinput" id="newgroup" placeholder="New group" val=""></li>
     </ul>
   </div>
   <ul>  
     <li id="create_group" class="add">+</li>
   </ul>
</td>
<td>   
    <div id="othergroups" class="group_left scrollable2">
     <ul id="other">
       <?php echo $this->inc('part.memberships'); ?>
     </ul>
   </div>
</td>
<td class="right">
<div id="group_right"></div>
</td>
</tr>
</tbody>
</table>



<div id="group_buttons">
    <div class="title-group"><i class="fa fa-cloud-upload"></i>Import group from text file</div>
    <form  id="import_group_form" class="file_upload_form" action="<?php echo OCP\Util::linkTo('group_custom', 'ajax/import.php'); ?>" method="post" enctype="multipart/form-data">
        <input id="import_group_file" type="file" name="import_group_file" /> 
    </form>
</div>
</div>

