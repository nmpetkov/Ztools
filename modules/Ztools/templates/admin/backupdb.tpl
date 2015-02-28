{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="export" size="small"}
    <h3>{gt text='Backup'}</h3>
</div>

<form class="z-form" action="{modurl modname="Ztools" type="admin" func="executebackupdb"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    <fieldset>
        <legend>{gt text='Database backups'}</legend>
        <div class="z-informationmsg">
            {gt text='Here is list of past database backups, available in directory:'} {$vars.ztools_backupsdir|safetext}<br />
            {gt text='To create new backup, use the form below.'}
        </div>
        <div class="z-formrow">
            <label for="past_backup">{gt text="Past backups"}</label>
            <select id="past_backup" name="past_backup" size="10">
                {foreach from=$backups key=index item=backup}
                <option value="{$backup}">{$backup}</option>
                {/foreach}
            </select>
        </div>
        <div class="z-formrow">
            <label for="backupsdir">{gt text='Backups directory'}</label>
            <input id="backupsdir" type="text" name="backupsdir" value="{$vars.ztools_backupsdir|safetext}" disabled />
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" name="create" value="1" set="icons/extrasmall" __alt="Create backup" __title="Create backup" __text="Create backup"}
        {button src="fileimport.png" name="download" value="1" set="icons/extrasmall" __alt="Download backup" __title="Download backup" __text="Download backup"}
        {button src="kcmdf.png"  name="restore" value="1" set="icons/extrasmall" __alt="Restore backup" __title="Restore backup" __text="Restore backup"}
        <a href="{modurl modname="Ztools" type="admin" func='main'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>

{adminfooter}