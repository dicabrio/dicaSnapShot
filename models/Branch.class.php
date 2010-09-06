<?php

/**
 *
 *	Development server control panel.
 *	Version 0.1, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *
 *	This class is used to select/manage branches on the development image.
 *
 *	It's a pretty 'dumb' class, e.g. no caching is used as of yet.
 *	I know it could speed things a bit up, but just don't have enough time.
 *	It's still fast enough for what it's worth.
 *
 */


class Branch {

	function getAllBranches () {

		// Get all the projects
		$rProjectSQL = mysql_query("SELECT id, name
									FROM projects
									WHERE 1");

		$aReturn = array();
		if (mysql_num_rows($rProjectSQL)) {
			while ($aProjectInfo = mysql_fetch_assoc($rProjectSQL)) {

				$iProjectID	= ((int) $aProjectInfo['id']);

				$aReturn[$iProjectID] = array('name' => $aProjectInfo['name'], 'branches' => array());

				// Get all the branches from this project
				$rBranchSQL = mysql_query("	SELECT id, dir_ad as name, is_active as is_current
											FROM branches
											WHERE project_id = '$iProjectID' ORDER BY dir_ad");
				if (mysql_num_rows($rBranchSQL)) {
					while ($aBranchInfo = mysql_fetch_assoc($rBranchSQL)) {

						$iBranchID	= $aBranchInfo['id'];
						$aReturn[$iProjectID]['branches'][$iBranchID] = array(	'name'			=> $aBranchInfo['name'],
																				'is_current'	=> $aBranchInfo['is_current']);

					}
				}
			}
				
		}

		return $aReturn;
	}

	/**
	 *
	 * 	This function renews the branch cache (e.g. replaces information in the table branches)
	 *	It does that by calling searchBranches
	 *
	 *	Accepts:	$iProjectID - NULL means every project is refreshed, any other number for only having that project refreshed
	 * 	Returns:	TRUE if succesfull, or FALSE if otherwise
	 *
	 */

	function renewBranchCache($iProjectID = NULL, $bFast = false) {
		$sQuery	= '	SELECT p.id, p.rootdir, p.symdir, d.directory
					FROM projects p
					INNER JOIN detection d ON p.id = d.project_id
					WHERE ';

		if ($iProjectID !== NULL) {
			$sQuery .= 'p.id = '.((int) $iProjectID);
				
			// Remove every entry of this branch
			mysql_query("DELETE FROM branches WHERE project_id = ".((int) $iProjectID));
		}
		else {
			$sQuery .= '1';
				
			// Clean table
			mysql_query("TRUNCATE TABLE branches");
				
		}

		$rPDetection = mysql_query($sQuery);

		if (mysql_num_rows($rPDetection)) {
			// Determine step size
			if ($bFast) {
				$iStepSize	= 2;
			}
			else {
				$iStepSize	= null;
			}
				
			$aToSearch = array();
			while ($aDetectionInfo = mysql_fetch_assoc($rPDetection)) {

				$iCurrentProjectID	= ((int) $aDetectionInfo['id']);
				if (!isset($aToSearch[$iCurrentProjectID])) {
					$aToSearch[$iCurrentProjectID] = array(	'rootdir' => $aDetectionInfo['rootdir'],
															'symdir' => $aDetectionInfo['symdir'],
															'detections' => array());
				}

				$aToSearch[$iCurrentProjectID]['detections'][] = $aDetectionInfo['directory'];
			}
				
			foreach ($aToSearch as $iCurrentProjectID => $aSearchInfo) {
				$aBranchDirectories = $this->searchBranches($aSearchInfo['rootdir'],
				$aSearchInfo['detections'],
				$iStepSize);
					
				$iLengthRootdir		= strlen($aSearchInfo['rootdir']);
				$sSymDir			= (substr($aSearchInfo['symdir'], -1, 1) == '/') ? substr($aSearchInfo['symdir'], 0, -1) : $aSearchInfo['symdir'];

				if (is_link($sSymDir)) {
					$sActiveDirectory	= readlink($sSymDir);
				}
				else {
					$sActiveDirectory	= NULL;
				}

				foreach ($aBranchDirectories as $sBranchDirectory) {
						
					// Determine whether this directory is active
					$iIsActive			= (int) ($sActiveDirectory === $sBranchDirectory);
						
					// Make sBranchDirectory only an addendum
					$sBranchDirectory	= mysql_real_escape_string(substr($sBranchDirectory, $iLengthRootdir+1));
						
					// Insert into the cache table
					mysql_query("INSERT INTO branches (`project_id`, `dir_ad`, `is_active`)
								 VALUES ('$iCurrentProjectID', '$sBranchDirectory', '$iIsActive')");
				}
			}
				
		}

		return true;

	}

	public function switchToBranch ($iProjectID, $iBranchID) {
		// Get all the information.
		$iBranchID	= ((int) $iBranchID);
		$iProjectID	= ((int) $iProjectID);

		$rNewBranch = mysql_query("	SELECT CONCAT(p.rootdir, b.dir_ad) as linkdir, p.symdir
									FROM branches b
									INNER JOIN projects p ON p.id = b.project_id
									WHERE b.id = $iBranchID AND b.project_id = $iProjectID");
		if (mysql_num_rows($rNewBranch)) {
			$aNewBranchInfo = mysql_fetch_assoc($rNewBranch);
				
			// If the link exists
			$sLinkDir		= $aNewBranchInfo['linkdir'];
			$sSymDir		= substr($aNewBranchInfo['symdir'], 0, -1);
				
			// If the file already exists, try to delete it first.
			if (file_exists($sSymDir) || is_link($sSymDir)) {
				if (is_link($sSymDir) || is_file($sSymDir)) {
					// It's a file (or symbolic link), so try to delete it.
					$bResult = unlink($sSymDir);
					if (!$bResult) {
						return false;
					}
				}
				else {
					// It's a directory, return false since we cannot delete it.
					return false;
				}
			}
			// Create a symlink
			$bResult		= symlink($sLinkDir, $sSymDir);
				
			if ($bResult) {
				// Update is_active
				mysql_query("UPDATE branches SET is_active='0' WHERE project_id = $iProjectID AND is_active='1'");
				mysql_query("UPDATE branches SET is_active='1' WHERE id = $iBranchID");
			}
				
			return $bResult;
		}

		return false;

	}

	/**
	 *
	 *	This function searches for branch(es) given a root directory and detection points
	 *	It does by recursively searching every directory, and checking for the detection points (directory)
	 *
	 * 	Because this is done recursively, and lots of directories need to be analysed, don't call this every time
	 * 	The results are cached after all in a table called branches.
	 *
	 * 	Accepts:	$sRootDir			- the root directory
	 * 				$aDetectionPoints	- an array with directories. Please note that it must be a directory name, not a path!
	 *
	 *	Returns:	an array with the directories that are branches.
	 *
	 */

	private function searchBranches($sRootDir, $aDetectionPoints, $iMaxRecursion) {
		$rDirectory = opendir($sRootDir);
		$aCheckList	= array();

		$aMatches	= array();

		//echo 'Scanning: '.$sRootDir.', with recursion level-left: '.$iMaxRecursion.'<br />';

		foreach ($aDetectionPoints as $sDetectionPoint) {
			$aCheckList[$sDetectionPoint] = $sDetectionPoint;
		}

		while (($sEntry = readdir($rDirectory)) !== FALSE) {
			if ($sEntry == '.' || $sEntry == '..') {
				continue;
			}
				
			if (is_dir($sRootDir.'/'.$sEntry)) {
					
				// If the directory is in our check list, remove it
				if (isset($aCheckList[$sEntry])) {
					unset($aCheckList[$sEntry]);
						
					// If the checklist is empty, this is a branch
					if (!count($aCheckList)) {
						$aMatches[] = $sRootDir;
					}
						
				}

				// Check if we already passed our recursion threshold.
				if ($iMaxRecursion >= 1) {
					// Search this directory also
					$aMatches = array_merge($aMatches, $this->searchBranches($sRootDir.'/'.$sEntry, $aDetectionPoints, $iMaxRecursion-1));
				}
				elseif ($iMaxRecursion === null) {
					// Search this directory also
					$aMatches = array_merge($aMatches, $this->searchBranches($sRootDir.'/'.$sEntry, $aDetectionPoints, NULL));
				}
			}
		}

		closedir($rDirectory);

		return $aMatches;
	}
}
?>