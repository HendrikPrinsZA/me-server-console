<?php

/*
 * PIMPED APACHE-STATUS
 * FUNCTIONS
 * 
 */

// ----------------------------------------------------------------------
// FUNKTIONEN
// ----------------------------------------------------------------------

/**
 * get new querystring - create the new querystring by existing query string
 * of current request and given new parameters
 * @param array $aQueryParams
 * @return string
 */
function getNewQs($aQueryParams) {
    $s = false;
    if ($_GET) {
        $aQueryParams = array_merge($_GET, $aQueryParams);
    }

    foreach ($aQueryParams as $var => $value) {
        if ($value)
            $s.="&amp;" . $var . "=" . urlencode($value);
    }
    $s = "?" . $s;
    return $s;
}

/**
 * follow a given url by checking http header data and follow locations
 * @param string   $url          url to follow
 * @return string
 */
function httpFollowUrl($sUrl){
		$sReturn=$sUrl;
		$sData=httpGet($sUrl, 1);
		preg_match('/Location:\ (.*)/', $sData, $aTmp);
		if (count($aTmp)) {
			$sNextUrl = trim($aTmp[1]);
			if ($sNextUrl && $sNextUrl!==$sUrl) {
				$sReturn=httpFollowUrl($sNextUrl);
			}
		}
		return $sReturn;
}

/**
 * make an http get request and return the response body
 * @param string   $url          url to fetch
 * @param boolean  $bHeaderOnly  send header only
 * @return string
 */
function httpGet($url, $bHeaderOnly=false) {
    $ch = curl_init($url);
	if ($bHeaderOnly) {
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
	} else {
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);			
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'php-curl :: pimped apache status');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
    $res = curl_exec($ch);
    curl_close($ch);
    return ($res);
}

/**
 * check authentication if a user and password were configured
 * @global array  $aUserCfg  config from ./config/config_user.php
 * @return boolean
 */
function checkAuth() {
    global $aUserCfg;
    global $aLangTxt;
    if (
        !array_key_exists('user', $aUserCfg['auth'])
        || !array_key_exists('password', $aUserCfg['auth'])
        || (
            $aUserCfg['auth']['user']=='admin'
            && !$aUserCfg['auth']['password']
           )
    ) {
        return true;
    }
    
    if(
        array_key_exists('PHP_AUTH_USER', $_SERVER)
        && array_key_exists('PHP_AUTH_PW', $_SERVER)
        && $aUserCfg['auth']['user']==$_SERVER['PHP_AUTH_USER']
        && $aUserCfg['auth']['password']==md5($_SERVER['PHP_AUTH_PW'])
    ){
        return true;
    }
    
    header('WWW-Authenticate: Basic realm="Pimped Apache Status"');
    header('HTTP/1.0 401 Unauthorized');
    die ($aLangTxt["authAccessDenied"]);
}

    
/**
 * check for an update of the product
 * @param bool  $bForce  force check and ignore ttl
 * @return type
 */
function checkUpdate($bForce=false) {
    global $aLangTxt;
    global $aEnv;
    global $aUserCfg;
    $sUrl = str_replace(" ", "%20", $aEnv['links']['update']['check']['url']);
    $iTtl = (int) $aUserCfg["checkupdate"];
    $sTarget = sys_get_temp_dir() . "/checkupdate_" . md5($sUrl) . ".tmp";
    
    $sOut = '';

    // if the user does not want an update check then respect it
    if (!$iTtl && !$bForce){
        
        return '<a href="' . $aEnv['links']['update']['updater']['url'] . '"'
                . ' target="'.$aEnv['links']['update']['updater']['target'].'&skin='.$aEnv['active']['skin'].'&lang='.$aEnv['active']['lang'].'"'
                . ' class="button"'
                . '>'.$aLangTxt['versionManualCheck'].'</a>';
        // return false;
    }

    $bExec = true;
    if (file_exists($sTarget)) {
        $bExec = false;
        $aStat = stat($sTarget);
        $iAge = time() - $aStat[9];
        if ($iAge > $iTtl) {
            $bExec = true;
        }
        $sOut.="last exec: " . $iAge . " s ago - timer is $iTtl<br>\n";
    } else {
        $sOut.="last exec: never (touchfile was not found)<br>\n";
    }
    if ($bForce) {
        $bExec = true;
        $sOut.="override: force parameter was found<br>\n";
    }

    if ($bExec) {
        $sOut.="fetching $sUrl ...<br>";
        $sResult = httpGet($sUrl);
        if (!$sResult) {
            $sResult = ' <span class="version-updateerror">' . $aLangTxt['versionError'] . '</span>';
        } else {
            file_put_contents($sTarget, $sResult);
        }
    } else {
        $sOut.="reading cache $sTarget ...<br>";
        $sResult = file_get_contents($sTarget);
    }
    
    $sVersion = str_replace("UPDATE: v", "", str_replace(" is available", "", $sResult));
    if (strpos($sResult, "UPDATE") === 0) {
        $sUrl=$aEnv['links']['update']['updater']['url'] 
                . '&lang='.$aEnv['active']['lang']
                . '&skin='.$aEnv['active']['skin']
                ;
        $sResult = ' <span class="version-updateavailable" '
                . 'title="' . $sResult . '">'
                . '<a'
                    . ' href="'.$sUrl.'"'
                    . ' target="'.$aEnv['links']['update']['updater']['target'].'"'
                . '>'
                . sprintf($aLangTxt['versionUpdateAvailable'], $sVersion)
                . '</a>'
                . '</span>';
    }
    if (strpos($sResult, "OK") === 0) {
        $sResult = ' <span class="version-uptodate" title="' . $sResult . '">'
                . $aLangTxt['versionUptodate'] 
                . '</span>';
    }
    // for DEBUG
    // echo $sOut;

    return '<span id="checkversion">'.$sResult.'</span>';
}

/**
 * return a bool only: does exist a newer version or not?
 * (used in views/update.php)
 * @param string  
 * @return bool
 */
function hasNewVersion($sUpdateOut=''){
    $sResult=$sUpdateOut?$sUpdateOut:checkUpdate(true);
    // echo htmlentities($sResult);
    return (strpos($sResult, "UPDATE")>0?true:false);    
}
