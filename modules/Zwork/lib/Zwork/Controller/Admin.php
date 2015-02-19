<?php
/**
 * Zwork Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Zwork_Controller_Admin extends Zikula_AbstractController
{
    /**
     * Main administration function
     */
    public function main()
    {
        return $this->scripts();
    }
    /**
     * Modify module Config
     */
    public function modifyconfig()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        // Get module configuration vars
        $vars = $this->getVars();
        if (!isset($vars['zwork_url_cpanel'])) {
            $vars['zwork_url_cpanel'] = '';
        }
        if (!isset($vars['zwork_url_phpmyadmin'])) {
            $vars['zwork_url_phpmyadmin'] = '';
        }

        $this->view->assign('vars', $vars);
        $this->view->assign('scriptsdir_exist', is_dir($vars['zwork_scriptsdir']));
        
        return $this->view->fetch('admin/modifyconfig.tpl');
    }

    /**
     * Update module Config
     */
    public function updateconfig()
    {
        $this->checkCsrfToken();
        
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        $vars = array();
        $vars['zwork_scriptsdir'] = FormUtil::getPassedValue('zwork_scriptsdir', 'userdata/Zwork');
        $vars['zwork_url_cpanel'] = FormUtil::getPassedValue('zwork_url_cpanel', '');
        $vars['zwork_url_phpmyadmin'] = FormUtil::getPassedValue('zwork_url_phpmyadmin', '');
        $scriptsdir_createfolder = (bool)FormUtil::getPassedValue('scriptsdir_createfolder', false, 'POST');

        // set the new variables
        $this->setVars($vars);

        if ($scriptsdir_createfolder && !empty($vars['zwork_scriptsdir'])) {
            if (is_dir($vars['zwork_scriptsdir'])) {
                 LogUtil::registerStatus(__('Directory exists: ').$vars['zwork_scriptsdir']);
            } else {
                if (FileUtil::mkdirs($vars['zwork_scriptsdir'], 0777)) {
                    LogUtil::registerStatus(__('Directory is created: ').$vars['zwork_scriptsdir']);
                } else {
                    LogUtil::registerError(__('Can not create directory: ').$vars['zwork_scriptsdir']);
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
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        // Get module configuration vars
        $vars = $this->getVars();

        $scripts = array();

        if (empty($vars['zwork_scriptsdir'])) {
            LogUtil::registerError(__('Please specify in module settings directory for scripts to execute!'));
        } else {
            if (is_dir($vars['zwork_scriptsdir'])) {
                $files = scandir($vars['zwork_scriptsdir']);
                foreach ($files as $file) {
                    if (!is_dir($vars['zwork_scriptsdir'] . (substr($vars['zwork_scriptsdir'], -1) == '/' ? '' : '/') . $file)) {
                    $scripts[] = $file;
                    }
                }
            } else {
                LogUtil::registerError(__('Please visit module settings and create directory for scripts to execute!'));
            }
        }

        $this->view->assign('vars', $vars);
        $this->view->assign('scripts', $scripts);

        return $this->view->fetch('admin/main.tpl');
    }

    /**
     * Execute selected scripts
     */
    public function executescrips()
    {
        $this->checkCsrfToken();
        
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        // Get module configuration vars
        $vars = $this->getVars();

        $execute = FormUtil::getPassedValue('execute', array());
        $scripts = FormUtil::getPassedValue('scripts', array());

        $countexecutions = 0;
        foreach ($execute as $key => $value) {
            if ($value) {
                ob_start();
                include DataUtil::formatForOS($vars['zwork_scriptsdir'] . (substr($vars['zwork_scriptsdir'], -1) == '/' ? '' : '/') . $scripts[$key]);
                $content = ob_get_clean();
                $countexecutions ++;
                LogUtil::registerStatus('<strong>' . $this->__('Result from') .' '. $scripts[$key] . ':</strong>');
                LogUtil::registerStatus($content);
            }
        }
  
        if ($countexecutions > 0) {
            LogUtil::registerStatus($this->__('Done! Executed selected scripts.'));
        } else {
            LogUtil::registerStatus($this->__('Please select scripts to execute!'));
        }

        return $this->scripts();
    }

    /**
     * Display Server information
     */
    public function displaysysinfo()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

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
        $this->view->assign('phpinfo', $this->getphpinfoclean());
        $this->view->assign('site_root', System::serverGetVar('DOCUMENT_ROOT'));
        $this->view->assign('server_ip', System::serverGetVar('SERVER_ADDR'));
        $this->view->assign('server_port', System::serverGetVar('SERVER_PORT'));
        $this->view->assign('server_software', System::serverGetVar('SERVER_SOFTWARE'));

        return $this->view->fetch('admin/sysinfo.tpl');
    }

    /**
     * Display phpinfo() (new page)
     */
    public function displaysysinforaw()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        phpinfo();
        exit();
    }

    /**
     * Get cleaned phpinfo() content
     */
    public function getphpinfoclean()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        ob_start();
        phpinfo();
        // $matches [1]; # Style information
        // $matches [2]; # Body information
        preg_match('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', ob_get_clean(), $matches);

        ob_start();
        echo "<div class='phpinfodisplay'><style type='text/css'>\n",
            implode("\n",
                array_map(create_function('$i', 'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'),
                    preg_split('/\n/', trim(preg_replace("/\nbody/", "\n", $matches[1]))))
                ),
            "</style>\n",
            $matches[2],
            "\n</div>\n";

        return ob_get_clean();
    }


    /**
     * Display browser information
     */
    public function displaybrowserinfo()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

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
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

        echo System::serverGetVar('HTTP_COOKIE');
        exit();
    }
}