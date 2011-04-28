<?php

/*
$root = pnServerGetVar("DOCUMENT_ROOT");
$moddir = pnModGetBaseDir("AVS");
$plugdir = $root."/".$moddir."/plugins/";
Loader::requireOnce($plugdir."avsplugin.class.php");
*/
Loader::requireOnce('modules/AVS/plugins/avsplugin.class.php');

class freiab18 implements avsplugin
{
	private $Name 	= "FreiAb18";
	private $Image 	= "freiab18.gif";
	private $URL	= "http://www.freiab18.de";
	
	public function name()
	{
		return $this->Name;	
	}

	public function image()
	{
		return $this->Image;	
	}	

	public function url()
	{
		return $this->URL;	
	}	
	
	public function showAdmin()
	{
		$settings = pnModGetVar("AVS");
		$render = & pnRender::getInstance('AVS',false);
    	$render->assign('name',		$this->Name);
    	$render->assign('plugin',	'freiab18');
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_admin_plugin_freiab18.htm');	
		
	}
	
	public function showUser()
	{
		$settings = pnModGetVar("AVS");
		$render = & pnRender::getInstance('AVS',false);
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_user_plugin_freiab18.htm');		
	}
	
	public function validate()
	{
		// Security and sanity checks
	    if (!SecurityUtil::confirmAuthKey('AVS')) {
	    	LogUtil::registerError(__("Autentication FAILED!!"));
	    	return false;
	    } else {
			if (strstr(pnServerGetVar('HTTP_REFERER'),"module=AVS&avs=freiab18") === false) {
				LogUtil::registerError(__("Autentication FAILED!!"));
				return false;
			} else {
				pnSessionSetVar("AVS_ADDITIONAL_DATA",serialize("no data"));
				return true;
			}
	    }
	}
}

?>