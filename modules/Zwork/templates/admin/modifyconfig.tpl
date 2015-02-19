{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text='Module settings'}</h3>
</div>

<form class="z-form" action="{modurl modname="Zwork" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    <fieldset>
        <legend>{gt text='General settings'}</legend>
        <div class="z-formrow">
            <label for="zwork_url_cpanel">{gt text='Hosting admin URL'}</label>
            <input id="zwork_url_cpanel" type="text" name="zwork_url_cpanel" value="{$vars.zwork_url_cpanel|safetext}" />
            <div class="z-informationmsg z-formnote">
                {gt text='Enter here URL address to your hosting provider admin panel (Cpanel or other).'}<br />
                {gt text='Link will appear in the info page for easy access.'}
            </div>
        </div>
        <div class="z-formrow">
            <label for="zwork_url_phpmyadmin">{gt text='Database admin URL'}</label>
            <input id="zwork_url_phpmyadmin" type="text" name="zwork_url_phpmyadmin" value="{$vars.zwork_url_phpmyadmin|safetext}" />
            <div class="z-informationmsg z-formnote">
                {gt text='Enter here URL address to your database admin panel (phpMyAdmin or other).'}<br />
                {gt text='Link will appear in the info page for easy access.'}
            </div>
        </div>
        <div class="z-formrow">
            <label for="zwork_scriptsdir">{gt text='Default scripts directory'}</label>
            <input id="zwork_scriptsdir" type="text" name="zwork_scriptsdir" value="{$vars.zwork_scriptsdir|safetext}" />
            {if $scriptsdir_exist}
            <div class="z-informationmsg z-formnote">{gt text='Directory exist.'}
            {else}
            <div class="z-warningmsg z-formnote">{gt text='Directory does not exist!'}
            {/if}
            <br />{gt text='This is the place, where you upload scripts to execute.'} {gt text='See example.php for sample.'}
            </div>
        </div>
        <div class="z-formrow">
            <label for="scriptsdir_createfolder">{gt text='Create specified scripts directory'}</label>
            <input id="scriptsdir_createfolder" type="checkbox" name="scriptsdir_createfolder" />
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
        <a href="{modurl modname="Zwork" type="admin" func='modifyconfig'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
    </div>
</form>
{adminfooter}