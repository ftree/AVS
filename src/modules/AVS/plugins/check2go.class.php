<?php
/*
$root = pnServerGetVar("DOCUMENT_ROOT");
$moddir = pnModGetBaseDir("AVS");
$plugdir = $root."/".$moddir."/plugins/";
Loader::requireOnce($plugdir."avsplugin.class.php");
*/
Loader::requireOnce('modules/AVS/plugins/avsplugin.class.php');

class check2go implements avsplugin
{
	private $Name 	= "Check2Go";
	private $Image 	= "check2go.gif";
	private $URL	= "http://www.check2go.de";
	
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
    	$render->assign('plugin',	'check2go');
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_admin_plugin_check2go.htm');	
		
	}
	
	public function showUser()
	{
		$settings = pnModGetVar("AVS");
		$render = & pnRender::getInstance('AVS',false);
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_user_plugin_check2go.htm');		
	}
	
	public function validate()
	{
		// Security and sanity checks
	    if (!SecurityUtil::confirmAuthKey('AVS')) {
	    	LogUtil::registerError(__("Autentication FAILED!!"));
	    	return false;
	    } else {
			if (strstr(pnServerGetVar('HTTP_REFERER'),"module=AVS&avs=check2go") === false) {
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