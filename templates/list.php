<div id="controls">
	<div class="row">
		 <div id="breadcrumb-container" class="hidden-xs"></div>
		<!-- <div class="col-md-4 col-sm-4 hidden-xs">
			<div id="breadcrumb-container"></div>
		</div> -->
		<div class="col-md-12 col-sm-12 col-xs-12 text-right">
			<div class="actions creatable hidden">
				<?php
					/* 	Note: the template attributes are here only for the public page. These are normally loaded
						through ajax instead (updateStorageStatistics). */ ?>
				<div id="uploadprogresswrapper">
					<div id="uploadprogressbar"></div>
					<input type="button" class="stop icon-close" style="display:none" value="" />
				</div>
				<div id="upload"
					 title="<?php isset($_['uploadMaxHumanFilesize']) ? p($l->t('Upload (max. %s)', array($_['uploadMaxHumanFilesize']))) : '' ?>">
						<input type="hidden" id="max_upload" name="MAX_FILE_SIZE" value="<?php isset($_['uploadMaxFilesize']) ? p($_['uploadMaxFilesize']) : '' ?>" />
						<input type="hidden" id="upload_limit" value="<?php isset($_['uploadLimit']) ? p($_['uploadLimit']) : '' ?>" />
						<input type="hidden" id="free_space" value="<?php isset($_['freeSpace']) ? p($_['freeSpace']) : '' ?>" />
						<?php if(isset($_['dirToken'])):?>
						<input type="hidden" id="publicUploadRequestToken" name="requesttoken" value="<?php p($_['requesttoken']) ?>" />
						<input type="hidden" id="dirToken" name="dirToken" value="<?php p($_['dirToken']) ?>" />
						<input type="hidden" id="dirToken" name="owner" value="<?php p($_['owner']) ?>" />
						<input type="hidden" id="dirToken" name="id" value="<?php p($_['id']) ?>" />
						<?php endif;?>
						<input type="hidden" class="max_human_file_size"
							   value="(max <?php isset($_['uploadMaxHumanFilesize']) ? p($_['uploadMaxHumanFilesize']) : ''; ?>)" />
						<input type="file" id="file_upload_start" name='files[]'
							   data-url="<?php p(\OC::$WEBROOT);?>/themes/<?=OC_Util::getTheme();?>/apps/files/ajax/upload.php" />
						<a href="#" class="btn btn-primary btn-flat"><i class="icon-upload-cloud"></i> <?php p($l->t('Upload'));?></a>
				</div>
				<?php if(!isset($_['dirToken'])):?>
						<div id="new" class="btn-group">
							<a type="button" class="btn btn-default btn-flat"><?php p($l->t('New'));?> <i class="fa fa-caret-down"><img src="<?php p(\OC::$WEBROOT);?>/themes/<?=OC_Util::getTheme();?>/core/img/icon-chevron-down-gray.png"></i></a>
							<ul>
								<li class=""
									data-type="file" data-newname="<?php p($l->t('New text file')) ?>.txt">
									<p><i class="icon-doc-text"></i> <?php p($l->t('Text file'));?></p>
								</li>
								<li class=""
									data-type="folder" data-newname="<?php p($l->t('New folder')) ?>">
									<p><i class="icon-folder-empty"></i> <?php p($l->t('Folder'));?></p>
								</li>
								<li class="" data-type="web">
									<p><i class="icon-link"></i> <?php p($l->t('From link'));?></p>
								</li>
							</ul>
						</div>
			<?php endif;?>
			</div>
			<div id="file_action_panel"></div>
			<div class="notCreatable notPublic hidden">
				<?php p($l->t('You don’t have permission to upload or create files here'))?>
			</div>
		</div>
	</div>
	<input type="hidden" name="permissions" value="" id="permissions" />
</div>

<div id="emptycontent" class="hidden"></div>

<table id="filestable" class="panel">

  <thead class="panel-heading">
	<tr>
	  <th id='headerName' class="hidden column-name">
		<div id="headerName-container" class="row">
		  <div class="col-xs-4 col-sm-1">
			<input type="checkbox" id="select_all_files" class="select-all"/>
			<label for="select_all_files"></label>
		  </div>
		  <div class="col-xs-3">
			<a class="name sort columntitle" data-sort="name"><span class="text-semibold"><?php p($l->t( 'Name' )); ?></span><span class="sort-indicator"></span></a>
		  </div>
		  <div class="col-xs-5 col-sm-5 text-right">
			<span id="" class="selectedActions">
			  <a href="" class="download btn btn-xs btn-default">
				<i class="icon-download-cloud"></i>
				<?php p($l->t('Download'))?>
			  </a>
			  <a href="" class="delete-selected btn btn-xs btn-danger">
				<i class="icon-trash"></i>
				<?php p($l->t('Delete'))?>
			  </a>
			</span>
		  </div>
		</div>
	  </th>
	  <th id="headerSize" class="hidden column-size">
		<a class="size sort columntitle" data-sort="size"><span><?php p($l->t('Size')); ?></span><span class="sort-indicator"></span></a>
	  </th>
	  <th id="headerDate" class="hidden column-mtime">
		<a id="modified" class="columntitle" data-sort="mtime"><span><?php p($l->t( 'Modified' )); ?></span><span class="sort-indicator"></span></a>
	  </th>
	</tr>
  </thead>
  
  <tbody id="fileList">
  </tbody>
  <tfoot>
  </tfoot>
</table>
<input type="hidden" name="dir" id="dir" value="" />
<div id="uploadsize-message" title="<?php p($l->t('Upload too large'))?>">
	<p>
	<?php p($l->t('The files you are trying to upload exceed the maximum size for file uploads on this server.'));?>
	</p>
</div>
<div id="scanning-message">
	<h3>
		<?php p($l->t('Files are being scanned, please wait.'));?> <span id='scan-count'></span>
	</h3>
	<p>
		<?php p($l->t('Currently scanning'));?> <span id='scan-current'></span>
	</p>
</div>
