-- Table boxes

DROP TABLE IF EXISTS {!#PREFIX}boxes;
  -- Create 
CREATE TABLE {!#PREFIX}boxes (
 `id` int(10) NOT NULL auto_increment,
 `autorid` int(10) NOT NULL,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('box','login_meinaccount','phpbox','statistics','boxes') NOT NULL,
 `created` int(90) NOT NULL,
 `seiteid` varchar(50) NOT NULL,
 `border` enum('0','1') NOT NULL,
 `title` varchar(100) NOT NULL,
 `sort` int(3) NOT NULL,
 `text` text NOT NULL,
 `width` varchar(5) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `autorid` (autorid),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `_show` (seiteid, sort),
KEY `searchable_fields` (text(166), title),
KEY `editorid` (editorid),
KEY `recordid` (recordid));

-- INSERT 
 INSERT INTO {!#PREFIX}boxes (`id` , `autorid` , `last_modified` , `class_name` , `created` , `seiteid` , `border` , `title` , `sort` , `text` , `width` , `editorid` , `recordid` , `snap_priority`) VALUES  ( '13','1','1320014384','login_meinaccount','1320012114','sidebar','0','Login/Mein Account','2','','200','1','2','2' )
, ( '9','1','1320012227','box','1320012186','4','0','','2','<h1>\\n\\n	Impressum</h1>\\n\\n<p>\\n\\n	Bitte f&uuml;gen Sie hier Ihr Impressum ein.</p>\\n\\n','630','1','3','1' )
, ( '10','1','1320012231','box','1320011996','1','0','','2','<h1>\\n\\n	Ihre neue Goma-Seite</h1>\\n\\n<p>\\n\\n	Willkommen auf Ihrer neuen Goma-Seite. Bitte melden Sie sich im linken Bereich an oder klicken Sie auf <a href=\"./admin/\">Administration</a>, um direkt in die Administration zu gelangen.</p>\\n\\n','630','1','1','2' )
; 





