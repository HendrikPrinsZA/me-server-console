<?php

/*
 * PIMPED APACHE-STATUS
 * DEFAULT template
 * 
 */


// ======================================================================
// html header
// ======================================================================
// default CSS and JS
$sDirBS='./javascript/bootstrap3';
$sHead = '<link rel="stylesheet" type="text/css" href="./templates/' . basename(dirname(__FILE__)) . '/style.css" media="screen">'
        . '<link href="'.$sDirBS.'/css/bootstrap.min.css" rel="stylesheet">'
        . '<link href="'.$sDirBS.'/css/bootstrap-theme.min.css" rel="stylesheet">'
	.'<script src="'.$sDirBS.'/js/bootstrap.min.js" type="text/javascript"></script>';

// add a meta refresh tag if needed
if ($aEnv["active"]["reload"]) {
    $sHead.='<meta http-equiv="refresh" content="' . $aEnv["active"]["reload"] . '">';
}

// ======================================================================
// 
// generate content
// 
// ======================================================================
// ----------------------------------------------------------------------
// first I draw menu bar on top. It contains
// - Project and version in a H1 tag
// - top right:
//     - driopdown menus to select reload timer, skin and language
// - a dropdown to select a server or servergroup
// - a reload button
// ----------------------------------------------------------------------


ob_start();
if (!include('./views/' . $aEnv["active"]["view"])) {
    $oLog->add('View could not be included: ' . $aEnv["active"]["view"], 'error');
}
$content = ob_get_contents();
ob_end_clean();

$sBody = '
    <nav class="navbar navbar-inverse navbar-fixed-top">
      
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          
            <div id="brand">
                <h1 class="title" id="top"><a href="?">'
                .$aUserCfg['icons']['title'].$aEnv["project"]["title"] .
                ' <span>v' . $aEnv["project"]["version"] . '</span></a></h1>
                <span id="checkversion"></span>
            </div>    
        </div>
        
          <ul class="nav navbar-nav navbar-right" style="margin-right: 0.5em;">
            <li>
                <a href="#"
                    onclick="location.reload();" 
                >'.$aUserCfg['icons']['refresh'].'<span>' . date("H:i:s")
                . '</span> '.$aLangTxt['lblReload'].'
                </a>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                    .$aLangTxt['menuReload']
                    .' <span>'.($aEnv['active']['reload']?$aEnv['active']['reload'].'s':'-').'</span> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                 ' . $oDatarenderer->renderLI($aEnv["links"]["reload"]) .'
                </ul>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                    .$aUserCfg['icons']['skin'].$aLangTxt['menuSkin']
                    .' <span>'.$aEnv['active']['skin'].'</span> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                 ' . $oDatarenderer->renderLI($aEnv["links"]["skins"]) .'
                </ul>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                    .$aUserCfg['icons']['lang'].$aLangTxt['menuLang']
                    .' <span>'.$aEnv['active']['lang'].'</span> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                 ' . $oDatarenderer->renderLI($aEnv["links"]["lang"]) .'
                </ul>
            </li>
          </ul>
          
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'
                    .$aUserCfg['icons']['group'].$aLangTxt['menuGroup']
                    .' <span>'.$aEnv['active']['group']
                    .($aEnv['active']['servers']? ' -&rsaquo; '. $aEnv['active']['servers'] : '')
                    .'</span> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu" id="serverlist">'
                    .$oDatarenderer->renderLI($aEnv["links"]["servers"]) .'
                </ul>
            </li>
          
              
          </ul>
        </div><!--/.nav-collapse -->
      
    </nav>
    <span id="h3menu"></span> 
    ';

// ----------------------------------------------------------------------
// add a DIV with the tiles
// ----------------------------------------------------------------------

$sBody.='
        <div id="divtiles">
        ';
foreach ($oDatarenderer->getValidTiles() as $sTilename) {
    $sBody.=$oDatarenderer->renderTile($sTilename);
}
$sBody.='</div>';

// ----------------------------------------------------------------------
// add Startup-Logs if any exists
// ----------------------------------------------------------------------
$sBody.=$oLog->render();


// ----------------------------------------------------------------------
// add a DIV with the content
// 
// - a menu for the views with tabs 
// - a DIV wih main content
// - a DIV with a link to jump to top of page
// ----------------------------------------------------------------------

$sBody.='
        <div id="divmainbody">
            ' . $oDatarenderer->renderTabs($aEnv["links"]["views"]) .
        '<div id="divmaincontent">
                <!--
                    <h2>' . $aLangTxt["view_" . $aEnv["active"]["view"] . "_label"] . '</h2>
                -->

                <div class="subh2">' .
        $content .
        '</div>
            </div>
        </div>
        <div id="divgotop">
            <a href="#"> ^ <span>' . $aLangTxt['lblLink2Top'] . '</span></a>
        </div>
        ';

// ----------------------------------------------------------------------
// add rendering logs
// ----------------------------------------------------------------------
$sBody.=$oLog->render();


// ======================================================================
// put header and body to the page object
// ======================================================================

$oPage->setHeader($sHead);
$oPage->setContent($sBody);
