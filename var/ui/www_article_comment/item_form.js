ui.www_article_comment.item_form = Ext.extend(Ext.form.FormPanel, {
	Load: function(data){
		var f = this.getForm();
		f.load({
			url: 'di/www_article_comment/get.json',
			params: {_sid: data.id},
			waitMsg: this.loadText,
			success: function(frm, act){
				var d = Ext.util.JSON.decode(act.response.responseText);
				f.setValues([{id: '_sid', value: d.data.id}]);
				this.fireEvent("data_loaded", d.data, d.data.id);
			},
			scope:this
		});
		f.setValues(data);
	},

	Save: function(){
		var f = this.getForm();
		if (f.isValid()){
			f.submit({
				url: 'di/www_article_comment/set.do',
				waitMsg: this.saveText,
				success: function(form, action){
					var d = Ext.util.JSON.decode(action.response.responseText);
					if (d.success){
						this.fireEvent('data_saved', d.data, d.data.id);
						this.Cancel();
					}
					else{

						showError(d.errors);
					}
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

	/**
	 * @constructor
	 */
	constructor: function(config){
		config = config || {};
		Ext.apply(this, {
			formWidth: 400,
			formHeight: 240,

			lblTitle: 'Тема',
			lblType: 'Опубликовано',

			loadText: 'Загрузка данных формы',

			saveText: 'Сохранение...',
			lblFile: 'Файл',
			lblDescr: 'Описание',

			bttSave: 'Сохранить',
			bttCancel: 'Отмена',
			lblPublished: 'Опубликовано',
			errSaveText: 'Ошибка во время сохранения',
			errInputText: 'Корректно заполните все необходимые поля',
			errConnectionText: "Ошибка связи с сервером"
		});
		
		Ext.apply(this, {
			frame: true, 
			fileUpload: false,
			defaults: {xtype: 'textfield', width: 150, anchor: '100%'},
			items: [
				{name: '_sid', inputType: 'hidden'},
				{name: 'item_id', inputType: 'hidden'},
				{fieldLabel: this.lblPublished, hiddenName: 'published', value: 0, xtype: 'combo', width: 50, anchor: null,
							store: new Ext.data.SimpleStore({ fields: ['value', 'title'], data: [[1, 'Да'], [0, 'Нет']] }),
							valueField: 'value', displayField: 'title', mode: 'local', triggerAction: 'all', selectOnFocus: true, editable: false
				},
				{fieldLabel: this.lblTitle, name: 'subject'},
				{fieldLabel: 'E-mail', name: 'email'},
				{fieldLabel: 'Автор', name: 'author_name'},
				{fieldLabel: this.lblDescr, name: 'comment', xtype: 'textarea'}
			],
			buttonAlign: 'right',
			buttons: [
				{iconCls: 'disk', text: this.bttSave, handler: this.Save, scope: this},
				{iconCls: 'cancel', text: this.bttCancel, handler: this.Cancel, scope: this}
			],
		});
		Ext.apply(this, config);
		ui.www_article_comment.item_form.superclass.constructor.call(this, config);
		this.on({
			data_saved: function(data, id){
				this.getForm().setValues([{id: '_sid', value: id}]);
				this._sid = data.id;
				this.reloadServices(data, id);
			},
			data_loaded: function(data, id){
				this.reloadServices(data, id);
			},
			scope: this
		})
	},

	filesList: function(){
		var app = new App({waitMsg: 'Загрузка формы'});
		app.on({
			apploaded: function(){
				var f = new ui.www_article_comment.main();
				var w = new Ext.Window({iconCls: this.iconCls, title: this.titleAdd, maximizable: true, modal: true, layout: 'fit', width: 500, height: 400, items: f});
				f.on({
					cancelled: function(){w.destroy()},
					closit: function(){w.destroy()},
					scope: this
				});
				w.show(null, function(){});
			},
			apperror: showError,
			scope: this
		});
		app.Load('www_article_comment', 'main');


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
	},

	reloadServices: function(data, id){
	}
});
