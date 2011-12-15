-- Table many_many_group_advrights_advrights

DROP TABLE IF EXISTS {!#PREFIX}many_many_group_advrights_advrights;
  -- Create 
CREATE TABLE {!#PREFIX}many_many_group_advrights_advrights (
 `id` int(10) NOT NULL auto_increment,
 `groupid` int(10) NOT NULL,
 `advrightsid` int(10) NOT NULL,
PRIMARY KEY (id),
UNIQUE `dataindexunique` (groupid, advrightsid),
KEY `dataindex` (groupid, advrightsid));

-- INSERT 
 INSERT INTO {!#PREFIX}many_many_group_advrights_advrights (`id` , `groupid` , `advrightsid`) VALUES  ( '151','4','14' )
, ( '150','4','13' )
, ( '149','4','12' )
, ( '148','4','11' )
, ( '147','4','9' )
, ( '146','4','8' )
, ( '145','4','7' )
, ( '144','4','6' )
, ( '143','4','5' )
, ( '142','4','4' )
, ( '141','4','3' )
, ( '140','4','2' )
, ( '139','4','1' )
, ( '152','5','1' )
, ( '153','5','2' )
, ( '154','5','3' )
, ( '155','5','4' )
, ( '156','5','5' )
, ( '157','5','6' )
, ( '158','5','7' )
, ( '159','5','8' )
, ( '160','5','9' )
, ( '161','5','10' )
, ( '162','5','11' )
, ( '163','5','12' )
, ( '164','5','14' )
, ( '165','5','15' )
; 





