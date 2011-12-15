-- Table rating_state

DROP TABLE IF EXISTS {!#PREFIX}rating_state;
  -- Create 
CREATE TABLE {!#PREFIX}rating_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

; 





