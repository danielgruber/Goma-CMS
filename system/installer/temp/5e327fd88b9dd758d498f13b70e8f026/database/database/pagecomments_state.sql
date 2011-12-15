-- Table pagecomments_state

DROP TABLE IF EXISTS {!#PREFIX}pagecomments_state;
  -- Create 
CREATE TABLE {!#PREFIX}pagecomments_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

; 





