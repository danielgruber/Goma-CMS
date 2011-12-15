-- Table smilies

DROP TABLE IF EXISTS {!#PREFIX}smilies;
  -- Create 
CREATE TABLE {!#PREFIX}smilies (
 `id` int(10) NOT NULL auto_increment,
 `autorid` int(10) NOT NULL,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('smilies') NOT NULL,
 `image` varchar(200) NOT NULL,
 `description` varchar(200) NOT NULL,
 `code` varchar(200) NOT NULL,
 `created` int(90) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `autorid` (autorid),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `editorid` (editorid),
KEY `recordid` (recordid));

-- INSERT 
 INSERT INTO {!#PREFIX}smilies (`id` , `autorid` , `last_modified` , `class_name` , `image` , `description` , `code` , `created` , `editorid` , `recordid` , `snap_priority`) VALUES  ( '1','15','1308435474','smilies','mysite/uploaded/useruploads/0527433041grinsen.gif',':D',':D','1308435474','15','1','2' )
, ( '2','15','1277481040','smilies','mysite/uploaded/useruploads/1308406650auge.gif',';)',';)','0','15','2','2' )
, ( '3','15','1277486058','smilies','mysite/uploaded/useruploads/1372182154lachen.gif',':)',':)','0','15','3','2' )
, ( '4','15','1277486103','smilies','mysite/uploaded/useruploads/6267288927cool.gif',':cool:',':cool:','0','15','4','2' )
, ( '5','15','1277486115','smilies','mysite/uploaded/useruploads/9180382154lachen.gif',':-)',':-)','0','15','5','2' )
, ( '6','15','1277486125','smilies','mysite/uploaded/useruploads/3400206650auge.gif',';-)',';-)','0','15','6','2' )
, ( '7','15','1277486140','smilies','mysite/uploaded/useruploads/7959782319hoch.gif',':hoch:',':hoch:','0','15','7','2' )
, ( '8','15','1277486160','smilies','mysite/uploaded/useruploads/7310660502idee.gif',':idee:',':idee:','0','15','8','2' )
, ( '9','15','1277486277','smilies','mysite/uploaded/useruploads/5304942169frage.gif',':?:',':?:','0','15','9','2' )
; 





