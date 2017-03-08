<?php

class ServerConsolePasswordBL {

    private $salt;
    private $skeletonKey;
    private $cycle_limit;

    public function __construct($input = array()) {
        // Set private variables
        $this->salt        = isset($input['salt']) ? trim($input['salt']) : '12312321334fff4f3442e12e12wwed344fw3';
        $this->skeletonKey = '1231237h7r6b3ehbdccyui3qbkhqbyfbyuhyy773';
        $this->cycle_limit = 12;
    }

    public function checkSessionStatus() {
        $sessionActive     = false;
        $forceRegistration = true;
        $forceLogin        = true;


        if (defined('FORCE_BYPASS_LOGIN_SECURITY') && FORCE_BYPASS_LOGIN_SECURITY) { 
            return array(
                'emailaddress' => 'johndoe@company.com',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'fullname' => 'John Doe',
                'sessionActive' => true, 
                'success' => true, 
                'errors' => array()
            ); 
        }

        $returnArray = array(
            'success' => true
        );

        // Check if ini is set up correctly 
        if (
            (defined('FIRSTNAME') && !empty(FIRSTNAME)) &&
            (defined('LASTNAME') && !empty(LASTNAME)) &&
            (defined('EMAILADDRESS') && !empty(EMAILADDRESS))
        ) { 
            $forceRegistration = false;

            $emailaddress = trim(EMAILADDRESS);
            $firstname    = trim(FIRSTNAME);
            $lastname     = trim(LASTNAME);
            $fullname     = substr(strtoupper($firstname), 0, 1).'. ' .ucwords(strtolower($lastname));

            $returnArray['emailaddress'] = $emailaddress;
            $returnArray['firstname']    = $firstname;
            $returnArray['lastname']     = $lastname;
            $returnArray['fullname']     = $fullname;
        }

        // Check if there is an active session currently and that it matches the config
        if ($forceRegistration == false) {
            if (
                isset($_SESSION['emailaddress']) && 
                !empty($_SESSION['emailaddress']) && 
                (trim($_SESSION['emailaddress']) === trim(EMAILADDRESS))
            ) { 
                $forceLogin    = false;
                $sessionActive = true;
            }
        }

        if ($forceRegistration) { $returnArray['forceRegistration'] = true; } else 
        if ($forceLogin)        { $returnArray['forceLogin']        = true; } else 
        if ($sessionActive)     { $returnArray['sessionActive']     = true; }

        return $returnArray;
    }

    public function checkAuthenticationFilePermission() {
        $value = 'Cannot read file';

        $auth_config = CONFIG_DIRECTORY_PATH;

        if (file_exists($auth_config)) {
            $value = 'Read Only';
            if (is_writable($auth_config)) {
                $value = 'Read and write';
            }
        }

        return array(
            'success' => true,
            'value' => $value
        );
    }

    public function authenticationSetup($input) {
        $serverConsolePasswordBL = $this;

        if (defined('FORCE_BYPASS_LOGIN_SECURITY') && FORCE_BYPASS_LOGIN_SECURITY) { return array('success' => true, 'errors' => array()); }

        $errors = array();

        $firstname    = !empty($input['firstname'])    ? trim($input['firstname'])    : '';
        $lastname     = !empty($input['lastname'])     ? trim($input['lastname'])     : '';
        $emailaddress = !empty($input['emailaddress']) ? trim($input['emailaddress']) : '';
        $password     = !empty($input['password'])     ? trim($input['password'])     : '';

        if (empty($firstname))    { $errors[] = 'First name is required';    }
        if (empty($lastname))     { $errors[] = 'Last name is required';     }
        if (empty($emailaddress)) { $errors[] = 'Email address is required'; }
        if (empty($password))     { $errors[] = 'Password is required';      }

        if (empty($errors) && !filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        if (empty($errors)) {
            if (
                ($result = $serverConsolePasswordBL->getStrength(array('password' => $password))) &&
                ($result['isStrong'])
            ) { 
                $password = $serverConsolePasswordBL->getSaltedPassword(array('password' => $password));
            } else {
                $errors[] = $result['error'];
            }
        }

        if (empty($errors)) {
            $auth_config = AUTH_CONFIG_PATH;
            if (file_exists($auth_config)) {
                if (is_writable($auth_config)) {
                    $myfile = fopen($auth_config, "w");
                    fwrite($myfile, "[Authentication]
FIRSTNAME = ".trim($firstname)."
LASTNAME = ".trim($lastname)."
EMAILADDRESS = ".trim($emailaddress)."
PWKEY = ".trim($password)."
");
                    fclose($myfile);
                } else {
                    $errors[] = 'Could not write file';
                }
            } else {
                $errors[] = 'Could not read file';
            }
        }

        if (empty($errors)) {

            // Activate Session
            $_SESSION['emailaddress'] = $emailaddress;

        }

        return array(
            'success' => empty($errors),
            'errors' => $errors
        );
    }

    public function authenticationLogout($input) {
        if (empty($_SESSION)) { session_start(); }
        session_unset();
        session_destroy();
        $_SESSION = array();

        return array(
            'success' => true
        );
    }

    public function authenticationLogin($input) {
        $serverConsolePasswordBL = $this;

        if (defined('FORCE_BYPASS_LOGIN_SECURITY') && FORCE_BYPASS_LOGIN_SECURITY) { return array('success' => true, 'errors' => array()); }

        $password = isset($input['password']) ? trim($input['password']) : '';

        $errors = array();

        if (file_exists(AUTH_CONFIG_PATH)) {
            $auth_ini_array = parse_ini_file(AUTH_CONFIG_PATH);
            $PWKEY = isset($auth_ini_array['PWKEY']) ? trim($auth_ini_array['PWKEY']) : '';
        }

        if (
            !empty($PWKEY) &&
            ($PWKEY === $serverConsolePasswordBL->getSaltedPassword(array('password' => $password)))
        ) {
            $_SESSION['emailaddress'] = EMAILADDRESS;
        } else {
            $errors[] = 'Password incorrect';
        }

        return array(
            'success' => empty($errors),
            'errors' => $errors
        );
    }

    // Returns a salty password.
    public function getSaltedPassword($input) {
        $password = isset($input['password']) ? trim($input['password']) : '';
        return sha1($this->salt.$password);
    }

    // Return the Skeleton Key.
    public function getSkeletonKey() {
        return $this->skeletonKey;
    }

    // Checks the strengh of a password.
    public function getStrength($input) {

        $password = isset($input['password']) ? trim($input['password']) : '';

        if( strlen($password) < 7 )              { $error[] = "- 7 characters";  }
        if( !preg_match("#[0-9]+#", $password) ) { $error[] = "- One number"; }
        if( !preg_match("#[a-z]+#", $password) ) { $error[] = "- One lowercase letter"; }
        if( !preg_match("#[A-Z]+#", $password) ) { $error[] = "- One UPPERCASE letter";   }
        if( !preg_match("#\W+#", $password) )    { $error[] = "- One symbol"; }

        if (!empty($error)) {
            $error = array_merge(array('Invalid Password', 'Requires:'), $error);
        }

        if (isset($error)) {
            return array('isStrong' => false, 'error' => implode('<br />', $error));
        } else {
            return array('isStrong' => true);
        }
    }


    // Generates a strong password of N length containing at least one lower case letter,
    // one uppercase letter, one digit, and one special character. The remaining characters
    // in the password are chosen at random from those four sets.
    //
    // The available characters in each set are user friendly - there are no ambiguous
    // characters such as i, l, 1, o, 0, etc. This, coupled with the $add_dashes option,
    // makes it much easier for users to manually type or speak their passwords.
    //
    // Note: the $add_dashes option will increase the length of the password by
    // floor(sqrt(N)) characters.
    public function generateStrongPassword($input) {

        $length         = isset($input['length'])         ? intval($input['length'])       : 7;
        $add_dashes     = isset($input['add_dashes'])     ? $input['add_dashes']           : true;
        $available_sets = isset($input['available_sets']) ? trim($input['available_sets']) : 'luds';

        $sets = array();
        if(strpos($available_sets, 'l') !== false) $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false) $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false) $sets[] = '23456789';
        if(strpos($available_sets, 's') !== false) $sets[] = '!@#$%&*?';

        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password.= $set[array_rand(str_split($set))];
            $all.= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password.= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        if (!$add_dashes) return $password;

        $dash_len = floor(sqrt($length));
        $dash_str = '';

        while (strlen($password) > $dash_len) {
            $dash_str.= substr($password, 0, $dash_len).'-';
            $password = substr($password, $dash_len);
        }

        $dash_str.= $password;

        return $dash_str;
    }

    public function changeUserPassword($input) {
        $passwordDL = DLFactory::getInstance('PasswordDL');

        $systemaccountid    = isset($input['systemaccountid'])    ? intval($input['systemaccountid'])  : 0;
        $userid             = isset($input['userid'])             ? intval($input['userid'])           : 0;
        $current_password   = isset($input['current_password'])   ? trim($input['current_password'])   : '';
        $new_password       = isset($input['new_password'])       ? trim($input['new_password'])       : '';
        $new_password_again = isset($input['new_password_again']) ? trim($input['new_password_again']) : '';

        $errors = array();

        // Password Validation
        if (($loggedin_pwkey = $passwordDL->getUserPassword(array('systemaccountid' => $systemaccountid, 'userid' => $userid))) && ($this->getSaltedPassword(array('password' => $current_password)) !== $loggedin_pwkey)) {
            $errors['current_password'] = 'Password is incorrect';
        }

        // Check not empty
        if ($current_password   === '')            { $errors['current_password']   = 'Please enter Current';             }
        if ($new_password       === '')            { $errors['new_password']       = 'Please enter New';                 }
        if ($new_password_again === '')            { $errors['new_password_again'] = 'Please enter Confirm';             }

        // Check Strength
        if (($strongPass = $this->getStrength(array('password' => $new_password))) && !$strongPass['isStrong'] && isset($strongPass['error'])) {
            $errors['new_password'] = $strongPass['error'];
        }

        if ($new_password_again !== $new_password) { $errors['new_password_again'] = 'Confirm does not match';}

        $pwkey = '';
        if (empty($errors)) {
            $pwkey = $this->getSaltedPassword(array('password' => $new_password));

            if ($this->isPasswordInHistoryCycle(array(
                'systemaccountid' => $systemaccountid,
                'userid'          => $userid,
                'password'        => $pwkey
            ))) {
                $errors['password'] = 'This password has already<br>been used in the last '.$this->cycle_limit.'<br> cycles';
            } else {
                $passwordDL->setUserPassword(array(
                    'systemaccountid' => $systemaccountid,
                    'userid'          => $userid,
                    'password'        => $pwkey
                ));
            }
        }

        if (!empty($errors)) {
            $errors['password_fields'] = reset($errors);
        }

        return array(
            'result' => empty($errors),
            'errors' => $errors,
            'pwkey'  => $pwkey
        );
    }

}
