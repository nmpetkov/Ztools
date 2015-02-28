{* add basic CodeMirror functionality *}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/lib/codemirror.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/addon/selection/active-line.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/addon/edit/matchbrackets.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/addon/edit/closebrackets.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/addon/edit/matchtags.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/addon/edit/closetag.js'}
{pageaddvar name='stylesheet' value='modules/Zwork/javascript/vendor/codemirror/lib/codemirror.css'}
{* add Javascript-mode dependencies *}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/mode/javascript/javascript.js'}
{* add PHP-mode dependencies (replace dependency loading by require.js!) *}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/mode/xml/xml.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/mode/htmlmixed/htmlmixed.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/mode/clike/clike.js'}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/mode/php/php.js'}
{* add SPARQL-mode dependencies *}
{pageaddvar name='javascript' value='modules/Zwork/javascript/vendor/codemirror/mode/sparql/sparql.js'}
{* codemirror style overwrites *}
{pageaddvar name='stylesheet' value='modules/Zwork/style/codemirror.css'}

{adminheader}

<div class="z-admin-content-pagetitle">
    {icon type="info" size="small"}
    <h3>{gt text='Edit script'}</h3>
</div>

<form class="z-form" action="{modurl modname="Zwork" type="admin" func="savescript"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    <input type="hidden" name="filename" value="{$filename}" />
    <fieldset>
        <legend>{gt text='Script'}</legend>
        <div class="z-formrow">
            <div>
            <label for="filecontent">{$filename}</label>
            <textarea id="filecontent" name="filecontent" cols="100" rows="40">{$filecontent}</textarea>
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
            </div>
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
        {button name="edit" value="1" src="edit.png" set="icons/extrasmall" __alt="Save and edit" __title="Save and edit" __text="Save and edit"}
        {button name="execute" value="1" src="exec.png" set="icons/extrasmall" __alt="Save and execute" __title="Save and execute" __text="Save and execute"}
        <a href="{modurl modname="Zwork" type="admin" func='main'}" title="{gt text="Cancel"}">{img modname=core src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>
{adminfooter}