<?php 

	$result = array();
	require_once(EXTENSIONS . '/cdi/lib/class.cdiutil.php');
	require_once(EXTENSIONS . '/cdi/lib/class.cdimaster.php');
	require_once(EXTENSIONS . '/cdi/lib/class.cdislave.php');
	require_once(EXTENSIONS . '/cdi/lib/class.cdidbsync.php');
	require_once(EXTENSIONS . '/cdi/lib/class.cdidumpdb.php');
	require_once(EXTENSIONS . '/cdi/lib/class.cdilogquery.php');
	require_once(EXTENSIONS . '/cdi/lib/class.cdipreferences.php');
	
	// We should not be processing any queries when the extension is disabled or when we are the Master instance
	if((!class_exists('Administration')) || !CdiUtil::isEnabled()) {
	   	$result["status"] = "error";
	   	$result["message"] = "You can only execute actions from within Symphony and when the CDI extension is enabled";
	} 
	
	// Clean the database and log files when the cdi_clear action is called
	if(isset($_POST["action"]["cdi_clear"])) {
		try {
			if(CdiUtil::isCdiMaster()) {
				CdiMaster::uninstall();
				CdiMaster::install();
			} else if (CdiUtil::isCdiSlave()) {
				CdiSlave::uninstall();
				CdiSlave::install();
			} else if(CdiUtil::isCdiDBSync()) {
				CdiDBSync::uninstall();
				CdiDBSync::install();
			}
		} catch(Exception $e) {
			$result["status"] = "error";
			$result["message"] = $e->getMessage();
		}
		$result["status"] = 'success';
	}

	// Clean the database backup list when cdi_clear_restore action is called
	else if(isset($_POST["action"]["cdi_clear_restore"])) {
		try {
			CdiDumpDB::uninstall();
			CdiDumpDB::install();
		} catch(Exception $e) {
			$result["status"] = "error";
			$result["message"] = $e->getMessage();
		}
		$result["status"] = 'success';
	}
	
	// CDI Export
	else if(isset($_POST["action"]["cdi_export"])) {
		try {
			CdiDumpDB::backup();
			$result["result"] = htmlspecialchars(CdiPreferences::appendRestore()->generate());
		} catch(Exception $e) {
			$result["status"] = "error";
			$result["message"] = $e->getMessage();
		}
		$result["status"] = 'success';
	} 
	
	// No action? Error!
	else {
	   	$result["status"] = "error";
	   	$result["message"] = "You can only execute actions if you actually post one!";
	}

	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');
	echo json_encode($result);
	die();
?>