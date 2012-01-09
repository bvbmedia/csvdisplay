<?php

########################################################################
# Extension Manager/Repository config file for ext "csvdisplay".
#
# Auto generated 09-01-2012 14:48
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'CSV Display',
	'description' => 'Displays CSV Data In a HTML Table, options to generate Headers from first data row and display data in multiple pages',
	'category' => 'fe',
	'author' => 'Christian Wolff',
	'author_email' => 'chris@connye.com',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'createDirs' => '',
	'uploadfolder' => 1,
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.3.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:15:"CSV_Analyze.php";s:4:"88b7";s:9:"ChangeLog";s:4:"78f3";s:10:"README.txt";s:4:"bf57";s:21:"ext_conf_template.txt";s:4:"f374";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"01d5";s:14:"ext_tables.php";s:4:"e483";s:14:"ext_tables.sql";s:4:"8d4e";s:13:"locallang.xml";s:4:"9247";s:17:"locallang__db.xml";s:4:"c617";s:16:"locallang_db.xml";s:4:"a024";s:14:"doc/manual.sxw";s:4:"43eb";s:19:"doc/wizard_form.dat";s:4:"705c";s:20:"doc/wizard_form.html";s:4:"1408";s:31:"pi1/class.tx_csvdisplay_pi1.php";s:4:"049c";}',
	'suggests' => array(
	),
);

?>