<?php
/**
 * Zwork Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Zwork_Installer extends Zikula_AbstractInstaller
{
    /**
     * Initializes a new install
     *
     * @return  boolean    true/false
     */
    public function install()
    {
        // Set up module config variables
        $this->setVar('zwork_scriptsdir', 'userdata/Zwork/scripts');
        $this->setVar('zwork_url_cpanel', '');
        $this->setVar('zwork_url_phpmyadmin', '');

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

        return true;
    }
}