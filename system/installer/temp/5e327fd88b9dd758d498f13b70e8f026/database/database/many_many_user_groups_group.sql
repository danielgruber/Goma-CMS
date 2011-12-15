-- Table many_many_user_groups_group

DROP TABLE IF EXISTS {!#PREFIX}many_many_user_groups_group;
  -- Create 
CREATE TABLE {!#PREFIX}many_many_user_groups_group (
 `id` int(10) NOT NULL auto_increment,
 `userid` int(10) NOT NULL,
 `groupid` int(10) NOT NULL,
PRIMARY KEY (id),
UNIQUE `dataindexunique` (userid, groupid),
KEY `dataindex` (userid, groupid));

; 





