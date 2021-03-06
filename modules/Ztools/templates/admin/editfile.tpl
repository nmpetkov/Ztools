{if $vars.ztools_scriptseditor}
    {* add basic CodeMirror functionality *}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/lib/codemirror.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/addon/selection/active-line.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/addon/edit/matchbrackets.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/addon/edit/closebrackets.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/addon/edit/matchtags.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/addon/edit/closetag.js'}
    {pageaddvar name='stylesheet' value='modules/Ztools/javascript/vendor/codemirror/lib/codemirror.css'}
    {* add Javascript-mode dependencies *}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/mode/javascript/javascript.js'}
    {* add PHP-mode dependencies (replace dependency loading by require.js!) *}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/mode/xml/xml.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/mode/htmlmixed/htmlmixed.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/mode/clike/clike.js'}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/mode/php/php.js'}
    {* add SPARQL-mode dependencies *}
    {pageaddvar name='javascript' value='modules/Ztools/javascript/vendor/codemirror/mode/sparql/sparql.js'}
    {* codemirror style overwrites *}
    {pageaddvar name='stylesheet' value='modules/Ztools/style/codemirror.css'}
{/if}

{adminheader}

<div class="z-admin-content-pagetitle">
    {icon type="info" size="small"}
    <h3>{gt text='Edit script'}</h3>
</div>

<form class="z-form" action="{modurl modname="Ztools" type="admin" func="savescript"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    <input type="hidden" name="filename" value="{$filename}" />
    <fieldset>
        <legend>{gt text='Script'}</legend>
        <div>
        <label for="filecontent"><strong>{$filename}</strong></label>
        <textarea id="filecontent" name="filecontent" cols="100" rows="40">{$filecontent}</textarea>
{if $vars.ztools_scriptseditor}
<script>
  var editor = CodeMirror.fromTextArea(document.getElementById("filecontent"), {
    mode: "application/x-httpd-php",
    styleActiveLine: true,
    matchBrackets: true,
    autoCloseBrackets: true,
    lineNumbers: true,
    lineWrapping: true,
    autoCloseTags: true
  });
</script>
{/if}
        </div>
        <div>
            <label for="filesaveasnew">{gt text='Save as new file'}</label>
            <input id="filesaveasnew" type="text" name="filesaveasnew" size=40 maxlength=255 />
            <span class="z-sub z-italic">{gt text='If you enter here, the file will save as new with this name.'}</span>
        </div>
    </fieldset>

    {notifydisplayhooks eventname='ztools.ui_hooks.item.form_edit' id=null}

    <div class="z-buttons">
        <div>
        {button src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
        {button name="edit" value="1" src="edit.png" set="icons/extrasmall" __alt="Save and edit" __title="Save and edit" __text="Save and edit"}
        {button name="execute" value="1" src="exec.png" set="icons/extrasmall" __alt="Save and execute" __title="Save and execute" __text="Save and execute"}
        {button name="execedit" value="1" src="exec.png" set="icons/extrasmall" __alt="Execute and edit" __title="Execute and edit" __text="Execute and edit"}
        <a href="{modurl modname="Ztools" type="admin" func='main'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}