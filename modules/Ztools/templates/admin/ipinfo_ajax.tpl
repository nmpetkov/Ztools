<div id="ztools_dialog" class="z-left" title="{gt text='IP address information'}" style="display: none;">
    {img id="ajaxindicator" modname=core set="ajax" src="indicator_circle.gif" alt=""}
</div>
<div style="display: none;">{gt text='Close'}</div>{* to be included in pot file only *}
<script type="text/javascript"><!--
    function Ztools_showIpInfo(ip_adr, divId) {

        // populate content by Ajax call
        var pars = {ip_adr: ip_adr};
        new Zikula.Ajax.Request(Zikula.Config.baseURL+"ajax.php?module=Ztools&type=ajax&func=getIpInfo",
            {parameters: pars, onComplete: Ztools_ajaxResponse});

        jQuery('#ztools_dialog').dialog({
            modal: true,
            minHeight: 350,
            autoResize: true,
            buttons: [ 
                { text: '{{gt text='Close'}}', click: function() { jQuery('#ztools_dialog').dialog('close'); } }
            ]
        });
    }

    function Ztools_ajaxResponse(req) {
        if (!req.isSuccess()) {
            Zikula.showajaxerror(req.getMessage());
            return;
        }
        var data = req.getData();
        if (data.alert) {
            alert(data.alert);
        }
        jQuery('#ztools_dialog').html(data.content);
    }
//--></script>
