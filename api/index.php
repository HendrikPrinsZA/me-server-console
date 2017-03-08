<?php
if (!isset($_SESSION)) { session_start(); }
require __DIR__.'/../config/core_config.php';

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

function class_autoloader($class) {
    $classFile = $class.'.php';
    $curDir = __dir__;
    $dirs = array($curDir.'/data', $curDir.'/data/bl', $curDir.'/data/lib', $curDir.'/data/dl');
    foreach ($dirs as $dir) {
        if (file_exists($dir.'/'.$classFile)) {
            include_once $dir.'/'.$classFile;
        }
    }
}

spl_autoload_register('class_autoloader');

//////////////////////////////////////////////
// NEW ROUTES

$app->get('/apache/mpm', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->checkApacheMPMEnabled());
});

$app->get('/mediator', function () use($app) {
    $serverConsoleMediatorBL = new ServerConsoleMediatorBL;

    echo json_encode($serverConsoleMediatorBL->triggerFunction($app->request->get()));
});

$app->get('/environment', function () use($app) {
    $serverConsoleEnvironmentBL = new ServerConsoleEnvironmentBL;

    echo json_encode($serverConsoleEnvironmentBL->getEnvironmentSummary());
});

$app->get('/authentication/permission', function () use($app) {
    $serverConsolePasswordBL = new ServerConsolePasswordBL;

    echo json_encode($serverConsolePasswordBL->checkAuthenticationFilePermission());
});

$app->post('/authentication/setup', function () use($app) {
    $serverConsolePasswordBL = new ServerConsolePasswordBL;

    echo json_encode($serverConsolePasswordBL->authenticationSetup($app->request->post()));
});

$app->post('/authentication/login', function () use($app) {
    $serverConsolePasswordBL = new ServerConsolePasswordBL;

    echo json_encode($serverConsolePasswordBL->authenticationLogin($app->request->post()));
});

$app->get('/authentication/logout', function () use($app) {
    $serverConsolePasswordBL = new ServerConsolePasswordBL;

    echo json_encode($serverConsolePasswordBL->authenticationLogout($app->request->post()));
});

$app->get('/authentication/checkstatus', function () use($app) {
    $serverConsolePasswordBL = new ServerConsolePasswordBL;

    echo json_encode($serverConsolePasswordBL->checkSessionStatus());
});

$app->get('/php', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->getPHPVersion());
});

$app->get('/ioncube', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->getIoncubeVersion());
});

$app->get('/configuration', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->getConfigurationValue($app->request->get()));
});

$app->get('/apache/mpm', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->checkApacheMPMEnabled());
});

$app->get('/php/opcode', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->checkPHPOpcacheEnabled());
});

$app->get('/phptimevsmysqltime', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->testTimePhpMysqlSync());
});

$app->get('/php/variables', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->checkPHPVariables());
});

$app->get('/mysql/variables', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->checkMySQLVariables());
});


$app->get('/conn', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->getCurrentConnectionDetails());
});

$app->get('/mysql', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->getMySQLVersion());
});

$app->get('/php', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->getPHPVersion());
});

$app->get('/ioncube', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->getIoncubeVersion());
});

$app->get('/action/phpini', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo $serverConsoleBL->returnPHPIni();
});

$app->get('/action/phpinfo', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo $serverConsoleBL->returnPHPInfo();
});

$app->get('/action/phperror', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo $serverConsoleBL->returnPHPErrorLog();
});

$app->get('/installed/versions', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->checkPreviousVersions());
});


$app->get('/installed/version/save/default', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->saveDefaultVersion($app->request->get()));
});

$app->get('/performance/pingtest', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode(array(
        'success' => true,
        'expected_time' => 0.4
    ));
});

$app->get('/performance/phpmysqltest', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->performanceTestPHPMySQL($app->request->get()));
});


$app->get('/performance/php', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->performanceTestPHP($app->request->get()));
});

$app->get('/tools/database', function () use($app) {
    $serverConsoleBL = new ServerConsoleBL;

    echo json_encode($serverConsoleBL->toolGetDatabasesAndDetails());
});


$app->run();
