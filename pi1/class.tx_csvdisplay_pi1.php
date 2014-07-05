<?php
/***************************************************************
 *  Copyright notice
 *  (c) 2007 Christian Wolff <chris@connye.com>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Plugin 'Display CSV data' for the 'csvdisplay' extension.
 * @author    Christian Wolff <chris@connye.com>
 */
require_once(PATH_tslib.'class.tslib_pibase.php');
class tx_csvdisplay_pi1 extends tslib_pibase {
	var $prefixId='tx_csvdisplay_pi1'; // Same as class name
	var $scriptRelPath='pi1/class.tx_csvdisplay_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey='csvdisplay'; // The extension key.
	var $pi_checkCHash=false;
	var $csvData=false;
	/**
	 * The main method of the PlugIn
	 * @param    string $content : The PlugIn content
	 * @param    array $conf : The PlugIn configuration
	 * @return    The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_USER_INT_obj=0; // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->uploadFolder='uploads/tx_csvdisplay/';
		global $TSFE, $LOCAL_LANG;
		if (is_array($conf['overwrite.'])) {
			$this->cObj->data=$data_array=t3lib_div::array_merge($this->cObj->data, $conf['overwrite.']);
		}
		$header=array();
		if (strlen($this->cObj->data['tx_csvdisplay_colheader'])>0) {
			$header=explode('|', $this->cObj->data['tx_csvdisplay_colheader']);
		}
		$layout='';
		if (strlen(trim($this->cObj->data['tx_csvdisplay_layout']))>0) {
			$layout=trim($this->cObj->data['tx_csvdisplay_layout']);
			if (preg_match('/^[A-Za-z0-9-_]+$/', $layout)) { // Make shure we get only Valid ascii characters in class name
				$layout=' tx_csvdisplay_table_layout_'.$layout;
			} else {
				$layout=' tx_csvdisplay_table_layout_LAYOUT_NAME_CONTAINS_ILLIGEAL_CHARACTERS';
			}
		}
		$filename=$this->cObj->data['tx_csvdisplay_csvfile'];
		if (strpos($filename, 'fileadmin/')===0) { // the file is a reference we dont neet the upload folder path
			$this->uploadFolder='';
		}
		$fullPathFile=PATH_site.$this->uploadFolder.$filename;
		if (!is_readable($fullPathFile) or empty($filename)) {
			return '<!-- ERROR: No file Selected for Display! -->';
		}
		// setting the delmiter
		if (strlen($this->cObj->data['tx_csvdisplay_delimiter'])>0) {
			$delemiter=$this->cObj->data['tx_csvdisplay_delimiter'];
		} else {
			$fileStat=$this->analyse_file($fullPathFile);
			$delemiter=$fileStat['delimiter']['value'];
		}
		// Fixing a bug with macintosh line endings
		$tmp=ini_get('auto_detect_line_endings');
		ini_set('auto_detect_line_endings', true);
		$this->csvData=file($fullPathFile);
		ini_set('auto_detect_line_endings', $tmp);
		$CS=t3lib_div::makeInstance('t3lib_cs');
		// Converting Charset if needet
		$charsetConv=$this->cObj->data['tx_csvdisplay_charsetconv'];
		$charsetConv=explode('|', $charsetConv);
		if (is_array($charsetConv)) {
			if (count($charsetConv)==2) {
				$charsetConv[0]=trim($charsetConv[0]);
				$charsetConv[1]=trim($charsetConv[1]);
				foreach ($this->data as $idx=>$line) {
					$this->data[$idx]=$CS->conv($line, $charsetConv[0], $charsetConv[1]);
				}
			}
		}
		switch ($this->cObj->data['tx_csvdisplay_firstdatarow']) {
			case 0: // as Data
				break;
			case 1: // as Header
				$header=$this->split_in_rows(array_shift($this->csvData), $delemiter);
				break;
			case 2: // remove
				// simply getting the first line to move the filepointer forward
				if (count($this->csvData)>0) {
					array_shift($this->csvData);
				}
				break;
		}
		// Outputting the Table
		$content.='<table class="tx_csvdisplay_table'.$layout.'">';
		//reading header
		if (count($header)>0) {
			$content.='<tr class="tx_csvdisplay_row_head">';
			for ($i=0; $i<count($header); $i++) {
				$content.='<th class="tx_csvdisplay_th'.$i.'">'.$header[$i].'</th>';
			}
			$content.='</tr>'."\n";
		}
		// Rows
		$i=0;
		while ($data_line=array_shift($this->csvData)) {
			$i++;
			$evenodd=($i%2)==0 ? '_even' : '_odd';
			$content.='<tr class="tx_csvdisplay_row'.$evenodd.' tx_csvdisplay_row'.$i.'">';
			$data_line=$this->split_in_rows($data_line, $delemiter);
			for ($j=0; $j<count($data_line); $j++) {
				$cell=$data_line[$j];
				// automatic link Generation
				if ($this->cObj->data['tx_csvdisplay_autolink']==1 and $conf['cellWrap.'][$j.'.']['disableAutolink']!=true) {
					$cell=$this->autolink($cell, $conf);
				}
				/* special Wrap definief for this cell */
				if ($conf['cellWrap.'][$j]==true) {
					/* special wraps look like this: data {td:1}, {td:3}*/
					$specialWrapData=$conf['cellWrap.'][$j.'.']['data'];
					for ($swd=0; $swd<count($data_line); $swd++) {
						$specialWrapData=str_replace('{td:'.$swd.'}', $data_line[$swd], $specialWrapData);
						$specialWrapData=str_replace('{td.URLEncode:'.$swd.'}', urlencode($data_line[$swd]), $specialWrapData);
						$specialWrapData=str_replace('{td.HtmlEncode:'.$swd.'}', htmlentities($data_line[$swd]), $specialWrapData);
					}
					$cell=$specialWrapData;
				}
				if (empty($cell)) {
					$cell='&nbsp;';
				}
				$evenodd=($i%2)==0 ? '_even' : '_odd';
				//$content .= '<td class="tx_csvdisplay_td'.(($j % 2)+1) .' tx_csvdisplay_td_count'.$j.'">'. $cell  .'</td>';
				$content.='<td class="tx_csvdisplay_td'.$j.'">'.$cell.'</td>';
			}
			$content.='</tr>
			';
		}
		$content.='</table>';
		return $this->pi_wrapInBaseClass($content);
	}
	/**
	 * The main method of the PlugIn
	 * @param        string $content : The Text for the autolink
	 * @param        array $conf : The PlugIn configuration
	 * @return        the text with added links
	 */
	function autolink($content, $conf) {
		$words=explode(' ', $content);
		$content='';
		foreach ($words as $word) {
			if (strpos(strtolower($word), 'http://')===0) {
				$word=$this->cObj->getTypoLink($word, $word.' _blank');
			} elseif (strpos(strtolower($word), 'www.')===0) {
				$word=$this->cObj->getTypoLink($word, 'http://'.$word.' _blank');
			} elseif (strpos(strtolower($word), 'ftp://')===0) {
				$word=$this->cObj->getTypoLink($word, 'ftp://'.$word.' _blank');
			} elseif (strpos(strtolower($word), '@')>0) {
				$word=$this->cObj->getTypoLink($word, $word);
			}
			$content.=$word.' ';
		}
		return $content;
	}
	function analyse_file($file, $capture_limit_in_kb=10) {
		// log the limit how much of the file was sampled (in Kb)
		$output['read_kb']=$capture_limit_in_kb;
		// read in file
		$fh=fopen($file, 'r');
		$contents=fread($fh, ($capture_limit_in_kb*1024)); // in KB
		fclose($fh);
		// specify allowed field delimiters
		$delimiters=array(
			'comma'=>',',
			'semicolon'=>';',
			'tab'=>"\t",
			'pipe'=>'|',
			'colon'=>':'
		);
		// specify allowed line endings
		$line_endings=array(
			'rn'=>"\r\n",
			'n'=>"\n",
			'r'=>"\r",
			'nr'=>"\n\r"
		);
		// loop and count each line ending instance
		foreach ($line_endings as $key=>$value) {
			$line_result[$key]=substr_count($contents, $value);
		}
		// sort by largest array value
		asort($line_result);
		// log to output array
		$output['line_ending']['results']=$line_result;
		$output['line_ending']['count']=end($line_result);
		$output['line_ending']['key']=key($line_result);
		$output['line_ending']['value']=$line_endings[$output['line_ending']['key']];
		$lines=explode($output['line_ending']['value'], $contents);
		// remove last line of array, as this maybe incomplete?
		array_pop($lines);
		// create a string from the legal lines
		$complete_lines=implode(' ', $lines);
		// log statistics to output array
		$output['lines']['count']=count($lines);
		$output['lines']['length']=strlen($complete_lines);
		// loop and count each delimiter instance
		foreach ($delimiters as $delimiter_key=>$delimiter) {
			$delimiter_result[$delimiter_key]=substr_count($complete_lines, $delimiter);
		}
		// sort by largest array value
		asort($delimiter_result);
		// log statistics to output array with largest counts as the value
		$output['delimiter']['results']=$delimiter_result;
		$output['delimiter']['count']=end($delimiter_result);
		$output['delimiter']['key']=key($delimiter_result);
		$output['delimiter']['value']=$delimiters[$output['delimiter']['key']];
		return $output;
	}
	function split_in_rows($set, $separator=";") {
		if (!$set) {
			return false;
		}
		$squode=false;
		$quode=false;
		$bslash=false;
		$temp="";
		$laststart=0;
		for ($i=0; $i<=strlen($set); $i++) {
			switch (true) {
				case substr($set, $i, 1)=='\\':
					if ($bslash==false) {
						$bslash=$i;
					} //bslash setzen
					break;
				case substr($set, $i, 1)=='"':
					if ($quote && !$bslash) {
						$quote=false;
					} //quote setzen
					elseif (!$squote) {
						$quote=true;
					} //quote setzen
					break;
				case substr($set, $i, 1)=='\'':
					if ($squote && !$bslash) {
						$squote=false;
					} //quote setzen
					elseif (!$quote) {
						$squote=true;
					} //quote setzen
					break;
			}
			#wenn kein squote oder quote aktiv und kein standard zeichen
			if ($squote || $quote || substr($set, $i, 1)!=$separator) {
				continue;
			}
			$val=preg_replace("=^[\"']=", "", trim(substr($set, $laststart, $i-$laststart)));
			$temp[]=preg_replace("=[\"']$=", "", $val);
			$laststart=$i+1;
		}
		# am Ende Angekommen, noch den Rest anh�ngen
		$val=preg_replace("=^[\"']=", "", trim(substr($set, $laststart, strlen($set)-$laststart)));
		$temp[]=preg_replace("=[\"']$=", "", $val);
		return $temp;
		#return split ( $separator, $set);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/csvdisplay/pi1/class.tx_csvdisplay_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/csvdisplay/pi1/class.tx_csvdisplay_pi1.php']);
}

?>