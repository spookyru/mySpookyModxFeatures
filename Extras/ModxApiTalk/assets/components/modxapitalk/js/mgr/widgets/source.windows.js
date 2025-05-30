ModxApiTalk.window.CreateSource = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'modxapitalk-source-window-create';
    }
    Ext.applyIf(config, {
        title: _('modxapitalk_source_create'),
        width: 550,
      autoHeight: false,
        scrollable: true,
        url: ModxApiTalk.config.connector_url,
        action: 'ModxApiTalk\\Processors\\ApiSource\\Create',
      fields: this.getFields(config),
        
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
      }],
        listeners: {
          beforeSubmit: {
            fn: function (fp) {
              const form = fp.getForm();

              // --- Параметры ---
              const rawParams = form.findField('params').getValue();
              const paramLines = rawParams.split('\n');
              const paramsObj = {};
              paramLines.forEach(line => {
                const parts = line.split(':');
                if (parts.length === 2) {
                  const key = parts[0].trim();
                  const raw = parts[1].trim();
                  const values = raw.includes(',') ? raw.split(',').map(v => v.trim()) : raw;
                  paramsObj[key] = values;
                }
              });
              form.findField('params').setValue(JSON.stringify(paramsObj));

              // --- Query Param ---
              const queryEnabled = form.findField('query_param_enabled').getValue();
              const queryName = form.findField('query_param_value').getValue();
              if (queryEnabled && queryName) {
                form.findField('query_param').setValue(JSON.stringify({ name_query: queryName }));
              } else {
                form.findField('query_param').setValue('');
              }

              // --- Extract Keys ---
              const rawExtractKeys = form.findField('extract_keys').getValue();
              if (rawExtractKeys && typeof rawExtractKeys === 'string') {
                const arr = rawExtractKeys
                  .split(',')
                  .map(k => k.trim())
                  .filter(Boolean);
                form.findField('extract_keys').setValue(JSON.stringify(arr));
              }

              return true;
            },
            scope: this
          }
        }
    });
    ModxApiTalk.window.CreateSource.superclass.constructor.call(this, config);
};
Ext.extend(ModxApiTalk.window.CreateSource, MODx.Window, {

    getFields: function (config) {
      return [{
        xtype: 'textfield',
        fieldLabel: _('modxapitalk_source_name'),
        name: 'name',
        anchor: '100%',
        allowBlank: false,
    }, {
        xtype: 'textarea',
        fieldLabel: _('modxapitalk_source_description'),
        name: 'description',
        anchor: '100%',
    }, {
        xtype: 'textfield',
        fieldLabel: _('modxapitalk_source_url'),
        name: 'url',
        anchor: '100%',
        },
        
        {
          xtype: 'modx-combo',
          fieldLabel: _('modxapitalk_auth_type'),
          name: 'auth_type',
          hiddenName: 'auth_type',
          store: [['none', 'Нет'], ['apikey', 'API Key'], ['bearer', 'Bearer']],
          mode: 'local',
          editable: false,
          triggerAction: 'all',
          anchor: '50%',
          listeners: {
            select: function (combo, record) {
              const val = record.data.field1;
        
              const isApiKey = val === 'apikey';
              const isBearer = val === 'bearer';
        
              const authKeyNameField = Ext.getCmp('modxapitalk-auth-key-name');
              const authValueField = Ext.getCmp('modxapitalk-auth-value');
              const authLocationField = Ext.getCmp('modxapitalk-auth-location');

              if (authKeyNameField) authKeyNameField.setVisible(isApiKey);
              if (authValueField) authValueField.setVisible(isApiKey || isBearer);
              if (authLocationField) authLocationField.setVisible(isApiKey);
            }
          }
        },
        {
          xtype: 'textfield',
          id: 'modxapitalk-auth-key-name',
          fieldLabel: _('modxapitalk_auth_key_name'),
          name: 'auth_header_key',
          anchor: '100%',
          hidden: true
        },
        {
          xtype: 'textfield',
          id: 'modxapitalk-auth-value',
          fieldLabel: _('modxapitalk_auth_value'),
          name: 'auth_header_value',
          anchor: '100%',
          hidden: true
        },
        {
          xtype: 'radiogroup',
          id: 'modxapitalk-auth-location',
          fieldLabel: _('modxapitalk_auth_header_type'),
          columns: 1,
          vertical: true,
          anchor: '100%',
          hidden: true,
          name: 'auth_header_type',
          items: [
            { boxLabel: _('modxapitalk_auth_header'), name: 'auth_header_type', inputValue: 'header', checked: true },
            { boxLabel: _('modxapitalk_auth_query'), name: 'auth_header_type', inputValue: 'query' }
          ]
        },
        {
          xtype: 'textarea',
          id: 'modxapitalk-param-builder-input',
          fieldLabel: _('modxapitalk_params_editor'),
          anchor: '100%',
          grow: true,
          height: 100,
          emptyText: 'topic: sports,technology\ncountry: US\nlang: en'
        },
        {
          xtype: 'hidden',
          id: 'modxapitalk-params',
          name: 'params'
        },
        {
          xtype: 'button',
          text: _('modxapitalk_generate_json'),
          style: 'margin: 10px 0',
          handler: function () {
            const input = Ext.getCmp('modxapitalk-param-builder-input').getValue();
            const lines = input.split('\n');
            const result = {};
        
            lines.forEach(line => {
              const parts = line.split(':');
              if (parts.length === 2) {
                const key = parts[0].trim();
                const raw = parts[1].trim();
                const values = raw.includes(',') ? raw.split(',').map(s => s.trim()) : raw;
                result[key] = values;
              }
            });
        
            Ext.getCmp('modxapitalk-params').setValue(JSON.stringify(result, null, 2));
            MODx.msg.status({
              title: _('modxapitalk_json_generated'),
              message: _('modxapitalk_json_saved')
            });
          }
        },       
        {
  xtype: 'xcheckbox',
  id: config.id + '-query-param-enabled',
  boxLabel: _('modxapitalk_include_query_param'),
  name: 'query_param_enabled',
  inputValue: 1,
  checked: false,
  listeners: {
    check: function (cb, checked) {
      Ext.getCmp(config.id + '-query-param-field').setVisible(checked);
    }
  }
},
{
  xtype: 'textfield',
  id: config.id + '-query-param-field',
  fieldLabel: _('modxapitalk_query_param_name'),
  name: 'query_param_value',
  anchor: '100%',
  hidden: true
},
{
  xtype: 'hidden',
  name: 'query_param'
},
        {
      xtype: 'button',
      style: 'margin-top: 10px',
      text: _('modxapitalk_test_request'),
      handler: function () {
        const win = this.findParentByType ? this.findParentByType('modx-window') : this.ownerCt.ownerCt;
        const values = win.fp ? win.fp.getForm().getValues() : {};
    
        MODx.Ajax.request({
          url: ModxApiTalk.config.connector_url,
          params: {
            action: 'ModxApiTalk\\Processors\\ApiSource\\Test',
            url: values.url,
            auth_type: values.auth_type,
            auth_value: values.auth_value,
            auth_header_value: values.auth_header_value,
            auth_header_key: values.auth_header_key,
            auth_header_type: values.auth_header_type,
            params: values.params
          },
          listeners: {
            success: {
              fn: function (r) {
                console.log(r.object.results);
                MODx.load({
                  xtype: 'modxapitalk-window-preview',
                  record: r.object.results,
                }).show();
              }
            },
            failure: {
              fn: function (r) {
                MODx.msg.alert(_('error'), r.message || 'Ошибка при запросе');
              }
            }
          }
        });
      }
        },
        {
        xtype: 'textarea',
        fieldLabel: _('modxapitalk_extract_keys'),
        name: 'extract_keys',
        anchor: '100%',
        grow: true,
        height: 80,
       emptyText: 'title, url, image'
    }, {
        xtype: 'xcheckbox',
        boxLabel: _('modxapitalk_source_active'),
        name: 'active',
        inputValue: 1,
        checked: true,
    }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('modxapitalk-source-window-create', ModxApiTalk.window.CreateSource);


ModxApiTalk.window.UpdateSource = function (config) {
  config = config || {};
  if (!config.id) {
    config.id = 'modxapitalk-source-window-update';
  }
  Ext.applyIf(config, {
    title: _('modxapitalk_source_update'),
    width: 550,
    autoHeight: true,
    url: ModxApiTalk.config.connector_url,
    action: 'ModxApiTalk\\Processors\\ApiSource\\Update',
    fields: this.getFields(config),
    keys: [{
      key: Ext.EventObject.ENTER, shift: true, fn: function () {
        this.submit()
      }, scope: this
    }],
    listeners: {
      beforeSubmit: {
        fn: function (fp) {
          const form = fp.getForm();

          // --- Обработка params ---
          const rawParams = form.findField('params').getValue();
          const paramLines = rawParams.split('\n');
          const paramsObj = {};

          paramLines.forEach(line => {
            const parts = line.split(':');
            if (parts.length === 2) {
              const key = parts[0].trim();
              const raw = parts[1].trim();
              const values = raw.includes(',') ? raw.split(',').map(v => v.trim()) : raw;
              paramsObj[key] = values;
            }
          });

          form.findField('params').setValue(JSON.stringify(paramsObj));

          // --- Обработка query_param ---
          const queryEnabled = form.findField('query_param_enabled').getValue();
          const queryName = form.findField('query_param_value').getValue();
          if (queryEnabled && queryName) {
            form.findField('query_param').setValue(JSON.stringify({ name_query: queryName }));
          } else {
            form.findField('query_param').setValue('');
          }

          return true;
        }, scope: this
      }
    }
  });
  ModxApiTalk.window.UpdateSource.superclass.constructor.call(this, config);
};
Ext.extend(ModxApiTalk.window.UpdateSource, MODx.Window, {

  getFields: function (config) {
    return [{
      xtype: 'hidden',
      name: 'id',
      id: config.id + '-id',
    }, {
      xtype: 'textfield',
      fieldLabel: _('modxapitalk_source_name'),
      name: 'name',
      anchor: '100%',
      allowBlank: false,
    }, {
      xtype: 'textarea',
      fieldLabel: _('modxapitalk_source_description'),
      name: 'description',
      anchor: '100%',
    }, {
      xtype: 'textfield',
      fieldLabel: _('modxapitalk_source_url'),
      name: 'url',
      anchor: '100%',
    }, {
      xtype: 'modx-combo',
      fieldLabel: _('modxapitalk_auth_type'),
      name: 'auth_type',
      hiddenName: 'auth_type',
      store: [['none', 'None'], ['apikey', 'API Key'], ['bearer', 'Bearer']],
      mode: 'local',
      editable: false,
      triggerAction: 'all',
      anchor: '50%',
      },
      {      
      xtype: 'xcheckbox',
      id: config.id + '-query-param-enabled',
      boxLabel: _('modxapitalk_include_query_param'),
      name: 'query_param_enabled',
      inputValue: 1,
      checked: false,
      listeners: {
      check: function (cb, checked) {
        Ext.getCmp(config.id + '-query-param-field').setVisible(checked);
      }
      }
    },
    {
    xtype: 'textfield',
    id: config.id + '-query-param-field',
    fieldLabel: _('modxapitalk_query_param_name'),
    name: 'query_param_value',
    anchor: '100%',
    hidden: true
      },
      {
      xtype: 'textfield',
      fieldLabel: _('modxapitalk_auth_value'),
      name: 'auth_value',
      anchor: '100%',
    }, {
      xtype: 'textarea',
      fieldLabel: _('modxapitalk_params'),
      name: 'params',
      anchor: '100%',
      grow: true,
      height: 80,
      emptyText: '{"category":["sport","movie"], "lang":["en","ru"]}'
    }, {
      xtype: 'textarea',
      fieldLabel: _('modxapitalk_extract_keys'),
      name: 'extract_keys',
      anchor: '100%',
      grow: true,
      height: 80,
      emptyText: 'title, url, image'
    }, {
      xtype: 'xcheckbox',
      boxLabel: _('modxapitalk_source_active'),
      name: 'active',
      inputValue: 1,
      checked: true,
    }];
  },

  loadDropZones: function () {
  }

});
Ext.reg('modxapitalk-source-window-update', ModxApiTalk.window.UpdateSource);

// --- Patch for setValues in updateItem (success.fn) ---
ModxApiTalk.updateSourceSetValues = function(w, r) {
  w.reset();
console.log('[DEBUG] Prepared params:', r.object.params);
  if (r.object.params && typeof r.object.params === 'object') {
    if (typeof r.object.params === 'string') {
      try {
        r.object.params = JSON.parse(r.object.params);
      } catch (e) {}
    }

    if (typeof r.object.params === 'object') {
      r.object.params = Object.entries(r.object.params).map(([k, v]) => {
        if (Array.isArray(v)) return `${k}: ${v.join(',')}`;
        return `${k}: ${v}`;
      }).join('\n');
    }
  }

  if (r.object.query_param && typeof r.object.query_param === 'string') {
    try {
      r.object.query_param = JSON.parse(r.object.query_param);
    } catch (e) {}
  }
  if (typeof r.object.query_param === 'object') {
    r.object.query_param_enabled = true;
    r.object.query_param_value = r.object.query_param.name_query || '';
  }
if (r.object.extract_keys && typeof r.object.extract_keys === 'string') {
  try {
    const parsed = JSON.parse(r.object.extract_keys);
    if (Array.isArray(parsed)) {
      r.object.extract_keys = parsed.join(', ');
    }
  } catch (e) {
    // оставим как есть
  }
}
  w.setValues(r.object);
};