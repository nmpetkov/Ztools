{ajaxheader modname='ztools' filename='ztools_admin_display.js' nobehaviour=true noscriptaculous=true effects=true}
{checkpermission component="Ztools::" instance="::" level="ACCESS_ADMIN" assign="rightsAdmin"}
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
            {gt text='To create new backup, use <Create> button below.'} {gt text='Possible errors, related to your PHP host settings'}:
            <div class="z-sub">
                [Fatal error: Allowed memory size of 'n' bytes exhausted] - {gt text='Solution is to increase `%s` PHP setting.' tag1='memory_limit'} {gt text='Current value is %s.' tag1='memory_limit'|ini_get}<br />
                [Fatal error: Maximum execution time of 'n' seconds exceeded] - {gt text='Solution is to increase `%s` PHP setting.' tag1='max_execution_time'} {gt text='Current value is %s seconds.' tag1='max_execution_time'|ini_get}
            </div>
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
        {if $vars.ztools_expmethodshow}
            <div class="z-formrow">
                <label for="export_method">{gt text="Export method"}</label>
                <select id="export_method" name="export_method" size="1">
                    <option value="1"{if $vars.ztools_exportmethod == "1"} selected="selected"{/if}>{gt text="Mysqldump php"}</option>
                    <option value="2"{if $vars.ztools_exportmethod == "2"} selected="selected"{/if}>{gt text="Mysqldump shell"}</option>
                    <option value="3"{if $vars.ztools_exportmethod == "3"} selected="selected"{/if}>{gt text="Ztools native"}</option>
                </select>
            </div>
        {else}
            <input type="hidden" name="export_method" value="{$vars.ztools_exportmethod}" />
        {/if}
        {if $rightsAdmin}
            <div class="z-formrow">
                <label for="export_compress">{gt text="Compression"}</label>
                <select id="export_compress" name="export_compress" size="1">
                    <option value="0"{if $vars.ztools_exportcompress == "0"} selected="selected"{/if}>{gt text="None"}</option>
                    <option value="1"{if $vars.ztools_exportcompress == "1"} selected="selected"{/if}>{gt text="Gzip"}</option>
                </select>
            </div>
        {else}
            <input type="hidden" name="export_compress" value="{$vars.ztools_exportcompress}" />
        {/if}

        <div class="z-buttons z-formbuttons">
            {button src="button_ok.png" name="create" value="1" set="icons/extrasmall" __alt="Create" __title="Create backup" __text="Create"}
            <a href="{modurl modname="Ztools" type="admin" func='main'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
        {if $rightsAdmin}
        <div class="z-formrow">
            <label for="backupsdir">{gt text='Backups directory'}</label>
            <input id="backupsdir" type="text" name="backupsdir" value="{$vars.ztools_backupsdir|safetext}" disabled />
        </div>
        {/if}

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
            <div class="z-sub z-formnote">{gt text='File name format'}: {gt text='date'}_{gt text='time'}_{gt text='database'}_{gt text='tables count-archived-total'}_{gt text='method'}</div>
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="fileimport.png" name="download" value="1" set="icons/extrasmall" __alt="Download" __title="Download backup" __text="Download"}
        {button src="14_layer_deletelayer.png" name="delete" value="1" set="icons/extrasmall" __alt="Delete" __title="Delete backup" __text="Delete"}
        {button src="kcmdf.png"  name="restore" value="1" set="icons/extrasmall" __alt="Restore" __title="Restore backup" __text="Restore"}
    </div>
</form>

{adminfooter}