<?php
/*
$root = pnServerGetVar("DOCUMENT_ROOT");
$moddir = pnModGetBaseDir("AVS");
$plugdir = $root."/".$moddir."/plugins/";
Loader::requireOnce($plugdir."avsplugin.class.php");
*/
Loader::requireOnce('modules/AVS/plugins/avsplugin.class.php');

class face2face implements avsplugin
{
	private $Name 	= "FaceVerify";
	private $Image 	= "face2face.jpg";
	private $URL	= "";
	
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
    	$render->assign('plugin',	'face2face');
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_admin_plugin_face2face.htm');	
		
	}
	
	public function showUser()
	{
		$settings = pnModGetVar("AVS");
		$render = & pnRender::getInstance('AVS',false);
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_user_plugin_face2face.htm');		
	}
	
	public function validate()
	{
		$pntables = pnDBGetTables();
		$cols = $pntables['AVS_face2face_column'];
		
		$hash 	= FormUtil::getPassedValue('hash','');
		
		$table 	= "AVS_face2face";
		$where 	= "$cols[hash]='$hash'";
		$count 	= DBUtil::selectObjectCount($table,$where);
		$obj  	= DBUtil::selectObject($table,$where);
		
		if ($count == 0) {
			return LogUtil::registerError(__("Ungültiger Verifikationscode!"));
		}

		$settings = pnModGetVar("AVS");
        $dateDiff = DateUtil::getDatetimeDiff($obj['lu_date'],DateUtil::getDatetime());		
		$minutes = $dateDiff['m'] + ($dateDiff['h']*60) + ($dateDiff['d']*24*60); 
        
		if ($obj['status'] != AVS_F2F_STATUS_VERIFY_OK_SEND && 
			$obj['status'] != AVS_F2F_STATUS_MANUELL_NOEMAIL &&
			$obj['status'] != AVS_F2F_STATUS_MANUELL_EMAIL &&	
			$obj['status'] != AVS_F2F_STATUS_VERIFY_AKTIVATED) {
			 return LogUtil::registerError(__("Dieser Verifikationscode ist nicht mehr gültig!<br>Beantrage bitte einen neuen."));
		}
 
		if ($obj['status'] == AVS_F2F_STATUS_VERIFY_AKTIVATED && 
			$minutes > $settings['face2face_verificationlength']) {
			$obj['status'] = AVS_F2F_STATUS_VERIFY_DELAYED;
			DBUtil::updateObject($obj,$table);				
			return LogUtil::registerError(__("Dieser Verifikationscode ist nicht mehr gültig!<br>Beantrage bitte einen neuen."));			
		}
		$obj['status'] = AVS_F2F_STATUS_VERIFY_AKTIVATED;
		DBUtil::updateObject($obj,$table);
		
		pnSessionSetVar("AVS_USED_PLUGIN_HASH",$hash);
		pnSessionSetVar("AVS_ADDITIONAL_DATA",serialize("no data"));
		
		return true;
	}
}

?>