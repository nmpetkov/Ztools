{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="info" size="small"}
    <h3>{gt text='Client information'}</h3>
</div>

<form class="z-form" action="{modurl modname="Ztools" type="admin" func="main"}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='General info'}</legend>
        <div class="z-formrow">
            <label>{gt text='User IP address and port'}</label>
            <span><strong>{$user_ip}:{$user_port}</strong></span>
            <div class="z-formnote"><strong><a href="https://ipinfo.io/{$user_ip}" target="_blank">{gt text='See details'}</a></strong></div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Browser info'}</legend>
        <div class="z-formrow">
            <label>{gt text='User agent'}</label>
            <span><strong>{$user_agent}</strong></span>
            <div class="z-formnote"><strong><a href="http://www.useragentstring.com/" target="_blank">{gt text='See details'}</a></strong></div>
        </div>
        <div class="z-formrow">
            <label>{gt text='Accept language'}</label>
            <span><strong>{$user_lang}</strong></span>
        </div>
        <div class="z-formrow">
            <label>{gt text='Accept'}</label>
            <span><strong>{$user_accept}</strong></span>
        </div>
        <div class="z-formrow">
            <label>{gt text='Cookies'}</label>
            <span><strong><a href="{modurl modname='ztools' type='admin' func='displaycookies'}" target="_blank">{gt text='Display on new page'}</a></strong></span>
        </div>
    </fieldset>
</form>

{adminfooter}