<?php

function AVS_user_main()
{
	$avs = FormUtil::getPassedValue('avs','NONE');
	$plugins = pnModAPIFunc('AVS','user','getPlugins');		

	$render = & pnRender::getInstance('AVS',false);
	$actives = pnModGetVar("AVS","active");
	if ($avs == 'NONE') {
		$avs = array();
	    foreach ($plugins as $plugin) {
			if (in_array($plugin,$actives)) {
		    	$class = new $plugin;	        	
				$avs[]= array('name' => $class->name(),
							  'plugin' => $plugin);
			}
		}
	
		$render->assign('plugins', $avs);
	    // fetch, process and display template
	    return $render->fetch('AVS_user_main.htm');
	} else {
		pnSessionSetVar("AVS_PLUGIN",$avs);
		
		$class = new $avs;
	    // fetch, process and display template
	    return $class->showUser();
	}
}
/*
function AVS_user_check()
{
	return pnModAPIFunc('AVS','user','check');
}
*/
function AVS_user_face2face()
{
	$hash = FormUtil::getPassedValue('hash','');
	if ($hash == "") {
		$mail 		= FormUtil::getPassedValue('mail','','POST');
		$imageface 	= FormUtil::getPassedValue('imageface',null,'FILES');
		$imagepass 	= FormUtil::getPassedValue('imagepass',null,'FILES');
	
		$ok = true;
		if ($mail == "") {
	        LogUtil::registerError(__('Gibt bitte eine gültige eMail Adresse ein!'));
	        $ok = false;
	    }
	    if ($imageface['name'] == "") {
	    	LogUtil::registerError(__('Gesichtbild fehlt!'));
	        $ok = false;	
	    } elseif ($imageface['error'] != UPLOAD_ERR_OK) {
	    	LogUtil::registerError(__('Fehler beim Gesichtbild upload!'));
	        $ok = false;
	    }
		if ($imagepass['name'] == "") {
	    	LogUtil::registerError(__('Passbild fehlt!'));
	        $ok = false;	
	    } elseif ($imagepass['error'] != UPLOAD_ERR_OK) {
	    	LogUtil::registerError(__('Fehler beim Passbild upload!'));
	        $ok = false;
	    }
	        
	    if ($ok) {
	    	$ok = pnModAPIFunc('AVS',
	    					   'user',
	    					   'doFaceValidate', 
	    					   array('email'=> $mail,
	    					         'imageface' => $imageface,
	    					   		 'imagepass' => $imagepass));
	    }
	    if (!$ok) {
	    	return pnRedirect(pnModURL('AVS','user','main',array('avs'=>'face2face')));
	    } else  {
			LogUtil::registerStatus("Deine Daten wurden zur Kontrolle eingereicht.");    	
	    	return pnRedirect(pnModURL('AVS','user','main'));	
	    }
	} else {
		pnSessionSetVar("AVS_PLUGIN","face2face");
		return pnRedirect(pnModURL('Users','user','register', array('hash'=>$hash)));
	}
}

?>