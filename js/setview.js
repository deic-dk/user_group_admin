if (!OCA.UserGroups) {
  OCA.UserGroups = {};
}

OCA.UserGroups.App = {
  //_bkFileList: null,
  _gid: null,
  _FileList: null,
  initialize: function($el, gid){
		/*this._bkFileList = new OCA.Files.FileList($el);
		this._bkFileList.changeDirectory(target);
		return this._bkFileList;*/
		if(this._FileList && this._gid==gid) {
			return this._FileList;
		}
		this._gid = gid;
		
		//var fileActions = OCA.Meta_data.App._createFileActions()
		var fileActions = new OCA.Files.FileActions();
		// default actions
		fileActions.registerDefaultActions();
		// legacy actions
		fileActions.merge(window.FileActions);
		// regular actions
		fileActions.merge(OCA.Files.fileActions);

		this._FileList = new OCA.UserGroups.FileList(
				$('#app-content-user-groups_'+gid),
			{
				scrollContainer: $('#app-content'),
				fileActions: fileActions,
				allowLegacyActions: true,
				gid: gid/*,
				dragOptions: OCA.Files.dragOptions,
				folderDropOptions: OCA.Files.folderDropOptions*/
			}
		);
		this._FileList.$el.find('#emptycontent').text(t('UserGroups', 'No files here'));
		return this._FileList;
  },
	_onActionsUpdated: function(ev, newAction) {
		// forward new action to the file list
		if (ev.action) {
			this.fileList.fileActions.registerAction(ev.action);
		} else if (ev.defaultAction) {
			this.fileList.fileActions.setDefault(
				ev.defaultAction.mime,
				ev.defaultAction.name
			);
		}
	},
	// TODO: delete this function
  reinitView: function(view, path){
		// The stuff below works, but keeps the hover bars with only default file actions
		OCA.Files.App.setActiveView('files'/*$(this).attr('data-id')*/, {silent: true});
		OCA.Files.App.files.initialize();
		$('#controls #breadcrumb-container .breadcrumb').remove();
		if($('tfoot .summary').length>1){
			$('tfoot .summary').first().remove();
		}

		var fileActions = new OCA.Files.FileActions();
		// default actions
		fileActions.registerDefaultActions();
		// legacy actions
		fileActions.merge(window.FileActions);
		// regular actions
		fileActions.merge(OCA.Files.fileActions);
		
		OCA.Files.App.fileList._onActionsUpdated = _.bind(this._onActionsUpdated, this);
		OCA.Files.fileActions.on('setDefault.app-files', this._onActionsUpdated);
		OCA.Files.fileActions.on('registerAction.app-files', this._onActionsUpdated);
		window.FileActions.on('setDefault.app-files', this._onActionsUpdated);
		window.FileActions.on('registerAction.app-files', this._onActionsUpdated);

		OCA.Files.App.fileList.initialize(view,
				{scrollContainer: $('#app-content'), dragOptions: OCA.Files.dragOptions, folderDropOptions: OCA.Files.folderDropOptions,
				fileActions: fileActions, allowLegacyActions: true});
		OCA.Files.App.fileList.changeDirectory(path, true, true);
		
		//if(typeof OCA.Meta_data.App.tag_semaphore == 'undefined' && typeof OCA.Meta_data.App.modifyFilelist != 'undefined'){
		//	OCA.Meta_data.App.modifyFilelist();
		//}		
		//$('#app-navigation ul li[data-id="'+$(this).attr('data-id')+'"] a').click();
		//window.location.href = "/index.php/apps/files?view=" + $(this).attr('data-id')+"&dir="+$(this).attr('data-path');
		
  }
};

function updateUserGroups(){
  $.ajax({
		url: OC.filePath('user_group_admin', 'ajax', 'groups.php'),
		async: false,
		success: function(response) {
			if(response){
				var bookmarks = '';
				$.each( response, function(key,value) {
					bookmarks = bookmarks+'<li data-id="user-groups_'+value.gid+
					'"><a href="#"><i class="icon icon-gift deic_green"></i><span>'+value.gid+'</span></a></li>';
				});
				$('.nav-sidebar li[data-id^="user-groups"]').remove();
				$('ul.nav-sidebar li[data-id="files_index"]').after(bookmarks);
				if($('ul.nav-sidebar li#places span i.icon-angle-right').is(':visible')){
					$('ul.nav-sidebar li[data-id^="user-groups"]').hide();
				}
			}
		}
  });
}

$(document).ready(function(){

	updateUserGroups();

  $('ul.nav-sidebar').on('click', 'li[data-id^="user-groups"]', function(e) {
		$('ul.nav-sidebar').find('.active').removeClass('active');
		$(this).children('a').addClass('active');
		
		if($('#app-content-'+$(this).attr('data-id')).length !== 0){
			$('#app-navigation ul li[data-id="'+$(this).attr('data-id')+'"] a').click();
		}
		else{
			window.location.href = "/index.php/apps/files?group=%2F&view=" + $(this).attr('data-id');
		}
	});
  
	// Fix the bookmark links to switch back to files view
	$('ul.nav-sidebar li[data-id^="internal-bookmarks_"]').click(function(e) {
		if(OCA.Files.App.getActiveView()!='files'){
			if(typeof OCA.UserGroups.App.oldFileList!='undefined'){
				OCA.Files.App.fileList = OCA.UserGroups.App.oldFileList;
			}
		}
	});

  $('[id^="app-content-user-groups"]').on('show', function(e) {
		var groupArr = e.target.getAttribute('id').split('_');
		groupArr.shift();
		var group = groupArr.join('_');
		OCA.UserGroups.App.oldFileList = OCA.Files.App.fileList;
		FileList = OCA.UserGroups.App.initialize($(e.target), group);
		OCA.Files.App.fileList = FileList;
		FileList.fixGroupLinks();
		OC.Upload.init(group);
		if(!OCA.Files.App.fileList.modified){
			OCA.Meta_data.App.modifyFilelist(OCA.UserGroups.FileList);
		}
		OCA.Files.App.fileList.modified = true;
  });
 
});
