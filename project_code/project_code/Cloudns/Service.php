<?php

/**
 * FOSSBilling.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license   Apache-2.0
 *
 * Copyright FOSSBilling 2022
 * This software may contain code previously used in the BoxBilling project.
 * Copyright BoxBilling, Inc 2011-2021
 *
 * This source file is subject to the Apache-2.0 License that is bundled
 * with this source code in the file LICENSE
 */

/**
 * This file is a delegate for module. Class does not extend any other class.
 *
 * All methods provided in this example are optional, but function names are
 * still reserved.
 */

namespace Box\Mod\ClouDNS;

class Service
{
    protected $di;

    /**
     * @param mixed $di
     */
    public function setDi($di)
    {
        $this->di = $di;
    }


    public static function makeTldModel(\Box_Event $event, $orderArr){
        $di = $event->getDi();

        $tld = $orderArr['config']['register_tld'];

        $service = $di['mod_service']('servicedomain');
        $tldModel = $service->tldFindOneByTld($tld);

        $model = $di['db']->dispense('ServiceDomain');
        
        $model->client_id = $orderArr['client']['id'];
        $model->tld_registrar_id = $tldModel->tld_registrar_id;
        $model->sld = $orderArr['config']['register_sld'];
        $model->tld = $tld;
        $model->period = $orderArr['config']['register_years'];
        $model->transfer_code = $orderArr['config']['transfer_code'];
        $model->privacy = false; 
        $model->action = null;
        $model->ns1 = null;
        $model->ns2 = null;
        $model->ns3 = null;
        $model->ns4 = null;

        $model->contact_first_name = $orderArr['client']['first_name'];
        $model->contact_last_name = $orderArr['client']['last_name'];
        $model->contact_email = $orderArr['client']['email'];
        $model->contact_company = $orderArr['client']['company'];
        $model->contact_address1 = $orderArr['client']['address_1'];
        $model->contact_address2 = $orderArr['client']['address_2'];
        $model->contact_country = $orderArr['client']['country'];
        $model->contact_city = $orderArr['client']['city'];
        $model->contact_state = $orderArr['client']['state'];
        $model->contact_postcode = $orderArr['client']['postcode'];
        $model->contact_phone_cc = $orderArr['client']['phone_cc'];
        $model->contact_phone = $orderArr['client']['phone'];

        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');

        return $model;
    }

    public static function onAfterAdminOrderActivate(\Box_Event $event) {

        $di = $event->getDi();

        $params = $event->getParameters();
        $order_id = $params['id'];
        $service = $di['mod_service']('order');

        $order = $di['db']->getExistingModelById('ClientOrder', $order_id, 'Order not found');
        $identity = $di['loggedin_admin'];
        $s = $service->getOrderServiceData($order, $identity);
        $orderArr = $service->toApiArray($order, true, $identity);

        if($orderArr["service_type"] != "domain"){
            return;
        }

        $config = $di['mod_config']('Cloudns');
        if (!isset($config['auth_passwd'])) {
            error_log('Auth password is not entered.');
        } elseif (!isset($config['auth_id'])) {
            error_log('Auth ID is not entered.');
        } elseif (!isset($config['server_ip'])) {
            error_log('Server IP is not entered.');
        }

        $auth_id = (string) $config['auth_id'];
        $auth_passwd = (string) $config['auth_passwd'];
        $server_ip = (string) $config['server_ip'];

        $cloudns = new ClouDNS_SDK($auth_id, $auth_passwd, false);
        
        $cloudns->apiLogin();

        // $s = $service->getOrderServiceData($order, $identity);
        // $orderArr = $service->toApiArray($order, true, $identity);

        // error_log(print_r($orderArr, TRUE));

        $model = self::makeTldModel($event,$orderArr);

        $serviceDomain = $di['mod_service']('servicedomain');
        $tld = $orderArr['config']['register_tld'];
        $sld = $orderArr['config']['register_sld'];

        $cloudns->dnsRegisterDomainZone($sld.$tld, 'master');

        $cloudns->dnsAddRecord($sld.$tld, 'A', '', gethostbyname($server_ip), 60);
        $cloudns->dnsAddRecord($sld.$tld, 'CNAME', 'www', $sld.$tld, 60);

        // MX records are automatically populated with Google's mailservers by default.
        $cloudns->dnsAddRecord($sld.$tld, 'MX', '', 'ASPMX.L.GOOGLE.COM', 3600, 1);
        $cloudns->dnsAddRecord($sld.$tld, 'MX', '', 'ALT1.ASPMX.L.GOOGLE.COM', 3600, 5);
        $cloudns->dnsAddRecord($sld.$tld, 'MX', '', 'ALT2.ASPMX.L.GOOGLE.COM', 3600, 5);
        $cloudns->dnsAddRecord($sld.$tld, 'MX', '', 'ALT3.ASPMX.L.GOOGLE.COM', 3600, 10);
        $cloudns->dnsAddRecord($sld.$tld, 'MX', '', 'ALT4.ASPMX.L.GOOGLE.COM', 3600, 10);

        $nameservers = $cloudns->dnsAvailableNameServers();
        error_log($nameservers[4]['name']);
        error_log($nameservers[5]['name']);
        error_log($nameservers[6]['name']);
        error_log($nameservers[7]['name']);

        $data = array(            
            "ns1" => $nameservers[4]['name'],
            "ns2" => $nameservers[5]['name'],
            "ns3" => $nameservers[6]['name'],
            "ns4" => $nameservers[7]['name'],
        );
        
        $serviceDomain->updateNameservers($model,$data);    
        
    }

}
