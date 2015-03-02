{ajaxheader modname='ztools' filename='ztools_admin_display.js' nobehaviour=true noscriptaculous=true effects=true}

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
            {gt text='To create new backup, use <Create> button below.'}
            {* Fatal error: Allowed memory size of [x] bytes exhausted (tried to allocate [y] bytes): You have to increase parameter 'memory_limit' in PHP settings.*}
            {* Fatal error: Allowed memory size of [x] bytes exhausted (tried to allocate [y] bytes) *}
            {* Fatal error: Maximum execution time of [n] seconds exceeded: You have to increase parameter 'max_execution_time' in PHP settings. *}
        </div>
        <div class="z-formrow">
            <label for="selectedtables">{gt text='Selected tables only'}</label>
            <input id="selectedtables" type="checkbox" name="selectedtables" value="1" onclick="ztools_showhide_div(this, 'selectedtables_div');" />
        </div>
        <div id="selectedtables_div" style="display: none;">{* begin hidden *}
            <div class="z-formrow">
                <label for="tablestoexport">{gt text="Tables to export"}<br />
                    <div class="z-sub z-formnote">{gt text='Total'}: {$tablestotal|formatnumber:0}</div>
                </label>
                <input type="hidden" name="tablestotal" value="{$tablestotal}" />
                <select id="tablestoexport" name="tablestoexport[]" size="6" multiple>
                    {foreach from=$tables key=index item=table}
                    <option value="{$table}">{$table}</option>
                    {/foreach}
                </select>
            </div>
        </div>{* end hidden *}
        <div class="z-buttons z-formbuttons">
            {button src="button_ok.png" name="create" value="1" set="icons/extrasmall" __alt="Create" __title="Create backup" __text="Create"}
            <a href="{modurl modname="Ztools" type="admin" func='main'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
        <div class="z-formrow">
            <label for="backupsdir">{gt text='Backups directory'}</label>
            <input id="backupsdir" type="text" name="backupsdir" value="{$vars.ztools_backupsdir|safetext}" disabled />
        </div>

        <div class="z-informationmsg">
            {gt text='Here is list of past database backups, available in directory:'} {$vars.ztools_backupsdir|safetext}
        </div>
        <div class="z-formrow">
            <label for="past_backup">{gt text="Past backups"}<br />
                <div class="z-sub z-formnote">{gt text='Total'}:<br >{$filescount|formatnumber:0} {if $filescount==1}{gt text='file'}{else}{gt text='files'}{/if}<br >{$filessize|formatnumber:0} {if $filessize==1}{gt text='byte'}{else}{gt text='bytes'}{/if}</div>
            </label>
            <select id="past_backup" name="past_backup[]" size="10" multiple>
                {foreach from=$backups key=index item=backup}
                <option value="{$backup.name}">{$backup.name}, {$backup.size|formatnumber:0} {gt text='bytes'}</option>
                {/foreach}
            </select>
            <div class="z-sub z-formnote">{gt text='File name format'}: {gt text='date'}_{gt text='time'}_{gt text='database'}_{gt text='tables count-archived-total'}</div>
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="fileimport.png" name="download" value="1" set="icons/extrasmall" __alt="Download" __title="Download backup" __text="Download"}
        {button src="fileimport.png" name="delete" value="1" set="icons/extrasmall" __alt="Delete" __title="Delete backup" __text="Delete"}
        {button src="kcmdf.png"  name="restore" value="1" set="icons/extrasmall" __alt="Restore" __title="Restore backup" __text="Restore"}
    </div>
</form>

{adminfooter}