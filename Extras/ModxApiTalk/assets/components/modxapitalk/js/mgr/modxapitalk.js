let ModxApiTalk = function (config) {
    config = config || {};
    ModxApiTalk.superclass.constructor.call(this, config);
};
Ext.extend(ModxApiTalk, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('modxapitalk', ModxApiTalk);

ModxApiTalk = new ModxApiTalk();