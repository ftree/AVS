<?php
function AVS_init()
{

    if (!DBUtil::createTable('AVS_userinfo')) {
        return false;
    }	

    if (!DBUtil::createTable('AVS_face2face')) {
        return false;
    }    
	pnModSetVar('AVS', 'face2face_verificationlength', 20);
    
    // Set up module hooks
    if (!pnModRegisterHook('item',
                           'create',
                           'API',
                           'AVS',
                           'user',
                           'ItemCreateHook')) {
        return LogUtil::registerError(__('Could not register Hook'));
    } else {
    	pnModAPIFunc('Modules', 'admin', 'enablehooks', array('callermodname' => 'Users', 'hookmodname' => 'AVS'));
    }
    
    // Initialisation successful
    return true;    
}

function AVS_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
		default:
        break;
    }

    // Update successful
    return true;
}

function AVS_delete()
{
	
	DBUtil::dropTable('AVS_userinfo');
	DBUtil::dropTable('AVS_face2face');
	
	pnModUnregisterHook('item','create','API','AVS','user','ItemCreateHook');
	
	//pnModDelVar('AVS');

    // Deletion successful
    return true;
}
?>