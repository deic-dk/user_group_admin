<div id="controls" style="height:63px;">
	<input type="hidden" name="permissions" value="" id="permissions">
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
<div class="col-xs-3 col-sm-6">

					<a class="name sort columntitle" data-sort="name"><span><?php p($l->t( 'Name' )); ?></span><span class="sort-indicator"></span></a>
</div>
<div class="col-xs-5 col-sm-5 text-right">
					<span id="selectedActionsList" class="selectedActions">
						<a href="" class="download">
							<img class="svg" alt="Download"
								 src="<?php print_unescaped(OCP\image_path("core", "actions/download.svg")); ?>" />
							<?php p($l->t('Download'))?>
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
					<span class="selectedActions"><a href="" class="delete-selected">
						<?php p($l->t('Delete'))?>
						<img class="svg" alt="<?php p($l->t('Delete'))?>"
							 src="<?php print_unescaped(OCP\image_path("core", "actions/delete.svg")); ?>" />
					</a></span>
			</th>
		</tr>
	</thead>
	<tbody id="fileList">
	</tbody>
	<tfoot>
	</tfoot>
</table>
