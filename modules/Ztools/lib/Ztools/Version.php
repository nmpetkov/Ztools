<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Ztools_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('System tools');
        $meta['url']            = $this->__('ztools');
        $meta['description']    = $this->__('System tools and code execution.');
        $meta['version']        = '1.0.0';
        $meta['securityschema'] = array('Ztools::' => '::');
        $meta['core_min']       = '1.3.0';

        return $meta;
    }
}