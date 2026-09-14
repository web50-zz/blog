ui.www_article.main = Ext.extend(ui.www_article.grid, {
	bttAdd: "Добавить публикацию",
	bttEdit: "Редактировать",
	bttDelete: "Удалить",
	bttFileTypes:"Типы файлов",
	bttTagTypes:"Типы тэгов",
	bttArticleTypes:"Категории публикаций",
	bttPostTypes:"Типы постов",
	addTitle: "Добавление фотографии",
	editTitle: "Редактирование фотографии",

	cnfrmTitle: "Подтверждение",
	cnfrmMsg: "Вы действительно хотите удалить эту фотографию?",


	Add: function(){
		var app = new App({waitMsg: this.frmLoading});
		var pid = this.getKey();
		app.on({
			apploaded: function(){
				var f = new ui.www_article.form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(){this.store.reload();w.destroy()},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article', 'form');
	},
	fileTypes: function(){
		var app = new App({waitMsg: this.frmLoading});
		var pid = this.getKey();
		app.on({
			apploaded: function(){
				var f = new ui.www_article_file_types.main();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					data_saved: function(){this.store.reload(); w.destroy();},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_file_types', 'main');
	},
	tagTypes: function(){
		var app = new App({waitMsg: this.frmLoading});
		var pid = this.getKey();
		app.on({
			apploaded: function(){
				var f = new ui.www_article_tag_types.main();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					data_saved: function(){this.store.reload(); w.destroy();},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_tag_types', 'main');
	},
	articleTypes: function(){
		var app = new App({waitMsg: this.frmLoading});
		var pid = this.getKey();
		app.on({
			apploaded: function(){
				var f = new ui.www_article_type.main();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					data_saved: function(){this.store.reload(); w.destroy();},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_type', 'main');
	},
	postTypes: function(){
		var app = new App({waitMsg: this.frmLoading});
		var pid = this.getKey();
		app.on({
			apploaded: function(){
				var f = new ui.www_article_post_types.main();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					data_saved: function(){this.store.reload(); w.destroy();},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_post_types', 'main');
	},

	Edit: function(){
		var row = this.getSelectionModel().getSelected();
		var id = row.get('id');
		var app = new App({waitMsg: this.frmLoading});
		app.on({
			apploaded: function(){
				var f = new ui.www_article.form();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleEdit, maximizable: true, modal: true, layout: 'fit', width: f.formWidth, height: f.formHeight, items: f});
				f.on({
					data_saved: function(){this.store.reload(); w.destroy();},
					cancelled: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){f.Load({_sid: id})});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article', 'form');
	},
	multiSave: function(){
		this.store.save();
	},
	Delete: function(){
		var record = this.getSelectionModel().getSelections();
		if (!record) return false;

		Ext.Msg.confirm(this.cnfrmTitle, this.cnfrmMsg, function(btn){
			if (btn == "yes"){
				this.store.remove(record);
			}
		}, this);
	},

	doFilter: function(){
		var p = {};
		Ext.each(this._filterFields, function(f){
			if (f.getValue() !== '') {
				var val = f.getValue();
				if (f.name == '_stitle' || f.name == '_sid') {
					val = '%' + val + '%';
				} else if (f.name == '_spost_type' && !val) {
					return;
				}
				p[f.name] = val;
			}
		});
		this.store.load({params: Ext.apply(p, {start: 0, limit: this.pagerSize})});
	},

	resetFilter: function(){
		Ext.each(this._filterFields, function(f){
			if (f.name == '_spost_type') {
				f.setValue(0);
			} else {
				f.setValue('');
			}
		});
		this.store.load({params: {start: 0, limit: this.pagerSize}});
	},

	/**
	 * @constructor
	 */
	constructor: function(config)
	{
		var sidField = new Ext.form.TextField({name: '_sid', width: 60, emptyText: 'ID', maskRe: /[0-9]/});
		var postTypeCombo = new Ext.form.ComboBox({
			name: '_spost_type', width: 98, emptyText: 'Тип',
			store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({url: 'di/www_article_post_types/list.js'}),
				reader: new Ext.data.JsonReader({root: 'records'}, [
					{name: 'id', type: 'int'}, 'title'
				]),
				autoLoad: true,
				listeners: {
					load: function(s) {
						s.insert(0, new s.recordType({id: 0, title: 'Все типы'}));
						postTypeCombo.setValue(0);
					}
				}
			}),
			valueField: 'id', displayField: 'title',
			mode: 'remote', triggerAction: 'all', editable: false, listWidth: 200
		});
		var dateField = new Ext.form.TextField({name: '_srelease_date', width: 65, emptyText: 'Дата'});
		var titleField = new Ext.form.TextField({name: '_stitle', width: 150, emptyText: 'Название'});

		this._filterFields = [sidField, postTypeCombo, dateField, titleField];
		Ext.each(this._filterFields, function(f){
			f.on('specialkey', function(field, e){
				if (e.getKey() == e.ENTER) this.doFilter();
			}, this);
		}, this);

		Ext.apply(this, {
			tbar: [
				{iconCls: 'note_add', text: this.bttAdd, handler: this.Add, scope: this},
				'-',
				sidField, postTypeCombo, dateField, titleField,
				{iconCls: 'find', text: 'Поиск', handler: this.doFilter, scope: this},
				{iconCls: 'cancel', text: 'Сброс', handler: this.resetFilter, scope: this},
				'-',
				{iconCls: 'note_add', text: this.bttFileTypes, handler: this.fileTypes, scope: this},
				{iconCls: 'note_add', text: this.bttTagTypes, handler: this.tagTypes, scope: this},
				{iconCls: 'note_add', text: this.bttArticleTypes, handler: this.articleTypes, scope: this},
				{iconCls: 'note_add', text: this.bttPostTypes, handler: this.postTypes, scope: this},
				'->', {iconCls: 'help', handler: function(){showHelp('www_article')}}
			]
		});
		config = config || {};
		Ext.apply(this, config);
		ui.www_article.main.superclass.constructor.call(this, config);
		this.on({
			rowcontextmenu: function(grid, rowIndex, e){
				grid.getSelectionModel().selectRow(rowIndex);
				var cmenu = new Ext.menu.Menu({items: [
					{iconCls: 'note_edit', text: this.bttEdit, handler: this.Edit, scope: this},
					{iconCls: 'note_delete', text: this.bttDelete, handler: this.Delete, scope: this},
					'-'
				]});
				e.stopEvent();  
				cmenu.showAt(e.getXY());
			},
			rowdblclick: this.Edit,
			scope: this
		});
	},

	/**
	 * To manually set default properties.
	 * 
	 * @param {Object} config Object containing all config options.
	 */
	configure: function(config){
		config = config || {};
		Ext.apply(this, config, config);
	},

	/**
	 * @private
	 * @param {Object} o Object containing all options.
	 *
	 * Initializes the box by inserting into DOM.
	 */
	init: function(o){
	}
});
