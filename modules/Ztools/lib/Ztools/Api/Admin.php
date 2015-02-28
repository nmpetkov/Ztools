<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Ztools_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Get available admin panel links
     *
     * @return array array of admin links
     */
    public function getLinks()
    {
        $links = array();
    
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_READ)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'displaysysinfo'),
                'text' => $this->__('Server information'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_OVERVIEW)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'displaybrowserinfo'),
                'text' => $this->__('Client information'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_EDIT)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'backupdb'),
                'text' => $this->__('Backup database'),
                'class' => 'z-icon-es-export');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'scripts'),
                'text' => $this->__('Scripts'),
                'class' => 'z-icon-es-gears');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'modifyconfig'),
                'text' => $this->__('Settings'),
                'class' => 'z-icon-es-config');
        }

        return $links;
    }

    public function createFile($args)
    {
        $filecontent = isset($args['filecontent']) ? $args['filecontent'] : '';
        $filename = isset($args['filename']) ? $args['filename'] : '';

        if (empty($filename)) {
            LogUtil::registerError($this->__('Error! File name can not be empty.'));
            return false;
        } elseif (file_exists($filename)) {
            LogUtil::registerError($this->__f('Error! File %s already exist.', $filename));
            return false;
        }

        $handle = fopen($filename, 'w');
        if (!$handle) {
            LogUtil::registerError($this->__f('Error! Can not create file %s.', $filename));
            return false;
        }

        if ($filecontent) {
            if (!fwrite($handle, $filecontent)) {
                LogUtil::registerError($this->__f('Error! Can not write to file %s.', $filename));
                fclose($handle);
                return false;
            }
        }
        fclose($handle);

        return true;
    }

    public function executeScript($args)
    {
        $filename = isset($args['filename']) ? $args['filename'] : '';
        $filenameWithpath = DataUtil::formatForOS($this->getScriptsDir() . $filename);

        if (file_exists($filenameWithpath)) {
            ob_start();
            include $filenameWithpath;
            $content = ob_get_clean();
            LogUtil::registerStatus('<strong>' . $this->__f('Result from: %s', $filename) . '</strong>');
            LogUtil::registerStatus($content);
        } else {
            LogUtil::registerError($this->__f('Error! File does not exist: %s', $filenameWithpath));
            return false;
        }

        return true;
    }

    public function getBackupsDir()
    {
        $dir = $this->getVar('ztools_backupsdir');

        return $dir . (substr($dir, -1) == '/' ? '' : '/');
    }

    public function getScriptsDir()
    {
        $dir = $this->getVar('ztools_scriptsdir');

        return $dir . (substr($dir, -1) == '/' ? '' : '/');
    }

    public function getScriptsDirLockStatus($args)
    {
        $type = isset($args['type']) ? $args['type'] : ''; // 'human' to return string, otherwise return logical

        $htaccessFile = DataUtil::formatForOS($this->getScriptsDir() . '.htaccess');

        if (file_exists($htaccessFile)) {
            $filecontent = file_get_contents($htaccessFile);
            if (strpos($filecontent, 'deny from all') !== false) {
                return true;
            }
        }

        return false;
    }

    public function setScriptsDirLockStatus($args)
    {
        $type = isset($args['type']) ? $args['type'] : ''; // 'lock' or 'unlock'

        $htaccessFile = DataUtil::formatForOS($this->getScriptsDir() . '.htaccess');

        unlink($htaccessFile);
        if ($type == 'lock') {
            $handle = fopen($htaccessFile, 'w');
            if (!$handle) {
                LogUtil::registerError($this->__f('Error! Can not create file %s.', $htaccessFile));
                return false;
            }
            $filecontent = 'Order deny,allow' . PHP_EOL . 'deny from all' . PHP_EOL;
            if (!fwrite($handle, $filecontent)) {
                LogUtil::registerError($this->__f('Error! Can not write to file %s.', $htaccessFile));
                fclose($handle);
                return false;
            }
            fclose($handle);
        } elseif ($type == 'unlock') {
            if (file_exists($htaccessFile)) {
                LogUtil::registerError($this->__f('Error! File %s can not be deleted.', $htaccessFile));
                return false;
            }
        }

        return true;
    }

    /**
     * Get cleaned phpinfo() content
     */
    public function getphpinfoclean()
    {
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
            ".phpinfodisplay .center table { margin-left: 0; max-width: 600px; } .phpinfodisplay td { padding: 4px; }\n</style>\n",
            $matches[2],
            "\n</div>\n";

        return ob_get_clean();
    }
}