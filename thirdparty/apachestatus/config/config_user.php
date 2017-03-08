<?php

/*
 * PIMPED APACHE SERVER-STATUS
 * 
 * TEMPLATE FOR USER CONFIG FILE
 * 
 * !!! COPY THIS FILE TO "config_user.php" to make your setup !!!
 * set values in config_default.php to override default settings
 * 
 * http://www.axel-hahn.de/docs/apachestatus/custom.htm
 */

$aServergroups = array(

    'my default environment' => array(
        'servers' => array(
            'localhost' => array(
                'label' => 'localhost',
            ),
        ),
    ),
    'my clustered web 1' => array(
        'servers' => array(
            'server1' => array(),
            'server2' => array(),
            'server3' => array(),
        ),
    ),
    'my clustered web 2' => array(
        'servers' => array(
            'serverA' => array(),
            'serverB' => array(),
            'serverC' => array(),
            'serverD' => array(),
        ),
    ),
  
    /*
    // advanced example for a loadbalanced web
    // default server-status is
    // http://[servername]/server-status
    // - use "status-url" to fetch apache server status from another url or port
    // - use "userpwd" for password a protected status url
    'my clustered web 1' => array(
        'servers' => array(
            'webserver-01' => array(
                'label' => 's01',
                'status-url' => 'http://s01:8888/server-status',
                'userpwd' => 'username:password',
            ),
            'webserver-02' => array(
                'label' => 's02',
                'status-url' => 'http://s02:8888/server-status',
                'userpwd' => 'username:password',
            ),
        ),
    ),
     */
 
);



$aUserCfg = array(
    'lang' => 'en', // one of de|en
    // 'tdbars' => array('Count'), // column names where to add a column with a bar

    /*
    'skin' => 'default', // where to search style.css and defaultTemplate
    'showHint' => false,
    'hideRows' => array("Srv", "Acc", "Req", "Conn", "Child", "Slot"),
     */
);
