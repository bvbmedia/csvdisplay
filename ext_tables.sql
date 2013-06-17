#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
        tx_csvdisplay_colheader tinytext NOT NULL,
        tx_csvdisplay_firstdatarow int(11) DEFAULT '0' NOT NULL,
        tx_csvdisplay_csvfile blob NOT NULL,
        tx_csvdisplay_pageitems int(11) DEFAULT '0' NOT NULL
        tx_csvdisplay_charsetconv varchar(255) DEFAULT '0' NOT NULL
        tx_csvdisplay_delimiter varchar(1) DEFAULT '0' NOT NULL
        tx_csvdisplay_autolink tinyint(3) DEFAULT '0' NOT NULL
        tx_csvdisplay_layout varchar(20) DEFAULT '0' NOT NULL 
);