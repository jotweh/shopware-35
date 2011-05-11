{extends file="backend/ext_js/index.tpl"}

{block name="backend_index_javascript" append}
<script type="text/javascript">
//<![CDATA[
Ext.application({
    name: 'EOS Payment',

    appFolder: 'app',

    controllers: [
        'Users'
    ],

    launch: function() {
        Ext.create('Ext.container.Viewport', {
            layout: 'fit',
            items: [
                {
                    xtype: 'panel',
                    title: 'Users',
                    html : 'List of users will go here'
                }
            ]
        });
    }
});
//]]>
</script>
{/block}