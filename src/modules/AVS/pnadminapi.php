<?php
/**
 * get available admin panel links
 *
 * @return array array of admin links
 */
function AVS_adminapi_getlinks()
{
	$dom = ZLanguage::getModuleDomain('AVS');
	
    $links = array();
    $links[] = array('url' => pnModURL('AVS', 'admin', 'showSettings'), 'text' => __('Allgemeine Einstellungen', $dom));
    $links[] = array('url' => pnModURL('AVS', 'admin', 'showEnteredUsers'), 'text' => __('User Übersicht', $dom));    
    $links[] = array('url' => pnModURL('AVS', 'admin', 'showWaitingUsers'), 'text' => __('User verifizieren', $dom));
    
    $plugins = pnModAPIFunc('AVS','user','getPlugins');
	foreach ($plugins as $plugin) {
		$class = new $plugin;	        	
		$links[] = array('url' => pnModURL('AVS', 'admin', 'showSettings',array('plugin'=>$plugin)), 'text' => $class->name());
	}
	
	return $links;
}

?>