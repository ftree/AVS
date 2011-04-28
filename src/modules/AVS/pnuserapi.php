<?php

function AVS_userapi_check()
{
	$plugin = pnSessionGetVar("AVS_PLUGIN","NONE");
	pnSessionDelVar("AVS_PLUGIN");
	if ($plugin == "NONE") {
		return false;
	} else {
		pnModAPIFunc('AVS','user','getPlugins');
		$class = new $plugin;
		$ret = $class->validate();
		if ($ret) {
			pnSessionSetVar("AVS_USED_PLUGIN",$plugin);
		}
		return $ret;
	}
}
/*
function AVS_userapi_confirmAVSKey($args)
{
	$modname = "AVS";
	$authid  = $args['authid'];

    if (empty($modname)) {
        $modname = pnModGetName();
    }

    // get the module info
    $modinfo = pnModGetInfo(pnModGetIDFromName($modname));
    $modname = strtolower($modinfo['name']);

    // get the array of randomed values per module and check if exists
    $rand_arr = SessionUtil::getVar('rand');
    if (!isset($rand_arr[$modname])) {
        return false;
    } else {
        $rand = $rand_arr[$modname];
    }

    // Regenerate static part of key
    $key = $rand . $modname;

    // validate useragent
    if (pnConfigGetVar('sessionauthkeyua')) {
        $useragent = sha1(pnServerGetVar('HTTP_USER_AGENT'));
        if (SessionUtil::getVar('useragent') != $useragent) {
            return false;
        }
    }

    // Test works because timestamp is embedded in authkey and appended
    // at the end of the authkey, so we can test validity of authid as
    // well as the number of seconds elapsed since generation.
    $keyexpiry = (int) pnConfigGetVar('keyexpiry');
    $timestamp = ($keyexpiry > 0 ? substr($authid, 40, strlen($authid)) : '');
    $key .= $timestamp;
    // check build key against authid
    if (sha1($key) == substr($authid, 0, 40)) {
        // now test if time expired
        $elapsedTime = (int) ((int) $timestamp > 0 ? time() - $timestamp : $keyexpiry - 1);
        if ($elapsedTime < $keyexpiry) {
            $rand_arr[$modname] = RandomUtil::getString(32, 40, false, true, true, false, true, true, false);
            SessionUtil::setVar('rand', $rand_arr);

            return true;
        }
    }

    return false;
}
*/

function AVS_userapi_getPlugins() {
	$root = pnServerGetVar("DOCUMENT_ROOT");
	$moddir = pnModGetBaseDir("AVS");
	$plugdir = $root."/".$moddir."/plugins/";

    $plugins = array();

	$handle = opendir($plugdir);
	if ($handle ) {
	    while (false !== ($file = readdir($handle))) {

	        if ($file != "." && $file != ".." && $file != "avsplugin.class.php" && strstr($file,".class.php") !== false) {
	        	$File = $plugdir."/".$file;
	        	if (!is_dir($File)) {
		        	Loader::requireOnce($File);
		            $parts = explode(".",$file);
		            $name = $parts[0];
					$plugins[] = $name;
	        	}
	        }
	    }
	    closedir($handle);
	}

	return $plugins;
}

function AVS_userapi_ItemCreateHook ($args) {
	$objectid  = $args['objectid'];
	$extrainfo = $args['extrainfo'];

	// new User was created
	if ($extrainfo['module'] == 'Users') {

		$pntables = pnDBGetTables();
		$cols = $pntables['AVS_face2face_column'];

		$usedplugin = pnSessionGetVar("AVS_USED_PLUGIN","NONE");
		$add_data 	= pnSessionGetVar("AVS_ADDITIONAL_DATA","no data");
		pnSessionDelVar("AVS_USED_PLUGIN");
		pnSessionDelVar("AVS_ADDITIONAL_DATA");
		
		$obj = array('user_id'	=> $objectid,
					 'avs' 		=> $usedplugin,
					 'data'		=> $add_data);

		if ($usedplugin == "face2face") {
			$hash = pnSessionGetVar("AVS_USED_PLUGIN_HASH","NONE");
			$obj1 = array('status' => AVS_F2F_STATUS_REGISTERED,
						  'userid' => $objectid);
			$obj1 = DBUtil::updateObject($obj1,'AVS_face2face',"$cols[hash] = '$hash'");
			$obj1 = DBUtil::selectObject('AVS_face2face',"$cols[hash] = '$hash'");

			$data = array('face2face_id' => $obj1['id']);
			$obj['data'] = serialize($data);
		}

		DBUtil::insertObject($obj,'AVS_userinfo','info_user_id');

	}

	return $extrainfo;
}

function AVS_userapi_doFaceValidate($args)
{
	$ok = true;
	$mail = $args['email'];
	$imageface = $args['imageface'];
	$imagepass = $args['imagepass'];

	$settings = pnModGetVar("AVS");
	$imagepath = $settings['face2face_imagepath'];
	$imagepath_orig = $imagepath."orig/";
	$imagepath_thumb = $imagepath."thumb/";

	$hash = md5($mail.time());

	$obj = array('mail' => $mail,
				 'hash' => $hash);

	$ret = DBUtil::insertObject($obj,'AVS_face2face');
	$id = sprintf("%07d",$ret['id']);

	$name = pathinfo($imagepath.$imageface['name']);
	$filename = $id."-face.".$name['extension'];
	$obj['image_face'] = $filename;
	if(!move_uploaded_file($imageface['tmp_name'], $imagepath_orig.$filename)) {
		$ok = false;
	}
	if(pnModAPIFunc('Thumbnail',
				 	'user',
					'generateImage',
					array('srcFilename' => $imagepath_orig.$filename,
						  'dstFilename' => $imagepath_thumb.$filename,
						  'w'			=> 100,
						  'h'			=> 100)) === false ) {
		$ok = false;
		LogUtil::registerError(__('Fehler beim Gesichtbild upload!'));
	}

	if(pnModAPIFunc('Thumbnail',
					'user',
					'generateImage',
					array('srcFilename' => $imagepath_orig.$filename,
						  'dstFilename' => $imagepath.$filename,
						  'w'			=> 640,
						  'h'			=> 480)) === false ) {
		$ok = false;
		LogUtil::registerError(__('Fehler beim Gesichtbild upload!'));
	}

	$name = pathinfo($imagepath.$imagepass['name']);
	$filename = $id."-pass.".$name['extension'];
	$obj['image_pass'] = $filename;
	if(!move_uploaded_file($imagepass['tmp_name'], $imagepath_orig.$filename)) {
		$ok = false;
	}
	if(pnModAPIFunc('Thumbnail',
					'user',
					'generateImage',
					array('srcFilename' => $imagepath_orig.$filename,
						  'dstFilename' => $imagepath_thumb.$filename,
						  'w'			=> 100,
						  'h'			=> 100)) === false ) {
		$ok = false;
    	LogUtil::registerError(__('Fehler beim Passbild upload!'));
	}

	if(pnModAPIFunc('Thumbnail',
					'user',
					'generateImage',
					array('srcFilename' => $imagepath_orig.$filename,
						  'dstFilename' => $imagepath.$filename,
						  'w'			=> 640,
						  'h'			=> 480)) === false ) {
		$ok = false;
    	LogUtil::registerError(__('Fehler beim Passbild upload!'));
	}
	DBUtil::updateObject($obj,'AVS_face2face');
	if ($ok) {
    	$sitename = pnConfigGetVar('sitename');
    	$render = & pnRender::getInstance('AVS',false);
    	$render->assign('sitename',	$sitename);
	    $msg = $render->fetch('AVS_user_mail_adminnotify.htm');

		$tomail = pnConfigGetVar('adminmail');
		$subject = $sitename.__(" - AVS: Ein neuer User braucht eine Face2Face Verifizierung!");
	    pnModAPIFunc('Mailer',
					 'user',
					 'sendmessage',
					 array('toaddress' 	=> $tomail,
					 	   'subject' 	=> $subject,
					 	   'body' 		=> $msg,
					 	   'html' 		=> true));

	}
	return $ok;
}


?>
