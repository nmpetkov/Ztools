<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Ztools_Installer extends Zikula_AbstractInstaller
{
    /**
     * Initializes a new install
     *
     * @return  boolean    true/false
     */
    public function install()
    {
        // Set up module config variables
        $this->setVar('ztools_backupsdir', 'userdata/Ztools/backups');
        $this->setVar('ztools_scriptsdir', 'userdata/Ztools/scripts');
        $this->setVar('ztools_scriptssort', '0');
        $this->setVar('ztools_scriptseditor', '1');
        $this->setVar('ztools_showphpinfo', '1');
        $this->setVar('ztools_downloaduseranges', '0');
        $this->setVar('ztools_url_cpanel', '');
        $this->setVar('ztools_url_phpmyadmin', '');
        $this->setVar('ztools_exportmethod', '2');
        $this->setVar('ztools_exportcompress', '0');
        $this->setVar('ztools_expmethodshow', '1');
        $this->setVar('ztools_mysqldumpexe', '');

        // Register hooks
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

        return true;
    }
    
    /**
     * Upgrade module
     *
     * @param   string    $oldversion
     * @return  boolean   true/false
     */
    public function upgrade($oldversion)
    {
        // upgrade dependent on old version number
        switch ($oldversion)
        {
            case '1.0.0':
                // Register hooks
                HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

            case '1.0.1':
				// future upgrade routines
        }

        return true;
    }
    
    /**
     * Delete module
     *
     * @return  boolean    true/false
     */
    public function uninstall()
    {
        $this->delVars();

        // Remove hooks
        HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());

        return true;
    }
}