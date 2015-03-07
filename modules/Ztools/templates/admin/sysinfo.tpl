{checkpermission component="Ztools::" instance="::" level="ACCESS_ADMIN" assign="rightsAdmin"}
{include file='admin/ajax_jquery.tpl'}
{include file='admin/ipinfo_ajax.tpl'}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="info" size="small"}
    <h3>{gt text='Server information'} {$coredata.version_num}</h3>
</div>

<form class="z-form" action="{modurl modname="Ztools" type="admin" func="main"}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='General info'}</legend>
        {if $rightsAdmin}
        <strong>
            {if empty($vars.ztools_url_cpanel)}
            <a href="#" title="{gt text='Please enter URL in settings.'}" onclick="alert('{gt text='Please enter URL in settings.'}')">{gt text='Hosting admin panel'}</a>
            {else}
            <a href="{$vars.ztools_url_cpanel}" target="_blank" title="{gt text='Open on new page'}">{gt text='Hosting admin panel'}</a>
            {/if}
        </strong>
        {/if}
        <div class="z-formrow">
            <label>{gt text='System'}</label>
            <span><strong>{$phpos}, {$server_software}, PHP {$server_phpversion}</strong></span>
        </div>
        <div class="z-formrow">
            <label>{gt text='Server IP address and port'}</label>
            <span><strong>{$server_ip}:{$server_port}</strong></span>
            <div class="z-formnote">
                <strong><a href="" onclick="Ztools_showIpInfo('{$server_ip}'); return false;">{gt text='See details'}</a></strong>
                &nbsp;&nbsp;<a href="https://ipinfo.io/{$server_ip}" target="_blank">{gt text='Visit site'}</a>
            </div>
        </div>
        <div class="z-formrow">
            <label>{gt text='Site root directory'}</label>
            <span><strong>{$site_root}</strong></span>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Database info'}</legend>
        {if $rightsAdmin}
        <strong>
            {if empty($vars.ztools_url_phpmyadmin)}
            <a href="#" title="{gt text='Please enter URL in settings.'}" onclick="alert('{gt text='Please enter URL in settings.'}')">{gt text='Database admin panel'}</a>
            {else}
            <a href="{$vars.ztools_url_phpmyadmin}" target="_blank" title="{gt text='Open on new page'}">{gt text='Database admin panel'}</a>
            {/if}
        </strong>
        {/if}
        <div class="z-formrow">
            <label>{gt text='Database server'}</label>
            <span><strong>{$dbserverversion}</strong></span>
        </div>
        <div class="z-formrow">
            <label>{gt text='Database driver'}</label>
            <span><strong>{$dbparams.driver}</strong></span>
        </div>
        <div class="z-formrow">
            <label>{gt text='Database host'}</label>
            <span><strong>{$dbparams.host}</strong></span>
        </div>
        <div class="z-formrow">
            <label>{gt text='Database name'}</label>
            <span><strong>{$dbparams.dbname}</strong></span>
        </div>
    </fieldset>
    {if $vars.ztools_showphpinfo != 2}
    <fieldset>
        <legend>{gt text='PHP info'}</legend>
        <strong><a href="{modurl modname='ztools' type='admin' func='displaysysinforaw'}" target="_blank" title="{gt text='Open on new page'}">{gt text='Display PHP info in new page'}</a></strong>
        {if $vars.ztools_showphpinfo == 1}
        <div style="font-size: 130%; margin-top: 5px">
            {$phpinfo}
        </div>
        {/if}
    </fieldset>
    {/if}
</form>

{adminfooter}