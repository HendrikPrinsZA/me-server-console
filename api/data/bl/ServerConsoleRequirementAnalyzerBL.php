<?php

class CSCRequirementAnalyzerBL {

// XML Parsing
if(!function_exists('xml_parser_create')) {
    $xmlStatus = "<b><span class=stop>{$mod_strings['LBL_CHECKSYS_XML_NOT_AVAILABLE']}</span></b>";
    installLog("ERROR:: {$mod_strings['LBL_CHECKSYS_XML_NOT_AVAILABLE']}");
    $error_found = true;
    $error_txt .= '
        <p><strong>'.$mod_strings['LBL_CHECKSYS_XML'].'</strong></p>
        <p class="error">'.$xmlStatus.'</p>
    ';
}else{
    installLog("XML Parsing Support Found");
}


// mbstrings
if(!function_exists('mb_strlen')) {
    $mbstringStatus = "<b><span class=stop>{$mod_strings['ERR_CHECKSYS_MBSTRING']}</font></b>";
    installLog("ERROR:: {$mod_strings['ERR_CHECKSYS_MBSTRING']}");
    $error_found = true;
    $error_txt .= '
        <p><strong>'.$mod_strings['LBL_CHECKSYS_MBSTRING'].'</strong></p>
        <p class="error">'.$mbstringStatus.'</p>
    ';
}else{
    installLog("MBString Support Found");
}

// zip
if(!class_exists('ZipArchive')) {
    $zipStatus = "<b><span class=stop>{$mod_strings['ERR_CHECKSYS_ZIP']}</font></b>";
    installLog("ERROR:: {$mod_strings['ERR_CHECKSYS_ZIP']}");
}else{
    installLog("ZIP Support Found");
}

function make_writable($file)
{

    $ret_val = false;
    if(is_file($file) || is_dir($file))
    {
        if(is_writable($file))
        {
            $ret_val = true;
        }
        else
        {
            $original_fileperms = fileperms($file);

            // add user writable permission
            $new_fileperms = $original_fileperms | 0x0080;
            @sugar_chmod($file, $new_fileperms);
            clearstatcache();
            if(is_writable($file))
            {
                $ret_val = true;
            }
            else
            {
                // add group writable permission
                $new_fileperms = $original_fileperms | 0x0010;
                @chmod($file, $new_fileperms);
                clearstatcache();
                if(is_writable($file))
                {
                    $ret_val = true;
                }
                else
                {
                    // add world writable permission
                    $new_fileperms = $original_fileperms | 0x0002;
                    @chmod($file, $new_fileperms);
                    clearstatcache();
                    if(is_writable($file))
                    {
                        $ret_val = true;
                    }
                }
            }
        }
    }

    return $ret_val;
}

$phpIniLocation = get_cfg_var("cfg_file_path");
installLog("php.ini location found. {$phpIniLocation}");


}