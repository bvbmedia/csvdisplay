<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_csvdisplay_colheader" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_colheader",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_csvdisplay_firstdatarow" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_firstdatarow",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_firstdatarow.I.0", "0"),
				Array("LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_firstdatarow.I.1", "1"),
				Array("LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_firstdatarow.I.2", "2"),
			),
			"size" => 1,	
			"maxitems" => 1,
		)
	),
	"tx_csvdisplay_csvfile" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_csvfile",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => "txt,csv",	
			"disallowed" => "",	
			"max_size" => 1000,	
			"uploadfolder" => "uploads/tx_csvdisplay",
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_csvdisplay_pageitems" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_pageitems",        
        "config" => Array (
            "type" => "input",
            "size" => "4",
            "max" => "4",
            "eval" => "int",
            "checkbox" => "0",
            "range" => Array (
                "upper" => "1000",
                "lower" => "10"
            ),
            "default" => 0
        )
    ),
	"tx_csvdisplay_charsetconv" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_charsetconv",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_csvdisplay_autolink" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:csvdisplay/locallang_db.xml:tt_content.tx_csvdisplay_autolink",        
        "config" => Array (
            "type" => "check",
            "default" => 1,
        )
    ),
);

// tx_csvdisplay_charsetconv

t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types'][$_EXTKEY.'_pi1']['showitem']='CType;;4;button;1-1-1, header;;3;;2-2-2, tx_csvdisplay_colheader;;;;1-1-1, tx_csvdisplay_firstdatarow, tx_csvdisplay_csvfile, tx_csvdisplay_seperator,tx_csvdisplay_autolink,tx_csvdisplay_charsetconv'; //,tx_csvdisplay_pageitems
//$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
//$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='tx_csvdisplay_colheader;;;;1-1-1, tx_csvdisplay_firstdatarow, tx_csvdisplay_csvfile, tx_csvdisplay_seperator,tx_csvdisplay_pageitems';

//t3lib_extMgm::addPlugin(Array('LLL:EXT:csvdisplay/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPlugin(Array('LLL:EXT:csvdisplay/locallang_db.xml:tt_content.CType_pi1', $_EXTKEY.'_pi1'),'CType');
?>