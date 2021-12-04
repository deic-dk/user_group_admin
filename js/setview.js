if (!OCA.UserGroups) {
  OCA.UserGroups = {};
}

OCA.UserGroups.App = {
  //_bkFileList: null,
  _FileList: [],
  initialize: function($el, gid){
		/*this._bkFileList = new OCA.Files.FileList($el);
		this._bkFileList.changeDirectory(target);
		return this._bkFileList;*/
		if(gid in this._FileList) {
			return this._FileList[gid];
		}
		
		//var fileActions = OCA.Meta_data.App._createFileActions()
		var fileActions = new OCA.Files.FileActions();
		// default actions
		fileActions.registerDefaultActions();
		// legacy actions
		fileActions.merge(window.FileActions);
		// regular actions
		fileActions.merge(OCA.Files.fileActions);

		this._FileList[gid] = new OCA.UserGroups.FileList(
				$('div[id="app-content-user-groups_'+gid.replace( /(:|\.|\[|\]|,|=|\')/g, "\\$1" )+
						'"]'),
			{
				scrollContainer: $('#app-content'),
				fileActions: fileActions,
				allowLegacyActions: true,
				gid: gid,
				dragOptions: OCA.Files.dragOptions,
				folderDropOptions: OCA.Files.folderDropOptions
			}
		);
		this._FileList[gid].$el.find('#emptycontent').text(t('UserGroups', 'No files here'));
		return this._FileList[gid];
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
		
  },
	fixGroupLinks:function(group){
		if(typeof group === 'undefined'){
			return false;
		}
		group = group.replace( /(:|\.|\[|\]|,|=|\')/g, "\\$1" );
		$("div[id^='app-content-user-groups']:not('.hidden') #breadcrumb-container .crumb").each(function(){
			var link = $(this).find('a');
			var href = link.attr('href');
			if(link && (typeof href== 'undefined' || href.indexOf('&group='))<0){
				link.attr('href', href+'&group='+group);
			}
		});
		// Change breadcrumb home icon to gift icon
		$('div[id="app-content-user-groups_'+group+'"]'+' #breadcrumb-container .breadcrumb .crumb a i.icon-home').addClass('icon-gift');
		$('div[id="app-content-user-groups_'+group+'"]'+' #breadcrumb-container .breadcrumb .crumb a i.icon-home').removeClass('icon-home');
		var topHref = $('div[id="app-content-user-groups_'+group+'"]'+' #breadcrumb-container .breadcrumb .crumb a').first().attr('href' );
		if(topHref){
			topHref = topHref.replace(/\&view=[^&]*&/g, '').replace(/\&view=[^&]*$/, '');
			$('div[id="app-content-user-groups_'+group+'"]'+' #breadcrumb-container .breadcrumb .crumb a').first().attr('href', topHref+'&view=user-groups_'+group);
		}
	},
	initGroupFileList:function(target){
		var groupArr = target.getAttribute('id').split('_');
		groupArr.shift();
		var group = groupArr.join('_');
		if(typeof OCA.UserGroups.App.oldFileList==='undefined'){
			OCA.UserGroups.App.oldFileList = OCA.Files.App.fileList;
		}
		FileList = OCA.UserGroups.App.initialize($(target), group);
		OCA.Files.App.fileList = FileList;
		if(!OCA.UserGroups.FileList.modified){
			OCA.UserGroups.App.fixGroupLinks();
		}
		OC.Upload.init(group);
		//if(!OCA.Files.App.fileList.modified){
		if(!OCA.UserGroups.FileList.modified){
			OCA.Meta_data.App.modifyFilelist(OCA.UserGroups.FileList);
		}
		OCA.Files.App.fileList.modified = true;
		OCA.UserGroups.FileList.modified = true;
	}
};

function updateUserGroups(){
  $.ajax({
		url: OC.filePath('user_group_admin', 'ajax', 'groups.php'),
	  data:{
		  onlyOwned: 'no'
	  },
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

function updateOwnedGroups(){
  $.ajax({
		url: OC.filePath('user_group_admin', 'ajax', 'groups.php'),
	  data:{
		  onlyOwned: 'yes'
	  },
		async: false,
		success: function(response) {
			if(response){
				var bookmarks = '';
				$.each( response, function(key,value) {
					if(typeof value.show_owned != 'undefined' && value.show_owned=='yes'){
						bookmarks = bookmarks+'<li data-id="owned-group-folders_'+value.gid+
						'"><a href="#"><i class="icon icon-binoculars deic_green"></i><span>'+value.gid+'</span></a></li>';
					}
				});
				$('.nav-sidebar li[data-id^="owned-group-folders_"]').remove();
				$('ul.nav-sidebar li[data-id="sharing_out"]').before(bookmarks);
			}
		}
  });
}

$(document).ready(function(){

	updateUserGroups();
	updateOwnedGroups();

	var places = readCookie('OCplaces');

	if(places == 'collapsed'){
			$('ul.nav-sidebar li[data-id^="owned-group-folders_"]').hide();
	}
	
	$('ul.nav-sidebar li[data-id^="owned-group-folders_"]').click(function(e) {
		ownedGroup = $(this).attr('data-id').substr(20);
		$('ul.nav-sidebar').find('.active').removeClass('active');
		$(this).children('a').addClass('active');
		//if(typeof OCA.Files=='undefined'){
			window.location.href = "/index.php/apps/files?dir=%2F&view=sharingin&owned_group="+ownedGroup ;
		/*}
		 // Not sure what the point of this was..
		else{
			OCA.Files.App.setActiveView('sharingin', {silent: false})
		}*/
	});

  $('ul.nav-sidebar').on('click', 'li[data-id^="user-groups"]', function(e) {
		$('ul.nav-sidebar').find('.active').removeClass('active');
		$(this).children('a').addClass('active');
		if($("div[id='app-content-"+$(this).attr('data-id').replace(/(:|\.|\[|\]|,|=|\')/g, "\\$1" )+"']").length !== 0){
			$('#app-navigation ul li[data-id="'+$(this).attr('data-id').replace(/(:|\.|\[|\]|,|=|\')/g, "\\$1")+'"] a').click();
		}
		else{
			window.location.href = "/index.php/apps/files?dir=%2F&view=" + $(this).attr('data-id').replace( /(:|\.|\[|\]|,|=|\')/g, "\\$1" );
		}
	});
  
	// Fix the bookmark links to switch back to files view
	$('ul.nav-sidebar li[data-id^="internal-bookmarks_"]').click(function(e) {
		if(OCA.Files && OCA.Files.App.getActiveView()!='files' && !$(this).attr('data-group')){
			if(typeof OCA.UserGroups.App.oldFileList!='undefined'){
				OCA.Files.App.fileList = OCA.UserGroups.App.oldFileList;
			}
		}
	});
	// Fix sharing link to switch back to files view
	$('ul.nav-sidebar li[data-id^="sharing"]').click(function(e) {
		if(OCA.Files && OCA.Files.App.getActiveView()!='files'){
			if(typeof OCA.UserGroups.App.oldFileList!='undefined'){
				OCA.Files.App.fileList = OCA.UserGroups.App.oldFileList;
			}
		}
	});

	 $('[id^="app-content-user-groups"]').on('hide', function(e) {
		$('ul.nav-sidebar').find('.active').removeClass('active');
	 });
	
  $('[id^="app-content-user-groups"]').on('show', function(e) {
  	OCA.UserGroups.App.initGroupFileList(e.target);
  });

 
});
