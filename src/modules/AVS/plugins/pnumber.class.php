<?php

/*
$root = pnServerGetVar("DOCUMENT_ROOT");
$moddir = pnModGetBaseDir("AVS");
$plugdir = $root."/".$moddir."/plugins/";
Loader::requireOnce($plugdir."avsplugin.class.php");
*/
Loader::requireOnce('modules/AVS/plugins/avsplugin.class.php');

class pnumber implements avsplugin
{
	private $Name 	= "Personalnummer";
	private $Image 	= "pnumber.gif";
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
    	$render->assign('plugin',	'pnumber');
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_admin_plugin_pnumber.htm');	
		
	}
	
	public function showUser()
	{
		$settings = pnModGetVar("AVS");
		$render = & pnRender::getInstance('AVS',false);
    	$render->assign('settings',	$settings);
    	
	    // fetch, process and display template
    	return $render->fetch('AVS_user_plugin_pnumber.htm');		
	}
	
	public function validate()
	{
		$p1 = FormUtil::getPassedValue('p1',null,'POST');
		$p2 = FormUtil::getPassedValue('p2',null,'POST');
		$p3 = FormUtil::getPassedValue('p3',null,'POST');
		$p4 = FormUtil::getPassedValue('p4',null,'POST');
		$p5 = FormUtil::getPassedValue('p5',null,'POST');
/*
		// Security and sanity checks
	    if (!SecurityUtil::confirmAuthKey('AVS')) {
	    	LogUtil::registerError(__("Autentication FAILED!!"));
	    	return false;
	    } else {
*/
			//$ID = $this->createGermanPersonalId();		
			//$ret = $this->checkGermanPersonalId($ID[0],$ID[1],$ID[2],$ID[3]);
			$ret = $this->checkGermanPersonalId($p1.$p2,$p3,$p4,$p5);
			if ($ret === false) {
				LogUtil::registerError(__("Die Ausweisnummer ist ungültig!!"));   	
				return false;
			} else {
				if ($ret['isAdult'] != 1) {
					LogUtil::registerError(__("Du bist leider noch keine 18 Jahre!!"));
					return false;
				} else {
					pnSessionSetVar("AVS_ADDITIONAL_DATA",serialize($ret));
					return true;
				}
			}
//	    }
	}
 
	function checkGermanPersonalId($ID01,$ID02,$ID03,$ID04) {
		$arrResult = array();
		$arrResult['ID'] = $ID01."-".$ID02."-".$ID03."-".$ID04;
		// Prüfsumme ermitteln
		if (!function_exists('buildChecksum')) {
			function buildChecksum($intID) {
				$intMultiplier = 7;
				$intSum = 0;
				if (strlen($intID) == 11) {
					$intIDLength = 9;
				} elseif (strlen($intID) == 7) {
					$intIDLength = 6;
				} else {
					$intIDLength = strlen($intID);
				}
				for ($a=0; $a<$intIDLength; $a++) { 				
					$intSign = (integer) substr($intID,$a,1); 				
					$intTmpSum = ($intSign*$intMultiplier); 				
					$intSum += (integer) 
					substr($intTmpSum,strlen($intTmpSum)-1,1); 				
					if ($intMultiplier == 7) { 					
						$intMultiplier = 3; 				
					} elseif ($intMultiplier == 3) { 					
						$intMultiplier = 1; 				
					} else { 					
						$intMultiplier = 7; 				
					} 			
				} 			
				return substr($intSum,strlen($intSum)-1,1); 		
			} 	
		} 	
		// Erste ID prüfen 	
		if (strlen($ID01) != 11) { 		
			return false; 	
		} 	
		if (buildChecksum($ID01) != substr($ID01,9,1)) {
			return false; 	
		} 	
		$arrResult['firstLocation'] = substr($ID01,0,4); 	
		$arrResult['origin'] = strtoupper($ID01{10}); 	
		$arrResult['isGerman'] = ($arrResult['origin'] == 'D') ? true : false; 	
		
		// Zweite ID prüfen 	
		if (strlen($ID02) != 7) { 		
			return false; 	
		} 	
		if (buildChecksum($ID02) != $ID02{6}) {
			return false; 	
		} 	
		$arrResult['birthday']['day'] = $ID02{4}.$ID02{5}; 	
		$arrResult['birthday']['month'] = $ID02{2}.$ID02{3}; 	
		$arrResult['birthday']['year'] = $ID02{0}.$ID02{1}; 	
		$arrResult['age'] = intval((mktime(0,0,0,date("m"),date("d"),date("Y")) - mktime(0,0,0,$arrResult['birthday']['month'],$arrResult['birthday']['day'],$arrResult['birthday']['year'])) 						/ (3600 * 24 * 365)); 	
		$arrResult['isAdult'] = ($arrResult['age'] >= 18) ? true : false;
	 
		// Dritte ID prüfen
		if (strlen($ID03) != 7) {
			return false;
		}
		if (buildChecksum($ID03) != $ID03{6}) {
			return false;
		}
		$arrResult['expiration']['day'] = $ID03{4}.$ID03{5};
		$arrResult['expiration']['month'] = $ID03{2}.$ID03{3};
		$arrResult['expiration']['year'] = $ID03{0}.$ID03{1};
		// Vierte ID prüfen
		$intCompletePersonalId = substr($ID01,0,10).$ID02.$ID03;
		if (buildChecksum($intCompletePersonalId) != $ID04) {
			return false;
		}
		return $arrResult;
	}
    
	function createGermanPersonalId($intLocation = null,
                                    $intNumber = null,
                                    $strNationality = 'D',
                                    $strBirthday = null, // z.B. 14.12.1981
                                    $strExpiration = null) // z.B. 14.12.2020
                                    // null = Zufällig
	{
		$arrResult = array();
		// Prüfsumme ermitteln
		if (!function_exists('createChecksum')) {
			function createChecksum($intID) {
				$intMultiplier = 7;
				$intSum = 0;
				$intIDLength = strlen($intID);
				for ($a=0; $a<$intIDLength; $a++) {
					$intSign = (integer) substr($intID,$a,1);
					$intTmpSum = ($intSign*$intMultiplier);
					$intSum += $intTmpSum;
					if ($intMultiplier == 7) {
						$intMultiplier = 3;
					} elseif ($intMultiplier == 3) {
						$intMultiplier = 1;
					} else {
						$intMultiplier = 7;
					}
				}
				return substr($intSum,strlen($intSum)-1,1);
			}
		}
	 
		// Parameter prüfen/ Vorbelegungen
		if (is_null($intLocation)) $intLocation = rand(1000,9999);
		if (!preg_match("|^[0-9]+$|",$intLocation) || ($intLocation<1000) || ($intLocation>9999)) {
			return false;
		}
		if (is_null($intNumber)) $intNumber = rand(11000,99999);
		if (!preg_match("|^[0-9]+$|",$intNumber) || ($intNumber<10000) || ($intNumber>99999)) {
			return false;
		}
		if (empty($strNationality)) $strNationality = chr(rand(65,95));
		if ((strlen($strNationality) != 1) || (ord($strNationality)<65) || (ord($strNationality)>95)) {
			return false;
		}
		if (is_null($strBirthday)) {
			$strBirthday = date("d.m.y", mktime(0,0,0,rand(1,12),rand(1,28), rand(date('Y',time())-60,date('Y',time())-16)));
		}
		if (!preg_match("/^([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})$/i",$strBirthday)) {
			return false;
		}
		if (is_null($strExpiration)) {
			$strExpiration = date("d.m.y",mktime(0,0,0,rand(1,12),rand(1,28),rand(date('Y',time())+1,date('Y',time())+5)));
		}
		if (!preg_match("/^([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})$/i",$strExpiration)) {
			return false;
		}
		// Informationen generieren
		$strBirthday = explode('.',$strBirthday);
		$strBirthday = $strBirthday[2].$strBirthday[1].$strBirthday[0];
		$strExpiration = explode('.',$strExpiration);
		$strExpiration = $strExpiration[2].$strExpiration[1].$strExpiration[0];
		$strTemp = $intLocation.$intNumber.createChecksum($intLocation.$intNumber);
		$arrResult[0] = $strTemp.$strNationality;
		$arrResult[1] = $strBirthday.createChecksum($strBirthday);
		$arrResult[2] = $strExpiration.createChecksum($strExpiration);
		$arrResult[3] = createChecksum($strTemp.$arrResult[1].$arrResult[2],true);
		return $arrResult;
	}
	
}

?>