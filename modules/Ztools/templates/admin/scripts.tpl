{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="gears" size="small"}
    <h3>{gt text='Scripts to execute'}</h3>
</div>

<div class="z-informationmsg">
    {gt text="This module executes PHP scripts, with system core functions and classes available."}
</div>
<form class="z-form" action="{modurl modname="Ztools" type="admin" func="executescripts"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    <fieldset>
        <legend>{gt text='Scripts'}</legend>
        <div class="z-informationmsg">
            {gt text='To add more scripts ready for execution, please upload them in scripts directory:'} {$vars.ztools_scriptsdir|safetext}<br />
            {gt text='See example.php for sample.'} {gt text='You can create an empty script in the form below.'}
        </div>
        {foreach from=$scripts key=index item=script}
        <div class="z-formrow">
            <label for="script_{$index}">{$script}</label>
            <input id="script_{$index}" type="checkbox" name="execute[{$index}]" />
            <input id="script_{$index}" type="hidden" name="scripts[{$index}]" value="{$script}" />
            &nbsp;&nbsp;&nbsp;<a href="{modurl modname='Ztools' type='admin' func='editfile' filename=$script}">{gt text='Edit'}</a>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href="{modurl modname='Ztools' type='admin' func='deletefile' filename=$script}" onclick="return confirm('{gt text="Are you sure you want to delete file\\n"|cat:$script|cat:"?"}')">{gt text='Delete'}</a>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href="{modurl modname='Ztools' type='admin' func='downloadfile' filename=$script}">{gt text='Download'}</a>
        </div>
        {/foreach}
        <div class="z-formrow">
            <label for="newSriptFilename">{gt text='Create empty PHP file:'}</label>
            <input id="newSriptFilename" type="text" name="newSriptFilename" maxlength="255" size="60" />
            <div class="z-sub z-formnote">{gt text='Enter just file name, php extension will be forced.'}</div>
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" set="icons/extrasmall" __alt="Execute" __title="Execute" __text="Execute"}
        <a href="{modurl modname="Ztools" type="admin" func='main'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>

<form class="z-form" action="{modurl modname="Ztools" type="admin" func="lockscriptsdir"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    <fieldset>
        <legend>{gt text='Security'}</legend>
        <div class="z-informationmsg">
            {gt text='By default the system is protected from unauthorized web access. Anyway, for security reason, you can explicitly lock access to scripts directory, by creating .htaccess file in it.'}
        </div>
        <div class="z-formrow">
            <label for="scriptsdir">{gt text='Scripts directory'}</label>
            <input id="scriptsdir" type="text" name="scriptsdir" value="{$vars.ztools_scriptsdir|safetext}" disabled />
        </div>
        <div class="{if $scriptsdirlockstatus}z-informationmsg{else}z-warningmsg{/if} z-formnote">
            <strong>{gt text='Status'}: {if $scriptsdirlockstatus}{gt text='Locked'}{else}{gt text='Unlocked'}{/if}</strong>
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" name="unlock" value="1" set="icons/extrasmall" __alt="Unlock" __title="Unlock" __text="Unlock"}
        {button src="button_cancel.png" name="lock" value="1"  set="icons/extrasmall" __alt="Lock" __title="Lock" __text="Lock"}
    </div>
</form>

{adminfooter}