{pageaddvar name='javascript' value='jquery'}
{if $coredata.version_num < '1.4.0'}
    {pageaddvar name='stylesheet' value='javascript/jquery-ui-1.12/themes/base/jquery-ui.css'}
    {*ajaxheader modname='Ztools' filename='ztools.js' noscriptaculous=true*}
    {pageaddvar name='javascript' value='javascript/jquery-ui-1.12/jquery-ui.min.js'}
{else}
    {pageaddvar name='stylesheet' value='web/jquery-ui/themes/base/jquery-ui.css'}
    {ajaxheader modname='Ztools' filename='ztools.js'}
{/if}
