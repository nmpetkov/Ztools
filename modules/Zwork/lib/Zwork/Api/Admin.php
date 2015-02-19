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
                'url' => ModUtil::url('Zwork', 'admin', 'main'),
                'text' => $this->__('Scripts'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url('Zwork', 'admin', 'displaysysinfo'),
                'text' => $this->__('Server information'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url('Zwork', 'admin', 'displaybrowserinfo'),
                'text' => $this->__('Client information'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Zwork::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url('Zwork', 'admin', 'modifyconfig'),
                'text' => $this->__('Settings'),
                'class' => 'z-icon-es-config');
        }

        return $links;
    }
}