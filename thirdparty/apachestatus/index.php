<?php

/*
 * PIMPED APACHE-STATUS
 * 
 */

global $aEnv;
$aEnv["project"] = array(
    'title' => 'Pimped Apache Status',
    'version' => '1.25',
    'releasedate' => '2016-04-20',
    'license' => 'GPL 3.0',
);
$aEnv["active"] = array();
$aEnv["links"]["project"] = array(
    'projecthome' => array(
        'url' => 'http://sourceforge.net/projects/pimpapachestat/',
        'label' => 'Sourceforge (en)',
        'target' => '_blank',
    ),
    'projecthome2' => array(
        'url' => 'http://www.axel-hahn.de/apachestatus.php',
        'label' => 'axel-hahn.de (de)',
        'target' => '_blank',
    ),
    'doc' => array(
        'url' => 'http://www.axel-hahn.de/docs/apachestatus/usage.htm',
        'label' => 'Documentation :: usage (en)',
        'target' => '_blank',
    ),
);
$aEnv["links"]["update"] = array(
    'check' => array(
        'url' => 'http://www.axel-hahn.de/versions/pimpedapachestatus_' . $aEnv["project"]["version"] . '.txt',
        'label' => '',
        'target' => '',
    ),
    'download' => array(
        'url' => 'http://sourceforge.net/projects/pimpapachestat/files/latest/download',
        'label' => '',
        'target' => '',
    ),
    'updater' => array(
        'url' => '?&view=update.php',
        'label' => '',
        'target' => '',
    ),
);

// I wanna see all warnings 
if (strpos($aEnv["project"]["version"], "beta")) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
}
$sGetStarted = '<br>see documentation <a href="http://www.axel-hahn.de/docs/apachestatus/get_started.htm">get started<a>.';
require_once './classes/primitivelogger.class.php';
global $oLog;
$oLog = new PrimitiveLogger();

global $aServergroups, $aDefaultCfg, $aUserCfg;
global $sJsOnReady;
$sJsOnReady = '';

// --- load default and user config
require_once("inc_functions.php");
include("config/config_default.php");
if (!@include("config/config_user.php")) {
    $oLog->add("Missing User config. Creating a new one...", 'warning');
    copy("config/config_user_default.php", "config/config_user.php");
    if (@include("config/config_user.php")) {
        $oLog->add("OK: File config/config_user.php was created.", 'info');
    } else {
        die("ERROR: Creation of config/config_user.php failed. Check the permissions on config directory (it must be writable for apache user)." . $sGetStarted);
    }
}
$aUserCfg = array_merge($aDefaultCfg, $aUserCfg);

// ------------------------------------------------------------
// check required features
// ------------------------------------------------------------

if (!function_exists("curl_multi_init")) {
    die("ERROR: PHP-CURL is not installed. It is required to run." . $sGetStarted);
}

if (!class_exists("DomDocument")) {
    $oLog->add("PHP-XML is not installed. XML Export is not available.", 'warning');
}

// ------------------------------------------------------------
// check GET
// ------------------------------------------------------------
// --- languages
$aEnv["active"]["lang"] = array_key_exists("lang", $_GET) ? $_GET["lang"] : $aUserCfg['lang'];
if (!$aEnv["active"]["lang"])
    $aEnv["active"]["lang"] = 'en';
require_once("lang/" . $aEnv["active"]["lang"] . ".php");

$sData = file_get_contents("lang/" . $aEnv["active"]["lang"] . ".js");
if (!$sData) {
    $oLog->add("language file was not found: lang/" . $aEnv["active"]["lang"] . ".js.", 'error');
    $sData = '{}';
}

// @since v1.16
checkAuth();

$aUserCfg['datatableOptions'] = str_replace("__LANG__", $sData, $aUserCfg['datatableOptions']);

// --- view
$aEnv["active"]["view"] = array_key_exists("view", $_GET) ? $_GET["view"] : $aUserCfg['defaultView'];
$aEnv["active"]["view"] = $aEnv["active"]["view"] ? $aEnv["active"]["view"] : $aUserCfg['views'][0];

// --- skins
$aEnv["active"]["skin"] = array_key_exists("skin", $_GET) ? $_GET["skin"] : $aUserCfg['skin'];

// --- autoreload
$aEnv["active"]["reload"] = array_key_exists("reload", $_GET) ? $_GET["reload"] : false;

// -- servergroup
$aEnv["active"]["group"] = array_key_exists("group", $_GET) ? $_GET["group"] : false;
if (!$aEnv["active"]["group"]) {

    foreach ($aServergroups as $sGroup => $aData) {
        $aEnv["active"]["group"] = $sGroup;
        break;
    }
}
$aEnv["active"]["servers"] = array_key_exists("servers", $_GET) ? $_GET["servers"] : false;

if ($aServergroups && !array_key_exists($aEnv["active"]["group"], $aServergroups)) {
    $oLog->add('The group ' . $aEnv["active"]["group"] . ' does not exist.', 'error');
}
if (!$aEnv["active"]["group"]) {
    $oLog->add('No group of servers was found. Please create one in the user config', 'error');
} else {

    foreach ($aServergroups[$aEnv["active"]["group"]]["servers"] as $sHost => $aData2) {
        $aServers2Collect[] = $sHost;
    }

    $aServers2Collect = array_key_exists("servers", $_GET) ? explode(",", $_GET["servers"]) : $aServers2Collect;

    // check: all servers are in my group?
    if ($aServers2Collect) {
        foreach ($aServers2Collect as $sHost) {
            if (!array_key_exists($sHost, $aServergroups[$aEnv["active"]["group"]]['servers'])) {
                $oLog->add('Server ' . $sHost . ' does not exist in group ' . $aEnv["active"]["group"] . '.');
            }
        }
    }
}
// given status url overrides server selection
if (array_key_exists("url", $_GET)) {
    // $parts=  parse_url($_GET["url"]);
    $aTestUrl = $_GET["url"];
}


// ----------------------------------------------------------------------
// 
// GENERATE ARRAYS FOR MENUS
// 
// ----------------------------------------------------------------------
// ------------------------------------------------------------
// servergroups and servers
// ------------------------------------------------------------

foreach ($aServergroups as $sGroup => $aServers) {
    foreach ($aServers['servers'] as $sServer => $aData) {
        if (!array_key_exists("disabled", $aData)) {
            if ($sGroup == $aEnv["active"]["group"]) {
                if (count($aServers2Collect) == 1 && $aServers2Collect[0] == $sServer) {
                    $aEnv["links"]["servers"][$sGroup]["subitems"][$sServer]["active"] = true;
                }
            }
            $aEnv["links"]["servers"][$sGroup]["subitems"][$sServer]["url"] = getNewQs(array("servers" => $sServer, "group" => $sGroup, "url" => ""));

            $sLabel = array_key_exists("label", $aData) ? $aData['label'] : $sServer;
            $aEnv["links"]["servers"][$sGroup]["subitems"][$sServer]["label"] = $sLabel;
        }
    }

    // if (count($aServers['servers'])==1) unset ($aEnv["links"]["servers"][$sGroup]["subitems"][$sServer]["active"]);
    if ($sGroup == $aEnv["active"]["group"] 
            // && count($aServers2Collect) <> 1
            ) {
        $aEnv["links"]["servers"][$sGroup]["active"] = true;
    }
    $aEnv["links"]["servers"][$sGroup]["label"] = $aLangTxt['menuGroup'] . ' ' . $sGroup;
    $aEnv["links"]["servers"][$sGroup]["class"] = "group";
    $aEnv["links"]["servers"][$sGroup]["url"] = getNewQs(array("servers" => "", "group" => $sGroup, "url" => ""));
}


// ------------------------------------------------------------
// available views
// ------------------------------------------------------------
foreach ($aUserCfg['views'] as $s) {
    $sLabel = '';
    if (array_key_exists($s, $aUserCfg['icons'])) {
        $sLabel.=$aUserCfg['icons'][$s] . ' ';
    }
    $sLabel .= $aLangTxt['view_' . $s . '_label'] ? $aLangTxt['view_' . $s . '_label'] : $s;
    $aEnv["links"]["views"][$s] = array(
        "label" => $sLabel,
        "url" => getNewQs(array("view" => $s)),
        "active" => ($s == $aEnv["active"]["view"]),
    );
}

// ------------------------------------------------------------
// available languages
// ------------------------------------------------------------
if ($aUserCfg['selectLang']) {
    foreach (explode(",", $aUserCfg['selectLang']) as $s) {
        $aEnv["links"]["lang"][$s] = array(
            "label" => $s,
            "url" => getNewQs(array("lang" => $s)),
            "active" => ($s == $aEnv["active"]["lang"]),
        );
    }
}

// ------------------------------------------------------------
// available skins
// ------------------------------------------------------------
if ($aUserCfg['selectSkin']) {
    foreach (explode(",", $aUserCfg['selectSkin']) as $s) {
        $aEnv["links"]["skins"][$s] = array(
            "label" => $s,
            "url" => getNewQs(array("skin" => $s)),
            "active" => ($s == $aEnv["active"]["skin"]),
        );
    }
}

// ------------------------------------------------------------
// autoreload page
// ------------------------------------------------------------
if ($aUserCfg['autoreload']) {
    foreach ($aUserCfg['autoreload'] as $iTime) {
        $s = $iTime . " s";
        if ($s == " s")
            $s = "---";
        $aEnv["links"]["reload"][$s] = array(
            "label" => $s,
            "url" => getNewQs(array("reload" => $iTime)),
            "active" => ($iTime == $aEnv["active"]["reload"]),
        );
    }
}

// ----------------------------------------------------------------------
// 
// collect server status of all servers to array $a
// 
// ----------------------------------------------------------------------
// if there is no server given in url then lets take the first entry in config
if (!isset($aServers2Collect)) {
    $aServers2Collect = array();
    $aEnv["links"]["servers"] = array();
    foreach ($aServergroups as $sGroup => $aData) {
        foreach ($aData["servers"] as $sHost => $aData2) {
            $aServers2Collect[] = $sHost;
        }
        break;
    }
}

require_once './classes/serverstatus.class.php';
$oServerStatus = new ServerStatus();

$i = 0;
if (isset($aTestUrl)) {
    $oServerStatus->addServer('testurl', array('status-url' => $aTestUrl));
} else if ($aServers2Collect) {
    foreach ($aServers2Collect as $sWebserver) {
        $oServerStatus->addServer($sWebserver, $aServergroups[$aEnv["active"]["group"]]['servers'][$sWebserver]);
        $i++;
    }
}

$aStatus = $oServerStatus->getStatus();
if (count($aStatus["errors"])) {
    foreach ($aStatus["errors"] as $sErr) {
        $oLog->add($sErr, 'error');
    }
}
global $aSrvStatus;
$aSrvStatus = $aStatus["data"];
global $aSrvMeta;
$aSrvMeta = $aStatus["meta"];

if (!count($aServers2Collect)) {
    $oLog->add('No server was defined for monitoring. Check your config in config/config_user.php.', 'error');
}


// ----------------------------------------------------------------------
// 
// GENERATE OUTPUT
// 
// ----------------------------------------------------------------------
// ----------------------------------------------------------------------
// create content
// ----------------------------------------------------------------------
require_once './classes/datarenderer.class.php';
$oDatarenderer = new Datarenderer();

// ----------------------------------------------------------------------
// page
// ----------------------------------------------------------------------
include("./classes/page.class.php");
$oPage = new Page();
$oPage->setOutputtype('html');

if (!include('./templates/' . $aEnv["active"]["skin"] . '/' . $aUserCfg['defaultTemplate'])) {
    die('ERROR: Template could not be included: ' . './templates/' . $aUserCfg['skin'] . '/' . $aUserCfg['defaultTemplate'] . '.<br>Check the values "skin" and "defaultTemplate" in your configuration.');
}

switch ($oPage->getOutputtype()) {
    case 'html':
        // v1.13: version check
        $sUpdateInfos = checkUpdate();
        $oPage->setContent(str_replace('<span id="checkversion"></span>', $sUpdateInfos, $oPage->getContent()));

        $oPage->setJsOnReady($sJsOnReady);
        if (!$aUserCfg["showHint"]) {
            $sHeader = $oPage->getHeader($sHead);
            $oPage->setHeader($sHeader . '<style>.hintbox{display: none;}</style>');
        }
        // @since v1.22 map langtxt for javascript
        $sHeader = $oPage->getHeader($sHead);
        $aLangJs=array();
        foreach($aLangTxt as $sKey => $sVal){
            if (strpos($sKey, 'js::')===0){
                $aLangJs[str_replace('js::','',$sKey)]=$sVal;
            }
        }
        $oPage->setHeader($sHeader . '<script>var aLang='.json_encode($aLangJs).';</script>');

        $oPage->setFooter('
            <div id="divfooter">
                Axel pimped the Apache status 4U - v' . $aEnv["project"]["version"] . ' (' . $aEnv["project"]["releasedate"] . ')
                <ul>' . $oDatarenderer->renderLI($aEnv["links"]["project"]) . '</ul>
                    <script>initPage();</script>
            </div>');
        break;
    default:
}
echo $oPage->render();
