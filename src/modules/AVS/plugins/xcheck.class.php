<?php

/*
$root = pnServerGetVar("DOCUMENT_ROOT");
$moddir = pnModGetBaseDir("AVS");
$plugdir = $root."/".$moddir."/plugins/";
Loader::requireOnce($plugdir."avsplugin.class.php");
*/
Loader::requireOnce('modules/AVS/plugins/avsplugin.class.php');

class xcheck implements avsplugin
{
	private $Name 	= "X-Check";
	private $Image 	= "xcheck.gif";
	private $URL	= "http://www.x-check.de";
	
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
    	$render->assign('plugin',	'xcheck');
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_admin_plugin_xcheck.htm');	
		
	}
	
	public function showUser()
	{
		$settings = pnModGetVar("AVS");
		$render = & pnRender::getInstance('AVS',false);
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_user_plugin_xcheck.htm');		
	}
	
	public function validate()
	{
		// Security and sanity checks
	    if (!SecurityUtil::confirmAuthKey('AVS','p1')) {
	    	LogUtil::registerError(__("Autentication FAILED!!"));
	    	return false;
	    } else {
			if (strstr(pnServerGetVar('HTTP_REFERER'),"module=AVS&avs=xcheck") === false) {
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