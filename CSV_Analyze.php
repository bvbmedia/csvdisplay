<?PHP

/***************************************************************
*  Copyright notice
*
*  (c) 2007 Christian Wolff <chris@connye.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
	the CSV_Analyze Class was Written by S.Ruttloff with Permission to Freely Copy and Modify it.
	his http://www.cmsr.sruttloff.de
*/
// 
class CSV_Analyze{
	function find_separator($set){
		$squode = FALSE;
		$quode  = FALSE;
		$bslash = FALSE;
		if (!$set) return False;
		for ($i = 0; $i <= strlen($set); $i++){
			switch (TRUE){
				case substr($set, $i, 1) == '\\':
					if ($bslash == FALSE)
						$bslash = $i;  //bslash setzen
					break;
				case substr($set, $i, 1) == '"':
					if ($quote && !$bslash)
						$quote  = FALSE;  //quote setzen
					elseif (!$squote)
						$quote  = TRUE;  //quote setzen
					break;
				case substr($set, $i, 1) == '\'':
					if ($squote && !$bslash)
						$squote  = FALSE;  //quote setzen
					elseif (!$quote)
						$squote  = TRUE;  //quote setzen
				break;
			}
			#wenn kein squote oder quote aktiv und kein standard zeichen
			if (!$squote && !$quote && !preg_match("=[a-zA-Z0-9_\-\+\"\']{1}=", substr($set, $i, 1))){
				$possible[ord(substr($set, $i, 1))]++;
			}
		}
		# sortiere nach Anzahl
		arsort ($possible);
		$array = each($possible);
		return chr($array[0]);
	}
	
	function split_in_rows($set, $separator = ";"){
		if (!$set) return False;
		$squode = FALSE;
		$quode  = FALSE;
		$bslash = FALSE;
		$temp = "";
		$laststart = 0;
		for ($i = 0; $i <= strlen($set); $i++){
			switch (TRUE){
				case substr($set, $i, 1) == '\\':
					if ($bslash == FALSE)
						$bslash = $i;  //bslash setzen
					break;
				case substr($set, $i, 1) == '"':
					if ($quote && !$bslash)
						$quote  = FALSE;  //quote setzen
					elseif (!$squote)
						$quote  = TRUE;  //quote setzen
					break;
				case substr($set, $i, 1) == '\'':
					if ($squote && !$bslash)
						$squote  = FALSE;  //quote setzen
					elseif (!$quote)
						$squote  = TRUE;  //quote setzen
					break;
			}
			#wenn kein squote oder quote aktiv und kein standard zeichen
			if ($squote || $quote || substr($set, $i, 1) != $separator) continue;
			$val = preg_replace("=^[\"']=", "" ,trim(substr($set, $laststart, $i - $laststart)));
			$temp[] = preg_replace("=[\"']$=", "" ,$val);
			$laststart = $i + 1;
		}
		# am Ende Angekommen, noch den Rest anhängen
		$val = preg_replace("=^[\"']=", "" ,trim(substr($set, $laststart, strlen($set) - $laststart)));
		$temp[] = preg_replace("=[\"']$=", "" ,$val);
		return $temp;
		#return split ( $separator, $set);
	}
}

?>