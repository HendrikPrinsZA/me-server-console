<?php

global $aLangTxt;
$aLangTxt = array(
    
    'id'=>'english',
    
    'menuGroup'=>'Group: ',
    'menuLang'=>'Language: ',
    'menuSkin'=>'Skin: ',
    'menuReload'=>'Reload interval: ',

    // ------------------------------------------------------------
    // version check
    // ------------------------------------------------------------
    'versionUptodate'=>'OK (up to date)',
    'versionError'=>'??',
    'versionUpdateAvailable'=>'Version %s is available',
    'versionManualCheck'=>'check for a new version',
    
    'authAccessDenied'=>'<h1>Access denied.</h1>User and password are required.',
    
    // ------------------------------------------------------------
    // for menu of views:
    // label for menu and h2
    // ------------------------------------------------------------
 
        'view_allrequests.php_label'=>'All requests',
        'view_original.php_label'=>'Original server-status',
        'view_performance-check.php_label'=>'Performance checks',
        'view_serverinfos.php_label'=>'Server info',
        'view_help.php_label'=>'Help',
        'view_dump.php_label'=>'Dumps',
        'view_setup.php_label'=>'Setup',
        'view_update.php_label'=>'Update',
        'view_selectserver.php_label'=>'List of groups and servers',

    // ------------------------------------------------------------
    // for all tables in the views
    // ------------------------------------------------------------

        // ............................................................
        'lblTable_status_workers'=>'Worker status',
        'lblTableHint_status_workers'=>'
            The table shows the status of apache worker processes of marked server or all marked servers of a group.<br>
            <ul>
                <li>"total" is the total count of worker processes</li>
                <li>"busy" count of active worker processes (status M is not equal "_" and not eqal ".").</li>
                <li>"idle" is count of processes with status "_".</li>
            </ul>',

        // ............................................................
        'lblTable_status'=>'Server status',
        'lblTableHint_status'=>'The table shows status information of the webserver(s)',

        // ............................................................
        'lblTile_server_responsetime'=>'Response time',
        'lblTileHint_server_responsetime'=>'Response time to fetch status from all servers',
        'lblTile_server_count'=>'Servers',
        'lblTileHint_server_count'=>'Count of requested webservers',

        // ............................................................
        'lblTile_requests_all'=>'Requests',
        'lblTileHint_requests_all'=>'Total count of (active and inactive) requests on all servers',
        'lblTable_requests_all'=>'List of all requests',
        'lblTableHint_requests_all'=>'The table shows all active and inactive requests.',

        // ............................................................
        'lblTile_requests_running'=>'Active requ.',
        'lblTileHint_requests_running'=>'Active requests',
        'lblTable_requests_running'=>'Active requests',
        'lblTableHint_requests_running'=>'The table shows requests that are currently processed on the selected webserver(s).',

        // ............................................................
        'lblTile_requests_mostrequested'=>'Most requested',
        'lblTileHint_requests_mostrequested'=>'Most often requested query',
        'lblTable_requests_mostrequested'=>'Most often processed requests',
        'lblTableHint_requests_mostrequested'=>'
            The table shows the most often processed requests.<br>
            Remarks:<br>
            <ul>
                <li>The table is sorted by coloumn "Count".</li>
                <li>The table contains active and already finished requests.</li>
            </ul>',

        // ............................................................
        'lblTable_requests_hostlist'=>'Most often requested vhosts',
        'lblTableHint_requests_hostlist'=>'
            The table shows the most often requested virtual hosts.<br>
            Remarks:<br>
            <ul>
                <li>The table is sorted by coloumn "Count".</li>
                <li>The table contains active and already finished requests.</li>
            </ul>',

        // ............................................................
        'lblTable_requests_methods' => 'Request methods',
        'lblTableHint_requests_methods' => 'Name and count of HTTP-request methods',
        
        // ............................................................
        'lblTile_requests_clients' => 'Max. from IP',
        'lblTileHint_requests_clients' => 'Maximum count of requests coming from a single IP',
        'lblTable_requests_clients' => 'Requests per ip address',
        'lblTableHint_requests_clients' => 'List of Clients and their count of (current and finished) requests.<br>
			Remark: An ip can be a gateway ip that masks several devices of an enterprise.',

        // ............................................................
        'lblTile_requests_longest'=>'Slowest request',
        'lblTileHint_requests_longest'=>'Slowest request',
        'lblTable_requests_longest'=>'Top 25 of slowest requests',
        'lblTableHint_requests_longest'=>'The table shows all requests ordered by response time.<br>
            Remarks:<br>
            <ul>
                <li>The response time in the Apache status is available, if the request is finished.
                    It is not available for currently procesed requests (its value is always "0").
                </li>
                <li>The table is ordered by columns "Req" (value is in ms)</li>
            </ul>',
    
        // ............................................................
        'lblTable_explanation'=>'Explanation',
        'lblTableHint_expalanation'=>'Colours in the tables',

    // ------------------------------------------------------------
    // description for tables
    // ------------------------------------------------------------
    
    
        'thWorkerServer' => 'Webserver',
        'thWorkerTotal' => 'total',
        'thWorkerActive' => 'busy',
        'thWorkerWait' => 'idle',
        'thWorkerBar' => 'visual',
        'thCount'=>'Count',
    
        'bartitleFreeWorkers' => 'free workers',
        'bartitleBusyWorkers' => 'busy workers',
        'bartitleIdleWorkers' => 'idle workers',
  
        'lblLink2Top' => 'top',
        'lblHintFilter' => 'Filter table by',
        'lblReload' => 'Refresh now',
        'lblExportLinks' => 'Export (unfiltered) table',

    // ------------------------------------------------------------
    // help page
    // ------------------------------------------------------------
        'lblHelpDoc'=>'Documentation',
        'lblHintHelpDoc'=>'small hints and links to the documentation',
        'lblHelpDocContent'=>'
            <br>
            <strong>Tables - sort</strong><br>
            <br>
            You can sort the table by any coloumn by clicking the name in the 
            table head with the left mousebutton. Reverse order by clicking 
            again.<br>
            Multi-coloumn sorting is available too: hold the SHIFT key while 
            clicking in the table head.<br>
            <br>
            <strong>Tables - filter</strong><br>
            <br>
            Use the search field to filter the table.<br>
            on some colums you can filter by their entries.<br>
            A click to the [X] icon removes the filter.<br>
            
            <br>
            <strong>Links</strong><br>
            <br>
            More detailed information you get here:
            ',
    
        'lblHelpBookmarklet'=>'<strong>Bookmarklet</strong><br>
            <br>
            If a server-status page is available in the public internet you can
            use a bookmarklet to view it here. Without any configuration.<br>
            Drag and drop the following link to your bookmarks: ',
        
        'lblHelpColors'=>'Colors of the request rows',
        'lblHintHelpColors'=>'
            The rows with the requests are colored. The color of each row
            depends on the selected skin.<br>
            In general the color depends on the criteria below.<br>
            The color properties of each group will be added.<br>
            ',
    
        'lblHelpThanks'=>'Thanks!',
        'lblHintHelpThanks'=>'The following helpers and and tools were used.',
        'lblHelpThanksContent'=>'
            <p>
                I say &quot;thank you&quot; to the developers of different
                tools I use here in my product:
            </p>
            <ul>
                <li>jQuery: <a href="http://jquery.com/">http://jquery.com/</a></li>
                <li>Datatables - sortable tables: <a href="http://datatables.net/">http://datatables.net/</a></li>
                <li>array2xml.class - XML export: <a href="http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes">http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes</a></li>
                <li>Font-awesome - Icons: <a href="http://fortawesome.github.io/Font-Awesome/">http://fortawesome.github.io/Font-Awesome/</a></li>
                <li>Bootstrap - Html framework: <a href="http://getbootstrap.com/">http://getbootstrap.com/</a></li>
            </ul>
            ',
    
    
        // column "comment" by column "M"
        'lblStatus' => 'Status',
        'cmtLegendM' => 'Mode of operation (column "M")',
        'cmtStatus_' => '_ Request finished. Waiting for new Connection',
        'cmtStatusS' => 'S Starting up',
        'cmtStatusR' => 'R Reading Request',
        'cmtStatusW' => 'W Sending Reply',
        'cmtStatusK' => 'K Keepalive (read)',
        'cmtStatusD' => 'D DNS Lookup',
        'cmtStatusC' => 'C Closing connection',
        'cmtStatusL' => 'L Logging',
        'cmtStatusG' => 'G Gracefully finishing',
        'cmtStatusI' => 'I Idle cleanup of worker',
        'cmtStatus.' => '. Request finished. Open slot with no current process',
        // 'cmtRequest'=>'',
    
        'cmtLegendRequest' => 'HTTP Request method',
        'cmtRequestGET' =>'GET',
        'cmtRequestHEAD' =>'HEAD',
        'cmtRequestPOST' =>'POST',
        'cmtRequestPUT' =>'PUT',
        'cmtRequestDELETE' =>'DELETE',
        'cmtRequestTRACE' =>'TRACE',
        'cmtRequestCONNECT' =>'CONNECT',
        'cmtRequestNULL' =>'NULL',
        'cmtRequestOPTIONS' =>'OPTIONS',
        'cmtRequestPROPFIND' =>'PROPFIND',
    
    
        'cmtLegendexectime' => 'Execution time of a request',
        'cmtexectimewarning' =>'warning',
        'cmtexectimecritical' =>'critcal',
    
    
    // ------------------------------------------------------------
    // description for debug
    // ------------------------------------------------------------
    
        'lblDumpsaUserCfg'=>'$aUserCfg - user configuration',
        'lblHintDumpsaUserCfg'=>'The user configuration array is a merge of
            the default config and the user specific configuration.',
    
        'lblDumpsaEnv'=>'$aEnv - environent of this current request',
        'lblHintDumpsaEnv'=>'It contains information to render output.<br>
            Here are name and version of the project, active values
            (like current selected server group, laguage or skin).<br>
            Below the key "links" are arrays that can be rendered
            i.e. as a dropdown or a tablist',
    
        'lblDumpsaSrvStatus'=>'$aSrvStatus - array of server status',
        'lblHintDumpsaSrvStatus'=>'Each server has an array key "status" and "request".
            These are parsed data from server-status pages.',
    
        'lblDumpsaLang'=>'$aLang - array of language specific texts',
        'lblHintDumpsaLang'=>'This table compares the language text arrays of
            the activated languages.',
        'lblDumpsMiss'=>'!!! This key has no value !!!',
    
    // ------------------------------------------------------------
    // software update
    // ------------------------------------------------------------
        'lblUpdate'=>'Update of the web application',
        'lblUpdateNewerVerionAvailable'=>'OK, a newer version is available.',
        'lblUpdateNoNewerVerionAvailable'=>'Remark: There is no newer version available. The execution of the updater ist not necessary.',
        'lblUpdateHints'=>'
            The update will be done in 2 steps:
            <ol>
                <li>Download of the zip file of the current version<br>%s</li>
                <li>uncompress zip file</li>
            </ol>
            ',
        'lblUpdateDonwloadDone'=>'OK, the file was downloaded.<br>In the next step it will be extracted.',
        'lblUpdateDonwloadFailed'=>'Error: unable to download the zip file.',
        'lblUpdateContinue'=>'Continue &raquo;',
        'lblUpdateUnzipFile'=>'Extract file: %s<br>To: %s',
        'lblUpdateUnzipOK'=>'OK: the new version was extracted. Have fun!<br>If you like the software you can support it and make me happy if you go to the docs page (see the footer below) and share it or donate a few bugs.<br>I also would need some more translations...',
        'lblUpdateUnzipFailed'=>'Error: unable to open the zip file.',
    
    // ------------------------------------------------------------
    // javascript
    // ------------------------------------------------------------
        'js::statsCurrent'=>'Current',
        'js::statsAvg'=>'Avg.',
        'js::statsMax'=>'Maximum',
        'js::statsMin'=>'Minimum',
        'js::srvFilterPlaceholder'=>'find a server',
    
);
