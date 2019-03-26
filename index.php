<?php
ob_start();

require(__DIR__.'/init.php');

if (\F3::exists('secure') && \F3::get('secure') == '1' && empty($_SERVER['HTTPS'])) {
    header('Location: ' . \Wyolution\View::instance()->getBaseUrl());
    exit(0);
}

// Default route
$f3->route('GET /','controllers\Dashboard->main'); //html

$f3->route('GET /chart','controllers\Main->courtChart'); //html
$f3->route('GET /chart2', 'controllers\Main->chart2');	// html
$f3->route('GET /chart3', 'controllers\Main->chart3');	// html

// JSON Calls - make all post before go live
$f3->route('GET|POST /stateFilter', 'controllers\StateFilter->getStates'); //json
$f3->route('GET|POST /stateCategory', 'controllers\StateFilter->getStatesCategory'); //json
$f3->route('GET|POST /stateDetails', 'controllers\StateDetails->getStateDetails');	// json
$f3->route('GET|POST /courtDetails', 'controllers\CourtDetails->getCourtDetails');	// json
$f3->route('GET|POST /courtCaseType', 'controllers\StateFilter->getCourtsByCaseType');	// json

$f3->route('GET /docs/release','controllers\Docs->releaseNotes');
$f3->route('GET /docs/help','controllers\Docs->help');

// Utility routes
$f3->route('GET /utils/session','controllers\Utils->session'); //html
$f3->route('GET /utils/hive','controllers\Utils->hive'); //html

// Show user-friendly error screen if something unexpected goes wrong
$f3->set('ONERROR',
    function($f3) {
        $utils = new controllers\Utils();
        $utils->error();
    }
);

$showDevOptions = $f3->get('SHOW_DEV_OPTIONS');
$f3->set('_showDevOptions', $showDevOptions);
$f3->run();

ob_end_flush();