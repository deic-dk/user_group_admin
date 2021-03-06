 (function() {

	var FileList = function($el, options) {
		this.initialized = false;
		this.modified = false;
		this.initialize($el, options);
		this.setupUploadEvents();
		this.gid = options.gid;
		this._currentDirectory = '/';
	};
	

  FileList.prototype = _.extend({}, OCA.Files.FileList.prototype, {
		
		appName: 'User_group_admin',
		
		getParam: function(href, key) {
			var results = new RegExp('[\?&]' + key + '=([^&#]*)').exec(href);
			if (results==null){
				return '';// null;
		}
		else{
			return results[1] || 0;
		}
	},
	getGetParam: function(key) {
		return this.getParam(window.location.href, key);
	},

		reload: function(_id, _owner, _group, _errorCallback) {
			var viewParam = this.getGetParam( 'view');
			if(this.gid && viewParam.indexOf('user-groups_')==0) {
				this._selectedFiles = {};
				this._selectionSummary.clear();
				this.$el.find('.select-all').prop('checked', false);
				this.showMask();
				$('ul.nav-sidebar').find('.active').removeClass('active');
				$('.nav-sidebar li[data-id="user-groups_'+this.gid.replace( /(:|\.|\[|\]|,|=|\')/g, "\\$1" )+'"] a').addClass('active');
				// Change breadcrumb home icon to gift icon
				$('div[id="app-content-user-groups_'+this.gid+'"] #breadcrumb-container .breadcrumb .crumb a i.icon-home').addClass('icon-gift');
				$('div[id="app-content-user-groups_'+this.gid+'"] #breadcrumb-container .breadcrumb .crumb a i.icon-home').removeClass('icon-home');
				// This is the original - and when not in the original files app, causes unnecessary abort and reload
				/*if (this._reloadCall) {
					this._reloadCall.abort();
				}*/
				if (!this._reloadCall) {
					this._reloadCall = $.ajax({
						url: this.getAjaxUrl('list'),
						data: { 
							dir :  this.getCurrentDirectory(),
							sort: this._sort,
							sortdirection: this._sortDirection,
							gid: this.gid
						}
					});
				}
				var callBack = this.reloadCallback.bind(this);
				var errorCallback = (typeof _errorCallback !== 'undefined'?_errorCallback:function(){return true;});
				return this._reloadCall.then(function(response){return callBack(response, errorCallback);},
				 function(response){return callBack(response, errorCallback);});
			}
			else {
				return OCA.Files.FileList.prototype.reload.apply(this, arguments);
			}
		},

		getAjaxUrl: function(action, params) {
			var q = '';
			if (params) {
				q = '?' + OC.buildQueryString(params);
			}
			return OC.filePath('user_group_admin', 'ajax', action + '.php') + q;
		},

		updateEmptyContent: function() {
			var dir = this.getCurrentDirectory();
			if (dir === '/') {
				// root has special permissions
				this.$el.find('#emptycontent').toggleClass('hidden', !this.isEmpty);
				this.$el.find('#filestable thead th').toggleClass('hidden', this.isEmpty);
			}
			else {
				OCA.Files.FileList.prototype.updateEmptyContent.apply(this, arguments);
			}
		}

  });
	
	OCA.UserGroups.FileList = FileList;
	
})();
