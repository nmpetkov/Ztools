{checkpermission component="Zwork::" instance="::" level="ACCESS_ADMIN" assign="rightsAdmin"}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="info" size="small"}
    <h3>{gt text='Server information'}</h3>
</div>

<form class="z-form" action="{modurl modname="Zwork" type="admin" func="main"}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='General info'}</legend>
        {if $rightsAdmin}
        <strong>
            {if empty($vars.zwork_url_cpanel)}
            <a href="#" title="{gt text='Please enter URL in settings.'}" onclick="alert('{gt text='Please enter URL in settings.'}')">{gt text='Hosting admin panel'}</a>
            {else}
            <a href="{$vars.zwork_url_cpanel}" target="_blank" title="{gt text='Open on new page'}">{gt text='Hosting admin panel'}</a>
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
            <div class="z-formnote"><strong><a href="https://ipinfo.io/{$server_ip}" target="_blank">{gt text='See details'}</a></strong></div>
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
            {if empty($vars.zwork_url_phpmyadmin)}
            <a href="#" title="{gt text='Please enter URL in settings.'}" onclick="alert('{gt text='Please enter URL in settings.'}')">{gt text='Database admin panel'}</a>
            {else}
            <a href="{$vars.zwork_url_phpmyadmin}" target="_blank" title="{gt text='Open on new page'}">{gt text='Database admin panel'}</a>
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
    {if $vars.zwork_showphpinfo != 2}
    <fieldset>
        <legend>{gt text='PHP info'}</legend>
        <strong><a href="{modurl modname='zwork' type='admin' func='displaysysinforaw'}" target="_blank" title="{gt text='Open on new page'}">{gt text='Display PHP info in new page'}</a></strong>
        {if $vars.zwork_showphpinfo == 1}
        <div style="font-size: 130%; margin-top: 5px">
            {$phpinfo}
        </div>
        {/if}
    </fieldset>
    {/if}
    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" set="icons/extrasmall" __alt="OK" __title="OK" __text="OK"}
    </div>
</form>

{adminfooter}