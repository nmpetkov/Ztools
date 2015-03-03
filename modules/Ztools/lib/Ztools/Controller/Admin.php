<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Ztools_Controller_Admin extends Zikula_AbstractController
{
    /**
     * Main administration function
     */
    public function main()
    {
        return $this->displaysysinfo();
    }
    /**
     * Modify module Config
     */
    public function modifyconfig()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        // Get module configuration vars
        $vars = $this->getVars();
        if (!isset($vars['ztools_url_cpanel'])) {
            $vars['ztools_url_cpanel'] = '';
        }
        if (!isset($vars['ztools_url_phpmyadmin'])) {
            $vars['ztools_url_phpmyadmin'] = '';
        }
        if (!isset($vars['ztools_scriptssort'])) {
            $vars['ztools_scriptssort'] = '0';
        }
        if (!isset($vars['ztools_scriptseditor'])) {
            $vars['ztools_scriptseditor'] = '1';
        }
        if (!isset($vars['ztools_showphpinfo'])) {
            $vars['ztools_showphpinfo'] = '1';
        }
        if (!isset($vars['ztools_downloaduseranges'])) {
            $vars['ztools_downloaduseranges'] = '0';
        }
        if (!isset($vars['ztools_exportmethod'])) {
            $vars['ztools_exportmethod'] = '1';
        }
        if (!isset($vars['ztools_exportcompress'])) {
            $vars['ztools_exportcompress'] = '0';
        }
        if (!isset($vars['ztools_expmethodshow'])) {
            $vars['ztools_expmethodshow'] = '1';
        }
        if (!isset($vars['ztools_mysqldumpexe'])) {
            $vars['ztools_mysqldumpexe'] = '';
        }

        $this->view->assign('vars', $vars);
        $this->view->assign('scriptsdir_exist', is_dir($vars['ztools_scriptsdir']));
        $this->view->assign('backupsdir_exist', is_dir($vars['ztools_backupsdir']));
        $this->view->assign('mysqldumpexe_exist', @file_exists($vars['ztools_mysqldumpexe']));

        return $this->view->fetch('admin/modifyconfig.tpl');
    }

    /**
     * Update module Config
     */
    public function updateconfig()
    {
        $this->checkCsrfToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $vars = array();
        $vars['ztools_backupsdir'] = FormUtil::getPassedValue('ztools_backupsdir', 'userdata/Ztools/backups');
        $vars['ztools_scriptsdir'] = FormUtil::getPassedValue('ztools_scriptsdir', 'userdata/Ztools/scripts');
        $vars['ztools_scriptssort'] = FormUtil::getPassedValue('ztools_scriptssort', '0');
        $vars['ztools_scriptseditor'] = FormUtil::getPassedValue('ztools_scriptseditor', '1');
        $vars['ztools_showphpinfo'] = FormUtil::getPassedValue('ztools_showphpinfo', '0');
        $vars['ztools_downloaduseranges'] = FormUtil::getPassedValue('ztools_downloaduseranges', "0");
        $vars['ztools_url_cpanel'] = FormUtil::getPassedValue('ztools_url_cpanel', '');
        $vars['ztools_url_phpmyadmin'] = FormUtil::getPassedValue('ztools_url_phpmyadmin', '');
        $vars['ztools_exportmethod'] = FormUtil::getPassedValue('ztools_exportmethod', '1');
        $vars['ztools_exportcompress'] = FormUtil::getPassedValue('ztools_exportcompress', '0');
        $vars['ztools_expmethodshow'] = FormUtil::getPassedValue('ztools_expmethodshow', '1');
        $vars['ztools_mysqldumpexe'] = FormUtil::getPassedValue('ztools_mysqldumpexe', '');
        $scriptsdir_createfolder = (bool)FormUtil::getPassedValue('scriptsdir_createfolder', false, 'POST');
        $backupsdir_createfolder = (bool)FormUtil::getPassedValue('backupsdir_createfolder', false, 'POST');

        // set the new variables
        $this->setVars($vars);

        if ($backupsdir_createfolder && !empty($vars['ztools_backupsdir'])) {
            if (is_dir($vars['ztools_backupsdir'])) {
                 LogUtil::registerStatus($this->__f('Directory exists: %s.', $vars['ztools_backupsdir']));
            } else {
                if (FileUtil::mkdirs($vars['ztools_backupsdir'], 0777)) {
                    LogUtil::registerStatus($this->__f('Directory is created: %s.', $vars['ztools_backupsdir']));
                } else {
                    LogUtil::registerError($this->__f('Can not create directory %s.', $vars['ztools_backupsdir']) 
                    .'<br />'. $this->__('Please create it manually, for example with FTP client.'));
                }
            }
        }

        if ($scriptsdir_createfolder && !empty($vars['ztools_scriptsdir'])) {
            if (is_dir($vars['ztools_scriptsdir'])) {
                 LogUtil::registerStatus($this->__f('Directory exists: %s.', $vars['ztools_scriptsdir']));
            } else {
                if (FileUtil::mkdirs($vars['ztools_scriptsdir'], 0777)) {
                    LogUtil::registerStatus($this->__f('Directory is created: %s.', $vars['ztools_scriptsdir']) 
                    .'<br />'. $this->__('Please create it manually, for example with FTP client.'));
                } else {
                    LogUtil::registerError($this->__f('Can not create directory %s.', $vars['ztools_scriptsdir']));
                }
            }
        }

        // clear the cache
        $this->view->clear_cache();
    
        LogUtil::registerStatus($this->__('Done! Updated configuration.'));
        return $this->modifyconfig();
    }

    /**
     * Display scripts to execute
     */
    public function scripts()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        // Get module configuration vars
        $vars = $this->getVars();

        $scripts = array();

        if (empty($vars['ztools_scriptsdir'])) {
            LogUtil::registerError($this->__('Please specify in module settings directory for scripts to execute!'));
        } else {
            if (is_dir($vars['ztools_scriptsdir'])) {
                if (phpversion() >= '5.4.0') {
                    $files = scandir($vars['ztools_scriptsdir'], SCANDIR_SORT_NONE);
                } else {
                    $files = scandir($vars['ztools_scriptsdir']);
                }
                if ($vars['ztools_scriptssort']) {
                    natcasesort($files);
                }
            
                $scriptsDir = ModUtil::apiFunc($this->name, 'admin', 'getScriptsDir');
                foreach ($files as $key => $file) {
                    if (!is_dir($scriptsDir . $file)) {
                        $scripts[$key]['name'] = $file;
                        $scripts[$key]['size'] = filesize($scriptsDir . $file);
                    }
                }
            } else {
                LogUtil::registerError($this->__('Please visit module settings and create directory for scripts to execute!'));
            }
        }

        $this->view->assign('vars', $vars);
        $this->view->assign('scripts', $scripts);
        $this->view->assign('scriptsdirlockstatus', ModUtil::apiFunc($this->name, 'admin', 'getScriptsDirLockStatus'));

        return $this->view->fetch('admin/scripts.tpl');
    }

    /**
     * Execute selected scripts
     */
    public function executescripts()
    {
        $this->checkCsrfToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $execute = FormUtil::getPassedValue('execute', array());
        $scripts = FormUtil::getPassedValue('scripts', array());
        $newSriptFilename = FormUtil::getPassedValue('newSriptFilename', '');
        
        // create file, if given
        if ($newSriptFilename) {
            $newSriptFilename = pathinfo($newSriptFilename, PATHINFO_FILENAME) . '.php'; // force php extension
            $newSriptFilename = $this->getScriptFullPath($newSriptFilename);
            $filecontent = '<?php' . PHP_EOL;
            $fileIsCreated = ModUtil::apiFunc($this->name, 'admin', 'createFile', array('filename' => $newSriptFilename, 'filecontent' => $filecontent));
            if ($fileIsCreated) {
                LogUtil::registerStatus($this->__f('New file %s is created.', $newSriptFilename));
            }
        }

        // execute selected scripts
        $countexecutions = 0;
        foreach ($execute as $key => $value) {
            if ($value) {
                ModUtil::apiFunc($this->name, 'admin', 'executeScript', array('filename' => $scripts[$key]));
                $countexecutions ++;
            }
        }
  
        if ($countexecutions > 0) {
            LogUtil::registerStatus($this->__('Done! Executed selected scripts.'));
        } else {
            if (!$newSriptFilename) {
                LogUtil::registerStatus($this->__('Please select scripts to execute!'));
            }
        }

        return $this->scripts();
    }

    /**
     * Execute one script (can be called with GET parameter)
     */
    public function executescript($args)
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $filename = isset($args['filename']) ? $args['filename'] : FormUtil::getPassedValue('filename', null, 'REQUEST');
        $postedit = isset($args['postedit']) ? $args['postedit'] : FormUtil::getPassedValue('postedit', 0, 'REQUEST');

        // execute given script
        ModUtil::apiFunc($this->name, 'admin', 'executeScript', array('filename' => $filename));
  
        if ($postedit) {
            return System::redirect(ModUtil::url($this->name, 'admin', 'editfile', array('filename' => $filename)));
        }
        return $this->scripts();
    }

    /**
     * Display Server information
     */
    public function displaysysinfo()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_READ), LogUtil::getErrorMsgPermission());

        // Get module configuration vars
        $vars = $this->getVars();
        $connection = $this->entityManager->getConnection();
        $dbparams = $connection->getParams();
        // $connection->getDatabasePlatform()->getName() - returns mysql
        $dbserverversion = '';
        if ($connection->getDatabasePlatform()->getName() == "mysql") {
            $stmt = $connection->prepare("SHOW VARIABLES LIKE 'version'");
            $stmt->execute();
            $result = $stmt->fetchAll(Doctrine_Core::FETCH_ASSOC);
            if ($result) {
                $dbserverversion = 'MySQL ' . $result[0]['Value'];
            }
        }

        $this->view->assign('vars', $vars);
        $this->view->assign('dbserverversion', $dbserverversion);
        $this->view->assign('dbparams', $dbparams);
        $this->view->assign('phpos', PHP_OS);
        $this->view->assign('phpinfo', ModUtil::apiFunc($this->name, 'admin', 'getphpinfoclean'));
        $this->view->assign('site_root', System::serverGetVar('DOCUMENT_ROOT'));
        $this->view->assign('server_ip', System::serverGetVar('SERVER_ADDR'));
        $this->view->assign('server_port', System::serverGetVar('SERVER_PORT'));
        $this->view->assign('server_software', System::serverGetVar('SERVER_SOFTWARE'));
        $this->view->assign('server_phpversion', phpversion());

        return $this->view->fetch('admin/sysinfo.tpl');
    }

    /**
     * Display phpinfo() (new page)
     */
    public function displaysysinforaw()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_OVERVIEW), LogUtil::getErrorMsgPermission());

        phpinfo();
        exit();
    }

    /**
     * Display browser information
     */
    public function displaybrowserinfo()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_OVERVIEW), LogUtil::getErrorMsgPermission());

        $this->view->assign('user_ip', System::serverGetVar('REMOTE_ADDR'));
        $this->view->assign('user_port', System::serverGetVar('REMOTE_PORT'));
        $this->view->assign('user_agent', System::serverGetVar('HTTP_USER_AGENT'));
        $this->view->assign('user_lang', System::serverGetVar('HTTP_ACCEPT_LANGUAGE'));
        $this->view->assign('user_accept', System::serverGetVar('HTTP_ACCEPT'));
        $this->view->assign('user_cookies', System::serverGetVar('HTTP_COOKIE'));

        return $this->view->fetch('admin/clientinfo.tpl');
    }

    /**
     * Display cookies (new page)
     */
    public function displaycookies()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_OVERVIEW), LogUtil::getErrorMsgPermission());

        echo System::serverGetVar('HTTP_COOKIE');
        exit();
    }

    /**
     * Edit a script (php) file in scripts directory
     */
    public function editfile($args)
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $filename = isset($args['filename']) ? $args['filename'] : FormUtil::getPassedValue('filename', null, 'REQUEST');

        if (empty($filename)) {
            LogUtil::registerArgsError();
            return System::redirect(ModUtil::url($this->name, 'admin', 'scripts'));
        }
        $filenameWithpath = $this->getScriptFullPath($filename);

        if (file_exists($filenameWithpath)) {
            $filecontent = file_get_contents($filenameWithpath);
        } else {
            LogUtil::registerError($this->__f('Error! File does not exist: %s', $filenameWithpath));
            return System::redirect(ModUtil::url($this->name, 'admin', 'scripts'));
        }

        // Get module configuration vars
        $vars = $this->getVars();

        $this->view->assign('vars', $vars);
        $this->view->assign('filename', $filename);
        $this->view->assign('filecontent', $filecontent);

        return $this->view->fetch('admin/editfile.tpl');
    }

    /**
     * Delete a script file in scripts directory
     */
    public function deletefile($args)
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $filename = isset($args['filename']) ? $args['filename'] : FormUtil::getPassedValue('filename', null, 'REQUEST');

        if (empty($filename)) {
            LogUtil::registerArgsError();
            return System::redirect(ModUtil::url($this->name, 'admin', 'scripts'));
        }
        $filenameWithpath = $this->getScriptFullPath($filename);

        if (file_exists($filenameWithpath)) {
            if (unlink($filenameWithpath)) {
                LogUtil::registerStatus($this->__f('File %s is deleted.', $filenameWithpath));
            } else {
                LogUtil::registerError($this->__f('Error! File %s can not be deleted.', $filenameWithpath));
            }
        } else {
            LogUtil::registerStatus($this->__f('File does not exist: %s', $filenameWithpath));
        }

        return System::redirect(ModUtil::url($this->name, 'admin', 'scripts'));
    }

    /**
     * Download a file from scripts directory
     */
    public function downloadscript($args)
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $filename = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'REQUEST');
        if (empty($filename)) {
            LogUtil::registerArgsError();
            return false;
        }

        // Get module configuration vars
        $vars = $this->getVars();

        ModUtil::apiFunc($this->name, 'admin', 'downloadFile', array('filename' => $this->getScriptFullPath($filename), 'useranges' => $vars['ztools_downloaduseranges']));

        return System::redirect(ModUtil::url($this->name, 'admin', 'scripts'));
    }

    /**
     * Save edited script
     */
    public function savescript()
    {
        $this->checkCsrfToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $filename = FormUtil::getPassedValue('filename', '');
        $filecontent = FormUtil::getPassedValue('filecontent', '');
        $edit = FormUtil::getPassedValue('edit', 0);
        $execute = FormUtil::getPassedValue('execute', 0);
        $execedit = FormUtil::getPassedValue('execedit', 0);
        $filesaveasnew = FormUtil::getPassedValue('filesaveasnew', '');

        if (empty($filesaveasnew)) {
            if (empty($filename)) {
                LogUtil::registerError($this->__('Error! File name is empty.'));
            }
        } else {
            $filesaveasnew = pathinfo($filesaveasnew, PATHINFO_FILENAME) . '.php'; // force php extension
            $filenameWithpath = $this->getScriptFullPath($filesaveasnew);
            $fileIsCreated = ModUtil::apiFunc($this->name, 'admin', 'createFile', array('filename' => $filenameWithpath, 'filecontent' => $filecontent));
            if ($fileIsCreated) {
                $filename = $filesaveasnew;
                LogUtil::registerStatus($this->__f('New file %s is created.', $filename));
            }
        }
        $filenameWithpath = $this->getScriptFullPath($filename);

        if (file_exists($filenameWithpath)) {
            if (file_put_contents($filenameWithpath, $filecontent) === false) {
                LogUtil::registerError($this->__f('Error! Can not write content to file %s.', $filenameWithpath));
            } else {
                LogUtil::registerStatus($this->__f('Done! File %s is saved.', $filenameWithpath));
            }
        } else {
            LogUtil::registerError($this->__f('Error! File %s does not exist.', $filenameWithpath));
        }

        if ($edit) {
            return System::redirect(ModUtil::url($this->name, 'admin', 'editfile', array('filename' => $filename)));
        } elseif ($execute) {
            ModUtil::apiFunc($this->name, 'admin', 'executeScript', array('filename' => $filename));
        } elseif ($execedit) {
            return $this->executescript(array('filename' => $filename, 'postedit' => 1));
        }

        return System::redirect(ModUtil::url($this->name, 'admin', 'scripts'));
    }

    /**
     * Save edited script
     */
    public function lockscriptsdir($args)
    {
        $this->checkCsrfToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $lock = FormUtil::getPassedValue('lock', '');
        $unlock = FormUtil::getPassedValue('unlock', '');
        $type = '';
        if ($lock) {
             $type = 'lock';
        } elseif ($unlock) {
             $type = 'unlock';
        }

        ModUtil::apiFunc($this->name, 'admin', 'setScriptsDirLockStatus', array('type' => $type));

        return System::redirect(ModUtil::url($this->name, 'admin', 'scripts'));
    }

    /**
     * Backup database
     */
    public function backupdb()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_EDIT), LogUtil::getErrorMsgPermission());

        // Get module configuration vars
        $vars = $this->getVars();

        $backups = array();
        $filessize = 0;

        if (empty($vars['ztools_backupsdir'])) {
            LogUtil::registerError($this->__('Please specify in module settings directory for backups to store!'));
        } else {
            if (is_dir($vars['ztools_backupsdir'])) {
                $files = scandir($vars['ztools_backupsdir'], 1); // descending order

                $backupsDir = ModUtil::apiFunc($this->name, 'admin', 'getBackupsDir');
                foreach ($files as $key => $file) {
                    if (!is_dir($backupsDir . $file)) {
                        $backups[$key]['name'] = $file;
                        $fileSize = filesize($backupsDir . $file);
                        $filessize += $fileSize;
                        $backups[$key]['size'] = $fileSize;
                    }
                }
            } else {
                LogUtil::registerError($this->__('Please visit module settings and create directory for backups to store!'));
            }
        }
        
        // List with tables in database
        $tables = ModUtil::apiFunc($this->name, 'backup', 'getTables');

        $this->view->assign('vars', $vars);
        $this->view->assign('backups', $backups);
        $this->view->assign('filescount', count($backups));
        $this->view->assign('filessize', $filessize);
        $this->view->assign('tables', $tables);
        $this->view->assign('tablestotal', count($tables));

        return $this->view->fetch('admin/backupdb.tpl');
    }

    /**
     * Execute action in beckupdb
     */
    public function executebackupdb()
    {
        $this->checkCsrfToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_EDIT), LogUtil::getErrorMsgPermission());

        $create = FormUtil::getPassedValue('create', 0);
        $delete = FormUtil::getPassedValue('delete', 0);
        $download = FormUtil::getPassedValue('download', 0);
        $restore = FormUtil::getPassedValue('restore', 0);
        $past_backup = FormUtil::getPassedValue('past_backup', false);
        $tablestoexport = FormUtil::getPassedValue('tablestoexport', null);
        $tablestotal = FormUtil::getPassedValue('tablestotal', 0);
        $selectedtables = FormUtil::getPassedValue('selectedtables', 0);
        $export_method = FormUtil::getPassedValue('export_method', 1);
        $export_compress = FormUtil::getPassedValue('export_compress', 0);

        if ($create) {
            // Create backup
            // sample: 2015-02_28 22-56-16_cmstory1_climbingguidebg_structure.sql
            // Only stem, extension will add createBackup
            $connection = $this->entityManager->getConnection();
            $dbparams = $connection->getParams();
            $today = getdate(); 
            $backupFilename = $today['year'].'-'.str_pad($today['mon'], 2, '0', STR_PAD_LEFT).'-'.str_pad($today['mday'], 2, '0', STR_PAD_LEFT)
                .'_'.str_pad($today['hours'], 2, '0', STR_PAD_LEFT).'-'.str_pad($today['minutes'], 2, '0', STR_PAD_LEFT).'-'.str_pad($today['seconds'], 2, '0', STR_PAD_LEFT)
                .'_'.$dbparams['dbname'];
            $args = array();
            $tablesCountInfo = '_tables-all';
            if ($selectedtables && is_array($tablestoexport)) {
                $tablestoexport_count = count($tablestoexport);
                if ($tablestoexport_count > 0) {
                    $args['tables'] = $tablestoexport;
                    // add info for tables to the archive name
                    if ($tablestoexport_count < $tablestotal) {
                        $tablesCountInfo = '_tables-' . $tablestoexport_count;
                    }
                }
            }
            $backupFilename .= $tablesCountInfo . '-' . $tablestotal;
            $backupFilename .= '_m-' . $export_method;
            $backupFilename .= '.sql';
            if ($export_compress == 1) {
                $backupFilename .= '.gz';
            }
            $args['filename'] = $this->getBackupFullPath($backupFilename);
            $args['export_method'] = $export_method;
            $args['export_compress'] = $export_compress;

            ModUtil::apiFunc($this->name, 'admin', 'createBackup', $args);
        }

        if ($delete) {
            // Delete selected files
            if (is_array($past_backup)) {
                foreach ($past_backup as $filename) {
                    if (!empty($filename)) {
                        $filenameWithpath = $this->getBackupFullPath($filename);

                        if (file_exists($filenameWithpath)) {
                            if (unlink($filenameWithpath)) {
                                LogUtil::registerStatus($this->__f('File %s is deleted.', $filenameWithpath));
                            } else {
                                LogUtil::registerError($this->__f('Error! File %s can not be deleted.', $filenameWithpath));
                            }
                        } else {
                            LogUtil::registerStatus($this->__f('File does not exist: %s', $filenameWithpath));
                        }
                    }
                }
            }
        }

        if ($restore) {
            // Restore from existing backup file
            if (empty($past_backup)) {
                 LogUtil::registerStatus($this->__('Please select a file from the list.'));
            } else {
                // here to check to see if this is last backup!
                $vars = $this->getVars();
                 LogUtil::registerStatus('Not yet ready!');
                //ModUtil::apiFunc($this->name, 'admin', 'restoreBackup', array('filename' => $this->getBackupFullPath($past_backup[0])));
            }
        }

        if ($download) {
            // Download existing backup file
            if (empty($past_backup)) {
                 LogUtil::registerStatus($this->__('Please select a file from the list.'));
            } else {
                $vars = $this->getVars();
                ModUtil::apiFunc($this->name, 'admin', 'downloadFile', array('filename' => $this->getBackupFullPath($past_backup[0]), 'useranges' => $vars['ztools_downloaduseranges']));
            }
        }

        return System::redirect(ModUtil::url($this->name, 'admin', 'backupdb'));
    }

    private function getScriptFullPath($filename)
    {
        $scriptsdir = ModUtil::apiFunc($this->name, 'admin', 'getScriptsDir');

        return DataUtil::formatForOS($scriptsdir . $filename);
    }

    private function getBackupFullPath($filename)
    {
        $scriptsdir = ModUtil::apiFunc($this->name, 'admin', 'getBackupsDir');

        return DataUtil::formatForOS($scriptsdir . $filename);
    }
}
