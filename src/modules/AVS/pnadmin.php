<?php

function AVS_admin_main()
{
    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

	$render = & pnRender::getInstance('AVS',false);

    $plugins = pnModAPIFunc('AVS','user','getPlugins');
	
    $actives = pnModGetVar("AVS","active");
    $avs = array();
    foreach ($plugins as $plugin) {
		$class 			= new $plugin;
		$plug['name'] 	= $class->name();
		$plug['value'] 	= $plugin;
		$plug['active'] = in_array($plugin,$actives) ? "1" : "0";
    	$avs[] 			= $plug; 	        	
	}
    
	$render->assign('plugins',		$avs);
    // fetch, process and display template
    return $render->fetch('AVS_admin_main.htm');
}


function AVS_admin_doVerify() {
    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    if (!SecurityUtil::confirmAuthKey('AVS')) {
    	return LogUtil::registerAuthidError(pnModURL('AVS', 'admin', 'showWaitingUsers'));
    }

    $id = FormUtil::getPassedValue('id',-1);
    $verify = FormUtil::getPassedValue('verify',"");
    $reason = FormUtil::getPassedValue('reason',"");
	if (!(strcmp($verify,"+") == 1 || strcmp($verify,"-") == 1)) {
    	LogUtil::registerError(__("Wrong Verify value!"));
		return pnRedirect(pnModURL('AVS', 'admin', 'showWaitingUsers'));
    }

    pnDBGetTables();
    $obj = DBUtil::selectObject("AVS_face2face","face_id = $id");

    $sitename = pnConfigGetVar('sitename');
    if ($verify == 1) {

    	$obj['status'] = AVS_F2F_STATUS_VERIFY_OK_SEND;
    	DBUtil::updateObject($obj,"AVS_face2face");

    	$render = & pnRender::getInstance('AVS',false);
    	$render->assign('hash',	$obj['hash']);
    	$render->assign('sitename',	$sitename);
	    $msg = $render->fetch('AVS_admin_mail_verifyok.htm');

		$tomail = $obj['mail'];
		$subject = __("Gesicht verifiziert für ").$sitename;
	    pnModAPIFunc('Mailer',
					 'user',
					 'sendmessage',
					 array('toaddress' 	=> $tomail,
					 	   'subject' 	=> $subject,
					 	   'body' 		=> $msg,
					 	   'html' 		=> true));
		LogUtil::registerStatus("User verifiziert und Mail versandt.");

    } elseif ($verify == 0) {
    	$obj['status'] = AVS_F2F_STATUS_VERIFY_DENIED;
    	DBUtil::updateObject($obj,"AVS_face2face");


    	$render = & pnRender::getInstance('AVS',false);
    	$render->assign('reason',	$reason);
    	$render->assign('sitename',	$sitename);
	    $msg = $render->fetch('AVS_admin_mail_verifydenied.htm');

		$tomail = $obj['mail'];
		$subject = __("Gesichtsverifikation abgelehnt");
	    pnModAPIFunc('Mailer',
					 'user',
					 'sendmessage',
					 array('toaddress' 	=> $tomail,
					 	   'subject' 	=> $subject,
					 	   'body' 		=> $msg,
					 	   'html' 		=> true));
		LogUtil::registerStatus("User abgelehnt und Mail versandt.");

    }

	return pnRedirect(pnModURL('AVS', 'admin', 'showWaitingUsers'));
}

function AVS_admin_showVerify() {
    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }
   	$id = FormUtil::getPassedValue('id',-1);

	$render = & pnRender::getInstance('AVS',false);
	$render->assign('id',	$id);


    // fetch, process and display template
    return $render->fetch('AVS_admin_showVerify.htm');
}

function AVS_admin_showEnteredUsers() {
    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

	$page = FormUtil::getPassedValue('page',1);

    $pntables   = pnDBGetTables();
    $cols = $pntables['AVS_userinfo_column'];

	$render = & pnRender::getInstance('AVS',false);

	$where = "";
	$sort = "$cols[cr_date] DESC";
	$limit = 25;
	$startnum = ($page - 1) * $limit;

	$users = DBUtil::selectObjectArray('AVS_userinfo',$where, $sort, $startnum, $limit);
	$rowcount = DBUtil::selectObjectCount('AVS_userinfo',$where);
	
	$rows = array();
	foreach ($users as $user) {
		$data = unserialize($user['data']);
		$user['dataarray'] = $data;
		$rows[] = $user;
	}
	
	$render->assign('users',	$rows);
	$render->assign('limit',	$limit);
	$render->assign('rowcount',	$rowcount);

    // fetch, process and display template
    return $render->fetch('AVS_admin_showEnteredUsers.htm');

}

function AVS_admin_showWaitingUsers() {
    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $status	= FormUtil::getPassedValue('status',AVS_F2F_STATUS_WAITING);
    
    $pntables   = pnDBGetTables();
    $cols = $pntables['AVS_face2face_column'];


	$render = & pnRender::getInstance('AVS',false);

	$where = $cols['status']."=".$status;

	$users = DBUtil::selectObjectArray('AVS_face2face',$where);

	$render->assign('users',	$users);

    // fetch, process and display template
    return $render->fetch('AVS_admin_showWaitingUsers.htm');

}

function AVS_admin_showImage() {
    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

	$id = FormUtil::getPassedValue('id',-1);
	$face = FormUtil::getPassedValue('face');
	$size = FormUtil::getPassedValue('size','normal');

	$settings = pnModGetVar("AVS");
	$imagepath = $settings['face2face_imagepath'];
	$imagepath_orig = $imagepath."orig/";
	$imagepath_thumb = $imagepath."thumb/";

	$obj = DBUtil::selectObjectByID('AVS_face2face',$id);

	if (($face ? $obj['image_face'] : $obj['image_pass']) != "") {
		switch ($size) {
			case 'normal':
				$file = $imagepath.($face ? $obj['image_face'] : $obj['image_pass']);
				break;
			case 'thumb':
				$file = $imagepath_thumb.($face ? $obj['image_face'] : $obj['image_pass']);
				break;
			case 'original':
				$file = $imagepath_orig.($face ? $obj['image_face'] : $obj['image_pass']);
				break;
	
		}

	    $values['srcFilename'] = $file ;
	    $values['returnRAW'] = true;
	
	    $ret = pnModAPIFunc('Thumbnail', 'user', 'generateImage', $values);
	    header('Content-Type: image/jpeg');
	    echo $ret;
	} else {
		echo "";
	}
    exit;	
    return true;

}


function AVS_admin_showSettings() {

    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    pnModAPIFunc('AVS','user','getPlugins');

	$plugin = FormUtil::getPassedValue('plugin','NONE');
	if ($plugin != 'NONE') {
		$class = new $plugin;

		$plugincode = $class->showAdmin();
		$render = & pnRender::getInstance('AVS',false);
		$render->assign('plugin',		$plugin);
		$render->assign('name',			$class->name());
		$render->assign('image',		$class->image());
		$render->assign('url',			$class->url());
		$render->assign('plugincode',	$plugincode);

    	return $render->fetch('AVS_admin_plugin_main.htm');
	} else {
		return AVS_admin_main();
	}
}

function AVS_admin_modifySettings() {

    // perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $settings 	= FormUtil::getPassedValue ('settings');
    $main 		= FormUtil::getPassedValue ('main','0');
    if ($main == "1") {
    	$actives = FormUtil::getPassedValue ('active');
    	pnModDelVar("AVS","active");
    	$values = array();
    	foreach ($actives as $key => $value) {
    		$values[] = $key;
    	}
    	pnModSetVar("AVS","active",$values);
    }
    if (isset($settings['face2face_imagepath'])) {
    	$path = $settings['face2face_imagepath'];
		$path = str_replace("\\","/",$path);
		$lastchar = substr($path,-1);
		if ($lastchar != "\\" && $lastchar != "/" && $path != "") {
			$path = $path."/";
		}
		$settings['face2face_imagepath'] = $path;
		if (!is_dir($path."thumb")) {
			mkdir($path."thumb");
		}
    	if (!is_dir($path."orig")) {
			mkdir($path."orig");
		}
    }

    pnModSetVars("AVS",$settings);

	LogUtil::registerStatus (__("Settings saved."));

    return AVS_admin_showSettings();
}

function AVS_admin_generateF2Fcodes() {
	// perform permission check
    if (!SecurityUtil::checkPermission('AVS::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }
    $count 	= FormUtil::getPassedValue('face2face_countcodes',0);
    if ($count > 0 ) {
    	for ($i=0; $i<$count; $i++) {
    		$hash = rand(100000,999999);
    		$where = "face_hash = '$hash'";
    		$retCount = DBUtil::selectObjectCount('AVS_face2face',$where);
    		while ($retCount > 0 ) {
	    		$hash = rand(100000,999999);
	    		$where = "face_hash = '$hash'";
	    		$retCount = DBUtil::selectObjectCount('AVS_face2face',$where);
			}
			$obj = array('mail' 	=> 'manuell',
						 'hash' 	=> $hash,
						 'status' 	=> AVS_F2F_STATUS_MANUELL_NOEMAIL);
			DBUtil::insertObject($obj,'AVS_face2face');
    	}
    }
	LogUtil::registerStatus ($count.__("codes wurden generiert."));
		    
    //return AVS_admin_main();
	return pnRedirect(pnModURL('AVS', 'admin'));
}


?>