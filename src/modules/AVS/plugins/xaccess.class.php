<?php

/*
$root = pnServerGetVar("DOCUMENT_ROOT");
$moddir = pnModGetBaseDir("AVS");
$plugdir = $root."/".$moddir."/plugins/";
Loader::requireOnce($plugdir."avsplugin.class.php");
*/
Loader::requireOnce('modules/AVS/plugins/avsplugin.class.php');

class xaccess implements avsplugin
{
	private $Name 	= "X-Access";
	private $Image 	= "xaccess.gif";
	private $URL	= "http://www.x-access.com";
	
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
    	$render->assign('plugin',	'xaccess');
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_admin_plugin_xaccess.htm');	
		
	}
	
	public function showUser()
	{
		$settings = pnModGetVar("AVS");
		$render = & pnRender::getInstance('AVS',false);
		$settings['sessionid']=session_id();
		pnSessionSetVar('xa_secure'.$settings['xaccess_siteid'],1);
    	$render->assign('settings',	$settings);
	    // fetch, process and display template
    	return $render->fetch('AVS_user_plugin_xaccess.htm');		
	}
	
	public function validate()
	{
		// Security and sanity checks
	    if (!SecurityUtil::confirmAuthKey('AVS')) {
	    	LogUtil::registerError(__("Autentication FAILED!!"));
	    	return false;
	    } else {
			if (strstr(pnServerGetVar('HTTP_REFERER'),"module=AVS&avs=xaccess") === false) {
				LogUtil::registerError(__("Autentication FAILED!!"));
				return false;
			} else {
				// Get additional User Info
				$settings = pnModGetVar("AVS");
				$handle=fopen('http://www.x-access.com/verifyaccess.php?id='.$settings['xaccess_siteid'].'&t='.$_REQUEST['xasecure'].'&custinfo=1','r');
				$result=fgets($handle,64);
				fclose($handle);
				$data = split("&",$result,3);
				pnSessionSetVar("AVS_ADDITIONAL_DATA",serialize($data)); 
				return true;
			}
	    }
	}
}

?>