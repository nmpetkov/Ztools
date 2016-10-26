<?php
// Example for use Ztools module for Zikula
// As you can see, all Zikula environment is available (functions and classes)

echo 'Hello World example!<br /><br />';

// Example with your data (Zikula UserUtil)
echo 'Your username is ' . UserUtil::getVar('uname') . '<br /><br />';

/*echo 'And this is all info for you in the site:<br />';
echo '<pre>';
    print_r(UserUtil::getVars(UserUtil::getVar('uid')));
echo '</pre>';*/

// Example with php functions
echo '<pre>';
    print_r(getdate());
echo '</pre>';
