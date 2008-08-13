<?php
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
 * Plugin 'Dispaly CSV data' for the 'csvdisplay' extension.
 *
 * @author	Christian Wolff <chris@connye.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_csvdisplay_pi1 extends tslib_pibase {
	var $prefixId = 'tx_csvdisplay_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_csvdisplay_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'csvdisplay';	// The extension key.
	var $pi_checkCHash = FALSE;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
        $this->pi_setPiVarDefaults();
        $this->pi_USER_INT_obj=0;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		global $TSFE,$LOCAL_LANG;
	
		if(is_array($conf['overwrite.'])){
			$this->cObj->data=$data_array = t3lib_div::array_merge($this->cObj->data,$conf['overwrite.']);
		}
		
		$header = explode('|',$this->cObj->data['tx_csvdisplay_colheader']);
		
		$filename = $this->cObj->data['tx_csvdisplay_csvfile'];
		if(!is_readable(PATH_site .'uploads/tx_csvdisplay/'.$filename) or empty($filename)){
			return '<!-- ERROR: No file Selected for Display! -->';
		}
		$data = file( PATH_site .'uploads/tx_csvdisplay/'.$filename);
		$data_line = 0;
		$delemiter = $this->find_separator($data[0]);
		switch($this->cObj->data['tx_csvdisplay_firstdatarow']){
			case 0: // as Data
				break;
			case 1: // as Header
				$header = $this->split_in_rows($data[0], $delemiter);
				$data_line++;
				break;
			case 2: // remove
				// simply getting the first line to move the filepointer forward
				$data_line++;
				break;
		}
		
		// reading the Data
		
		// Outputting the Table
		$content .= '<table class="tx_csvdisplay_table">';
		
		//reading header
		if (count($header)>0){
			$content .= '<tr class="tx_csvdisplay_row_head">';
			for($i=0;$i<count($header);$i++){
				$content .= '<th>' . $header[$i] .'</th>';
			}
			$content .= '</tr>'."\n";
		
		}
		
		// Converting data setting
		$charsetConv = $this->cObj->data['tx_csvdisplay_charsetconv'];
		$charsetConv = explode('|',$charsetConv);
		
		if (is_array($charsetConv)){
			if(count($charsetConv)==2){
				$charsetConv[0] = trim($charsetConv[0]);
				$charsetConv[1] = trim($charsetConv[1]);
			}else{
				$charsetConv = false;
			}
		}else{
			$charsetConv = false;
		}
		/* Going thru the rows */
		$end= count($data);
		for($i=$data_line;$i<$end;$i++){
			$evenodd = ($i % 2)==0 ? '_even' : '_odd';
			//$content .= '<tr class="tx_csvdisplay_row'.(($i % 2)+1).' tx_csvdisplay_rowcount'.$i.'">';
			$content .= '<tr class="tx_csvdisplay_row'. $evenodd .' tx_csvdisplay_row'.$i.'">';
			
			/* Going thru the data cells*/
			$data_line = $this->split_in_rows($data[$i], $delemiter);
			for($j=0;$j<count($data_line);$j++){
				$cell = $data_line[$j];
				
				if($charsetConv != false){
					//$content .= '<!-- convertetd -->';
					
						if(function_exists('iconv')){
							$cell=iconv($charsetConv[0],$charsetConv[1],$cell);
						}else{
							// fallback converting for Europe with and UTF-8
							
							if( ($charsetConv[0]=='ISO-8859-1' or $charsetConv[0]=='ISO-8859-15') and $charsetConv[1]=='UTF-8'){
								$cell=utf8_encode($cell);
							}
							if($charsetConv[0]=='UTF-8' and ($charsetConv[0]=='ISO-8859-1' or $charsetConv[1]=='ISO-8859-15')){
								$cell=utf8_decode($cell);
							}
							
						}
				}
				// automatic link Generation
				if($this->cObj->data['tx_csvdisplay_autolink']==1 and $conf['cellWrap.'][$j .'.']['disableAutolink']!=true){
					$cell = $this->autolink($cell,$conf);
				}
				
				/* special Wrap definief for this cell */
				if($conf['cellWrap.'][$j]==true){
					/* special wraps look like this: data {td:1}, {td:3}*/
					$specialWrapData = $conf['cellWrap.'][$j .'.']['data'];
					for($swd=0;$swd<count($data_line);$swd++){
						$specialWrapData = str_replace('{td:'.$swd.'}',$data_line[$swd],$specialWrapData);
						$specialWrapData = str_replace('{td.URLEncode:'.$swd.'}',urlencode($data_line[$swd]),$specialWrapData);
						$specialWrapData = str_replace('{td.HtmlEncode:'.$swd.'}',htmlentities($data_line[$swd]),$specialWrapData);
					}
					$cell=$specialWrapData;
					
				}
					
				if(empty($cell)){$cell = '&nbsp;';}
				$evenodd = ($i % 2)==0 ? '_even' : '_odd';
				//$content .= '<td class="tx_csvdisplay_td'.(($j % 2)+1) .' tx_csvdisplay_td_count'.$j.'">'. $cell  .'</td>';
				$content .= '<td class="tx_csvdisplay_td'.$j.'">'. $cell  .'</td>';
			}
			$content .= '</tr>
			';
		}
		$content .= '</table>';
		
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	
	
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The Text for the autolink
	 * @param	array		$conf: The PlugIn configuration
	 * @return	the text with added links
	 */
	function autolink($content,$conf){
	$words=explode(' ',$content);
	$content ='';
	foreach($words as $word){
		if(strpos(strtolower($word),'http://')===0){
			$word = $this->cObj-> getTypoLink($word,$word .' _blank');
		}elseif (strpos(strtolower($word),'www.')===0){
			$word = $this->cObj-> getTypoLink($word, 'http://'.$word .' _blank');
		}elseif (strpos(strtolower($word),'ftp://')===0){
			$word = $this->cObj-> getTypoLink($word, 'ftp://'.$word .' _blank');
		}elseif (strpos(strtolower($word),'@')>0){
			$word = $this->cObj-> getTypoLink($word, $word );
		}
		$content .= $word .' ';
	}
		return $content;
	}
	
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/csvdisplay/pi1/class.tx_csvdisplay_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/csvdisplay/pi1/class.tx_csvdisplay_pi1.php']);
}

?>