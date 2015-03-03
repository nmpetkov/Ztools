{ajaxheader modname='ztools' filename='ztools_admin_display.js' nobehaviour=true noscriptaculous=true effects=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text='Module settings'}</h3>
</div>

<form class="z-form" action="{modurl modname='Ztools' type='admin' func='updateconfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <fieldset>
        <legend>{gt text='General settings'}</legend>
        <div class="z-formrow">
            <label for="ztools_url_cpanel">{gt text='Hosting admin URL'}</label>
            <input id="ztools_url_cpanel" type="text" name="ztools_url_cpanel" value="{$vars.ztools_url_cpanel|safetext}" />
            <div class="z-informationmsg z-formnote">
                {gt text='Enter here URL address to your hosting provider admin panel (Cpanel or other).'}<br />
                {gt text='Link will appear in the info page for easy access.'}
            </div>
        </div>
        <div class="z-formrow">
            <label for="ztools_url_phpmyadmin">{gt text='Database admin URL'}</label>
            <input id="ztools_url_phpmyadmin" type="text" name="ztools_url_phpmyadmin" value="{$vars.ztools_url_phpmyadmin|safetext}" />
            <div class="z-informationmsg z-formnote">
                {gt text='Enter here URL address to your database admin panel (phpMyAdmin or other).'}<br />
                {gt text='Link will appear in the info page for easy access.'}
            </div>
        </div>
        <div class="z-formrow">
            <label for="ztools_showphpinfo">{gt text="Display PHP info"}</label>
            <select id="ztools_showphpinfo" name="ztools_showphpinfo" size="1">
                <option value="0"{if $vars.ztools_showphpinfo == "0"} selected="selected"{/if}>{gt text="With link in new page"}</option>
                <option value="1"{if $vars.ztools_showphpinfo == "1"} selected="selected"{/if}>{gt text="Always on info page"}</option>
                <option value="2"{if $vars.ztools_showphpinfo == "2"} selected="selected"{/if}>{gt text="Never"}</option>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text='Backups'}</legend>
        <div class="z-formrow">
            <label for="ztools_backupsdir">{gt text='Default backups directory'}</label>
            <input id="ztools_backupsdir" type="text" name="ztools_backupsdir" value="{$vars.ztools_backupsdir|safetext}" />
            {if $backupsdir_exist}
            <div class="z-informationmsg z-formnote">{gt text='Directory exist.'}
            {else}
            <div class="z-warningmsg z-formnote">{gt text='Directory does not exist!'}
            {/if}
            <br />{gt text='This is the place, where database backup files are stored.'}
            </div>
        </div>
        {if !$backupsdir_exist}
        <div class="z-formrow">
            <label for="backupsdir_createfolder">{gt text='Create specified directory'}</label>
            <input id="backupsdir_createfolder" type="checkbox" name="backupsdir_createfolder" />
        </div>
        {/if}
        <div class="z-formrow">
            <label for="ztools_exportmethod">{gt text="Default export method"}</label>
            <select id="ztools_exportmethod" name="ztools_exportmethod" size="1">
                <option value="1"{if $vars.ztools_exportmethod == "1"} selected="selected"{/if}>{gt text="Mysqldump php"} ({gt text="pure php/sql code"})</option>
                <option value="2"{if $vars.ztools_exportmethod == "2"} selected="selected"{/if}>{gt text="Mysqldump shell"} ({gt text="call to mysqldump executable - fastest, but depend on server rights"})</option>
                <option value="3"{if $vars.ztools_exportmethod == "3"} selected="selected"{/if}>{gt text="Ztools native"} ({gt text="pure php/sql code"})</option>
            </select>
        </div>
        <div class="z-formrow">
            <label for="ztools_mysqldumpexe">{gt text='Mysqldump executable'}<br />
                <div class="z-sub z-formnote">{gt text='Only for method'} `{gt text="Mysqldump shell"}`</div>
            </label>
            <input id="ztools_mysqldumpexe" type="text" name="ztools_mysqldumpexe" value="{$vars.ztools_mysqldumpexe|safetext}" />
            <div class="z-informationmsg z-formnote">
                {if empty($vars.ztools_mysqldumpexe)}
                {elseif $mysqldumpexe_exist}
                    {gt text='File exist.'} 
                {else}
                    {gt text='File does not exist!'} 
                {/if}
                {gt text='Ordinary path is `/usr/bin/mysqldump`, but leave empty if you are not sure, this is OK for most cases.'}
            </div>
        </div>
        <div class="z-formrow">
            <label for="ztools_expmethodshow">{gt text="Display export method selector"}</label>
            <select id="ztools_expmethodshow" name="ztools_expmethodshow" size="1">
                <option value="0"{if $vars.ztools_expmethodshow == "0"} selected="selected"{/if}>{gt text="No"}</option>
                <option value="1"{if $vars.ztools_expmethodshow == "1"} selected="selected"{/if}>{gt text="Yes"}</option>
            </select>
        </div>
        <div class="z-formrow">
            <label for="ztools_exportcompress">{gt text="Compression"}</label>
            <select id="ztools_exportcompress" name="ztools_exportcompress" size="1">
                <option value="0"{if $vars.ztools_exportcompress == "0"} selected="selected"{/if}>{gt text="None"}</option>
                <option value="1"{if $vars.ztools_exportcompress == "1"} selected="selected"{/if}>{gt text="Gzip"}</option>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text='Scripts'}</legend>
        <div class="z-formrow">
            <label for="ztools_scriptsdir">{gt text='Default scripts directory'}</label>
            <input id="ztools_scriptsdir" type="text" name="ztools_scriptsdir" value="{$vars.ztools_scriptsdir|safetext}" />
            {if $scriptsdir_exist}
            <div class="z-informationmsg z-formnote">{gt text='Directory exist.'}
            {else}
            <div class="z-warningmsg z-formnote">{gt text='Directory does not exist!'}
            {/if}
            <br />{gt text='This is the place, where you upload scripts to execute.'} {gt text='See example.php for sample.'}
            </div>
        </div>
        {if !$scriptsdir_exist}
        <div class="z-formrow">
            <label for="scriptsdir_createfolder">{gt text='Create specified directory'}</label>
            <input id="scriptsdir_createfolder" type="checkbox" name="scriptsdir_createfolder" />
        </div>
        {/if}
        <div class="z-formrow">
            <label for="ztools_scriptssort">{gt text="Display scripts"}</label>
            <select id="ztools_scriptssort" name="ztools_scriptssort" size="1">
                <option value="0"{if $vars.ztools_scriptssort == "0"} selected="selected"{/if}>{gt text="Not sorted"}</option>
                <option value="1"{if $vars.ztools_scriptssort == "1"} selected="selected"{/if}>{gt text="Alphabetical order"}</option>
            </select>
        </div>
        <div class="z-formrow">
            <label for="ztools_scriptseditor">{gt text="Internal editor for scripts"}</label>
            <select id="ztools_scriptseditor" name="ztools_scriptseditor" size="1">
                <option value="0"{if $vars.ztools_scriptseditor == "0"} selected="selected"{/if}>{gt text="None"}</option>
                <option value="1"{if $vars.ztools_scriptseditor == "1"} selected="selected"{/if}>{gt text="CodeMirror"}</option>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text='Download files'}</legend>
        <div class="z-formrow">
            <label for="ztools_downloaduseranges">{gt text="Use HTTP ranges"}</label>
            <input id="ztools_downloaduseranges" type="checkbox" name="ztools_downloaduseranges" value="1" {if $vars.ztools_downloaduseranges}checked="checked"{/if} />
            <div class="z-informationmsg z-formnote">{gt text='This allow client (browser) to resume aborted downloads. Disable if broken files are received.'}</div>
        </div>
    </fieldset>

    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
        <a href="{modurl modname="Ztools" type="admin" func='main'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>
{adminfooter}