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
 * This file connects FOSSBilling admin area interface and API
 * Class does not extend any other class.
 */

namespace Box\Mod\ClouDNS\Controller;

class Admin implements \Box\InjectionAwareInterface
{
    protected $di;

    /**
     * @param mixed $di
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * @return mixed
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * This method registers menu items in admin area navigation block
     * This navigation is cached in bb-data/cache/{hash}. To see changes please
     * remove the file.
     *
     * @return array
     */
    // public function fetchNavigation()
    // {
    //     return [
    //         'group' => [
    //             'index' => 1500,                // menu sort order
    //             'location' => 'ClouDNS',          // menu group identifier for subitems
    //             'label' => 'ClouDNS extension',    // menu group title
    //             'class' => 'cloudns',           // used for css styling menu item
    //         ],
    //         'subpages' => [
    //             [
    //                 'location' => 'ClouDNS', // place this module in extensions group
    //                 'label' => 'Settings',
    //                 'index' => 1500,
    //                 'uri' => $this->di['url']->adminLink('cloudns'),
    //                 'class' => '',
    //             ],
    //         ],
    //     ];
    // }

    /**
     * Methods maps admin areas urls to corresponding methods
     * Always use your module prefix to avoid conflicts with other modules
     * in future.
     *
     * @example $app->get('/cloudns/test',      'get_test', null, get_class($this)); // calls get_test method on this class
     * @example $app->get('/cloudns/:id',        'get_index', array('id'=>'[0-9]+'), get_class($this));
     */
    public function register(\Box_App &$app)
    {
        $app->get('/cloudns', 'get_index', [], get_class($this));
        // $app->get('/cloudns/test', 'get_test', [], get_class($this));
        // $app->get('/cloudns/user/:id', 'get_user', ['id' => '[0-9]+'], get_class($this));
        // $app->get('/cloudns/api', 'get_api', [], get_class($this));
    }

    public function get_index(\Box_App $app)
    {
        // always call this method to validate if admin is logged in
        $this->di['is_admin_logged'];

        return $app->render('mod_cloudns_settings');
    }

}
