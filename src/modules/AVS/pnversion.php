<?php


$dom = ZLanguage::getModuleDomain('AVS');

$modversion['name']           = 'AVS';
$modversion['version']        = '1.0';
$modversion['displayname']    = "AVS";
$modversion['description']    = __('Age Verification System', $dom);

// The following in formation is used by the credits module
// to display the correct credits
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 0;
$modversion['author']         = 'Florian Tree';
$modversion['contact']        = 'http://www.tord.eu';

// The following information tells the PostNuke core that this
// module has an admin option.
$modversion['admin']          = 1;
$modversion['user']           = 1;



