-- Table advrights_state

DROP TABLE IF EXISTS {!#PREFIX}advrights_state;
  -- Create 
CREATE TABLE {!#PREFIX}advrights_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

-- INSERT 
 INSERT INTO {!#PREFIX}advrights_state (`id` , `stateid` , `publishedid`) VALUES  ( '1','1','1' )
, ( '2','2','2' )
, ( '3','3','3' )
, ( '4','4','4' )
, ( '5','5','5' )
, ( '6','6','6' )
, ( '7','7','7' )
, ( '8','8','8' )
, ( '9','9','9' )
, ( '10','10','10' )
, ( '11','11','11' )
, ( '12','12','12' )
, ( '13','13','13' )
, ( '14','14','14' )
, ( '15','15','15' )
; 





