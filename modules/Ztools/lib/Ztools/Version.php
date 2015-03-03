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
        $meta['url']            = $this->__(/*!module name that appears in URL*/'ztools');
        $meta['description']    = $this->__('System tools and code execution.');
        $meta['version']        = '1.0.1';
        $meta['securityschema'] = array('Ztools::' => '::');
        $meta['core_min']       = '1.3.0';
        $meta['capabilities']   = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));

        return $meta;
    }

    protected function setupHookBundles()
    {
        // Register hooks
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.ztools.ui_hooks.item', 'ui_hooks', $this->__('Ztools hooks'));
        $bundle->addEvent('display_view', 'ztools.ui_hooks.item.display_view');
        $bundle->addEvent('form_edit', 'ztools.ui_hooks.item.form_edit');
        $this->registerHookSubscriberBundle($bundle);
    }
}