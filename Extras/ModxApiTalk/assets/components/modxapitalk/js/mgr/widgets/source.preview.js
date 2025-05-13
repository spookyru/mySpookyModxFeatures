ModxApiTalk.window.Preview = function (config) {
  config = config || {};
  Ext.applyIf(config, {
    title: _('modxapitalk_preview'),
    width: 600,
    autoHeight: true,
    modal: true,
    fields: [{
      xtype: 'textarea',
      fieldLabel: _('modxapitalk_preview_result'),
      name: 'response',
      anchor: '100%',
      grow: true,
      readOnly: true,
      height: 300,
      value: JSON.stringify(config.record, null, 2)
    }]
  });
  ModxApiTalk.window.Preview.superclass.constructor.call(this, config);
};
Ext.extend(ModxApiTalk.window.Preview, MODx.Window);
Ext.reg('modxapitalk-window-preview', ModxApiTalk.window.Preview);