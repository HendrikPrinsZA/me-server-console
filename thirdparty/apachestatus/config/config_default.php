<?php

/*
 * PIMPED APACHE-STATUS
 * DEFAULT CONFIG FILE
 * 
 * !!! DO NOT CHANGE THIS FILE !!!
 * 
 * SEE config_user_default.php. COPY config_user_default.php to
 * "config_user.php" AND MAKE YOUR CHANGES THERE.
 * 
 * check
 * http://www.axel-hahn.de/docs/apachestatus/custom.htm
 */

$aServergroups = array(
    'default' => array(
        'server' => array(
            'localhost' => array(),
        ),
    ),
);


$aDefaultCfg = array(
    'auth' => array(
        array('user'=>'admin', 'password'=>false)
    ),
    'autoreload' => array(false, 10, 30, 60, 300),
    
    // how often check for new version in [sec]; 
    // 60*60*24*7 = once per week
    // 0 = check disabled
    'checkupdate' => 60 * 60 * 24 * 1,
    
    'datatableOptions' => '{ 
        "bPaginate": false, 
        "bLengthChange": false, 
        "bFilter": true, 
        "bSort": true, 
        "bAutoWidth": false, 
        "bStateSave": true,
        "sPaginationType": "full_numbers",
        "dom": \'C<"clear">lfrtip\',
        "oLanguage": __LANG__ 
        }',
    'defaultTemplate' => 'out_html.php', // 
    'defaultView' => false, // one of the views; false is first in views array
    // check in request table: limits in [ms] for execution time (column "Req")
    'execTimeRequest' => array(
        'warning' => '1000',
        'critical' => '5000',
    ),
    'hideRows' => array(), // hide table rows of apache status    
    'lang' => 'en', // default language
    'selectLang' => 'en,de', // list of selectable languages (in folder "lang")
    'selectSkin' => 'default,summer,ice', // selectable skins (in folder "templates")
    // minify the views
    'showHint' => true, // shows hint in all views
    'skin' => 'default', // where to search style.css and defaultTemplate
    // where to draw bars
    'tdbars' => array('thCount', 'Req'),
    'tdlink' => array(),
    'views' => array('serverinfos.php', 'performance-check.php', 'allrequests.php', 'original.php', 'help.php'),
    
    'icons' => array(
        'title' => '<i class="fa fa-square"></i> ',
        'group' => '<i class="fa fa-folder-o"></i> ',
        'time' => '<i class="fa fa-clock-o"></i> ',
        'refresh' => '<i class="fa fa-refresh"></i> ',
        'skin' => '<i class="fa fa-tint"></i> ',
        'lang' => '<i class="fa fa-comment"></i> ',
        'export' => '<i class="fa fa-download"></i> ',
        // views
        'serverinfos.php' => '<i class="fa fa-tasks"></i>',
        'performance-check.php' => '<i class="fa fa-line-chart"></i>',
        'allrequests.php' => '<i class="fa fa-navicon"></i>',
        'original.php' => '<i class="fa fa-file-text-o"></i>',
        'help.php' => '<i class="fa fa-question-circle"></i>',
        // help page
        'help-doc' => '<i class="fa fa-book"></i>',
        'help-color' => '<i class="fa fa-tint"></i>',
        'help-thanks' => '<i class="fa fa-comment-o"></i>',
        'update.php' => '<i class="fa fa-rocket"></i>',
    ),
);
