<?php
/**
 * Zwork Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Zwork_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();
    
        if (SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'scripts'),
                'text' => $this->__('Scripts'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'displaysysinfo'),
                'text' => $this->__('Server information'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'displaybrowserinfo'),
                'text' => $this->__('Client information'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN)) {
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
            LogUtil::registerError($this->__f('Error! File %s already exist.', $filenameWithpath));
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

    public function getScriptsDir()
    {
        $scriptsdir = $this->getVar('zwork_scriptsdir');

        return $scriptsdir . (substr($scriptsdir, -1) == '/' ? '' : '/');
    }
}