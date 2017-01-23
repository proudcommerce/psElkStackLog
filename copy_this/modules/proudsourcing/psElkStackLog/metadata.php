<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @copyright (c) Proud Sourcing GmbH | 2017
 * @link www.proudcommerce.com
 * @package psElkStackLog
 * @version 1.2.0
 **/

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'           => 'psElkStackLog',
    'title'        => 'psElkStackLog',
    'description'  => array(
        'de' => 'Shopinformationen an Logserver (z. B. elasticsearch) senden.',
        'en' => 'Send shop information to log server, eg. elastic search.',
    ),
    'thumbnail'    => 'logo.jpg',
    'version'      => '1.2.0',
    'author'       => 'Proud Sourcing GmbH',
    'url'          => 'http://www.proudcommerce.com',
    'email'        => 'support@proudcommerce.com',
    'extend'       => array(
        'search'    =>              'proudsourcing/psElkStackLog/application/controllers/pselkstacklog_search',
        'thankyou'    =>            'proudsourcing/psElkStackLog/application/controllers/pselkstacklog_thankyou'
    ),
    'files' => array(
        'pselkstacklog_core'    =>  'proudsourcing/psElkStackLog/core/pselkstacklog_core.php',
        'pselkstacklog_module'  =>  'proudsourcing/psElkStackLog/core/pselkstacklog_module.php',
        'pselkstacklog_cron'    =>  'proudsourcing/psElkStackLog/application/controllers/pselkstacklog_cron.php'
    ),
    'templates' => array(
    ),
    'blocks' => array(
    ),
    'settings' => array(
        array('group' => 'main', 'name' => 'psElkStackLog_logurl', 'type' => 'str', 'value' => '', 'position' => 10),
        array('group' => 'main', 'name' => 'psElkStackLog_hash', 'type' => 'str', 'value' => md5(time()), 'position' => 15),
        array('group' => 'logging', 'name' => 'psElkStackLog_log_search', 'type' => 'bool', 'value' => true, 'position' => 20),
        array('group' => 'logging', 'name' => 'psElkStackLog_log_order', 'type' => 'bool', 'value' => true, 'position' => 30),
    ),
    'events'      => array(
        'onActivate'   => 'pselkstacklog_module::onActivate',
        'onDeactivate' => 'pselkstacklog_module::onDeactivate',
    ),
);