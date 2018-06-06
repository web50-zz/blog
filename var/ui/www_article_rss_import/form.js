ui.www_article_rss_import.form = Ext.extend(Ext.form.FormPanel, {
	formWidth: 900,
	formHeight: 500,

	loadText: 'Загрузка данных формы',
	lblTitle: 'Заголовок',
	lblSource: 'Источник',
	loadText: 'Загрузка данных формы',
	lblURI: 'URI',
	lblPublished: 'Опубликовано',
	lblPostType:'Тип публикации',

	lblId: "Id",
	saveText: 'Сохранение...',
	blankText: 'Необходимо заполнить',
	maxLengthText: 'Не больше 256 символов',

	bttSave: 'Сохранить',
	bttCancel: 'Отмена',

	errSaveText: 'Ошибка во время сохранения',
	errInputText: 'Корректно заполните все необходимые поля',
	errConnectionText: "Ошибка связи с сервером",
	errIdNodSet: 'Сохраните запись',
	msgNotDefined: 'Операция не активна, пока не сохранена форма',
	bttFiles: 'Файлы',
	bttComments: 'Комментарии',
	bttCategory: 'Входит в категории',
	bttTags:'Тэги',

	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/www_article_rss_import/get.json',
			params: {_sid: data._sid},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				this.fireEvent("data_loaded", d.data, data.id);
			},
			scope:this
		});
		f.setValues(data);
	},

	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/www_article_rss_import/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success)
						this.fireEvent('data_saved', d.data, d.data.id);
					else
						showError(d.errors);
				},
				failure: function(form, action){
					switch (action.failureType){
						case Ext.form.Action.CLIENT_INVALID:
							showError(this.errInputText);
						break;
						case Ext.form.Action.CONNECT_FAILURE:
							showError(this.errConnectionText);
						break;
						case Ext.form.Action.SERVER_INVALID:
							showError(action.result.errors);
					}
				},
				scope: this
			});
		}
	},
	
	Cancel: function(){
		this.fireEvent('cancelled');
	},

	importRss: function(){
		var f = this.getForm();
		if (f.isValid()){
			var vals = f.getValues();
			Ext.Msg.confirm(this.cnfrmTitle, 'Вы хотите импортировать?', function(btn){
				if (btn == "yes") Ext.Ajax.request({
						url: 'di/www_article_rss_import/import.do',
						params: {id: vals._sid},
						callback: function(options, success, response){
							var d = Ext.util.JSON.decode(response.responseText);
							if (d.success == true){
								Ext.Msg.alert('',d.msg);
								this.fireEvent('import_completed', id);
							}else{
								if(!d.msg)
								{
									showError('Во время importa возникли ошибки.');
								}else{	
									showError(d.msg);
								}
							}
						},
						scope: this
					});
			}, this);
		}
	},

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		var tb = new Ext.Toolbar({
			enableOverflow: true,
			items: [
			]
		});
		Ext.apply(this, {
			layout: 'fit',
			tbar: tb,
			items: [{
					layout: 'form',
					frame: true, 
					labelWidth: 100,
					labelAlign: 'right',
					autoScroll: true,
					defaults: {xtype: 'textfield', width: 80, anchor: '98%'},
					items: [
						{name: '_sid', xtype: 'hidden'},
						{fieldLabel: this.lblId, name: 'id', xtype: 'displayfield'},
						{fieldLabel: this.lblTitle, name: 'title', maxLength: 255, maxLengthText: 'Не больше 255 символов',allowBlank: false},
						{fieldLabel: this.lblSource, name: 'source', maxLength: 64, maxLengthText: 'Не больше 64 символов'},
						{fieldLabel: this.lblPostType, hiddenName: 'post_type', xtype: 'combo', allowBlank: false,
							valueField: 'id', displayField: 'title', value: '1', emptyText: '', 
							store: new Ext.data.JsonStore({url: 'di/www_article_post_types/type_list.json', root: 'records', fields: ['id', 'title'], autoLoad: true,
								listeners: {
									load: function(store,ops){
										var f = this.getForm().findField('post_type');
										f.setValue(f.getValue());
									}, 
									beforeload:function(store,ops){
									},
									scope: this
								}
							}),
							mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
						},
						{fieldLabel: '', xtype: 'compositefield', items: [
							{xtype: 'button', iconCls: 'add', text:'Импортировать свежее',listeners: {click: function(){this.fireEvent('import_rss')}, scope: this}},
							{xtype: 'displayfield', name: 'some_some'}
						]},

					]
			}],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			]
		});
		Ext.apply(this, config);
		ui.www_article_rss_import.form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(data, id){
				this.getForm().setValues([{id: '_sid', value: id}]);
			},
			data_loaded: function(data, id){
			},
			import_rss: function(){
				var vals = this.getForm().getValues();
				if(!(vals._sid >0)){
					showError(this.msgNotDefined);
					return;
				}
				this.importRss();
			},
			scope: this
		})
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
