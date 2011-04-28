<?php

function AVS_pntables()
{
	
	
	define('AVS_F2F_STATUS_WAITING',		 	0);
	define('AVS_F2F_STATUS_VERIFY_OK_SEND',		10);
	define('AVS_F2F_STATUS_MANUELL_NOEMAIL', 	11);
	define('AVS_F2F_STATUS_MANUELL_EMAIL', 		12);
	define('AVS_F2F_STATUS_VERIFY_DENIED',		20);
	define('AVS_F2F_STATUS_VERIFY_AKTIVATED',	30);
	define('AVS_F2F_STATUS_REGISTERED',			40);
	define('AVS_F2F_STATUS_VERIFY_DELAYED',		50);
	
	// Initialise table array
    $pntable = array();
	
    // userinfo table
    $pntable['AVS_userinfo'] = DBUtil::getLimitedTablename('avs_userinfo');
    $pntable['AVS_userinfo_column'] = array ('user_id'    	    => 'info_user_id',
										     'avs'    			=> 'info_avs',
    										 'data'				=> 'info_data');

    // Add Primary Index
    $pntable['AVS_userinfo_primary_key_column'] = 'user_id';

    // Add some standard colums to the table
	ObjectUtil::addStandardFieldsToTableDefinition ($pntable['AVS_userinfo_column'],"info");

    // column definition
    $pntable['AVS_userinfo_column_def'] = array('user_id'    	=> "I  		PRIMARY	",
												'avs'  			=> "C(254)	NOTNULL	",
												'data' 			=> "X				");
	// Add some standard colums to the table
	ObjectUtil::addStandardFieldsToTableDataDefinition ($pntable['AVS_userinfo_column_def']);
    
	/*********************************************************************************************/
	
    // userinfo table
    $pntable['AVS_face2face'] = DBUtil::getLimitedTablename('avs_face2face');
    $pntable['AVS_face2face_column'] = array ('id'    	    => 'face_id',
										      'mail'    	=> 'face_mail',
    										  'image_face'	=> 'face_imagefcae',
    										  'image_pass'	=> 'face_imagepass',
    										  'hash'		=> 'face_hash',
    										  'status'		=> 'face_status',
    										  'userid'		=> 'face_userid');
	// Add Primary Index
    $pntable['AVS_userinfo_primary_key_column'] = 'id';
	
	// Add some standard colums to the table
	ObjectUtil::addStandardFieldsToTableDefinition ($pntable['AVS_face2face_column'],"face");    
    
    // column definition
    $pntable['AVS_face2face_column_def'] = array('id'    		=> "I AUTO PRIMARY	",
												 'mail'  		=> "C(254)	NOTNULL	",
											     'image_face'  	=> "C(20)			",
											     'image_pass'  	=> "C(20)			",
												 'hash' 		=> "C(50)	NOTNULL	",
    											 'status'		=> "I		NOTNULL DEFAULT 0",
    											 'userid'		=> "I				",);    
	
	// Add some standard colums to the table
	ObjectUtil::addStandardFieldsToTableDataDefinition ($pntable['AVS_face2face_column_def']);

	
    return $pntable;
    
}
?>