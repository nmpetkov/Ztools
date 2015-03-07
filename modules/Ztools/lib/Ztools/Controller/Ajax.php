<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Ztools_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * This function sets active/inactive status.
     *
     * @param ip_adr
     *
     * @return true or Ajax error
     */
    public function getIpInfo()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Ztools::', '::', ACCESS_OVERVIEW));

        $ip_adr = $this->request->request->get('ip_adr', '');
        $alert = '';
        $content = '';
  
        if (!$ip_adr) {
            $alert .= $this->__('No IP passed.');
        } else {
            ob_start();
            $serviceName = 'ipinfo.io';
            $serviceLinks = 'http://ipinfo.io/' . $ip_adr; // to pass to tpl for visiting site
            @readfile('http://ipinfo.io/' . $ip_adr . '/json');
            $json = ob_get_contents();
            ob_end_clean();
            if ($json) {
                    try {
                        $item = json_decode($json, true);
                        if (false) {
                            ob_start();
                            pr($item);
                            $content .= ob_get_contents();
                            ob_end_clean();
                        } else {
                            if (is_array($item)) {
                                if (isset($item['country'])) {
                                    $item['country_name'] = ZLanguage::getCountryName($item['country']);
                                } else {
                                    $item['country_name'] = '';
                                }
                                Zikula_AbstractController::configureView();
                                $this->view->assign('item', $item);
                                $this->view->assign('serviceName', $serviceName);
                                $this->view->assign('serviceLinks', $serviceLinks);
                                $content .= $this->view->fetch('admin/ipinfo.tpl');
                            }
                        }
                    } catch (Exception $e) {
                        $content .= $e->getMessage();
                    }   
            } else {
                $alert .=  $this->__f('Could not get info.');
            }
        }

        return new Zikula_Response_Ajax(array('ip' => $ip_adr, 'content' => $content, 'alert' => $alert));
    }
}
