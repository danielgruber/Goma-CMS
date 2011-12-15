-- Table pages

DROP TABLE IF EXISTS {!#PREFIX}pages;
  -- Create 
CREATE TABLE {!#PREFIX}pages (
 `id` int(10) NOT NULL auto_increment,
 `autorid` int(10) NOT NULL,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('page','boxpage','articlecategory','article','articles','contact','errorpage','members','phppage','redirector','pages') NOT NULL,
 `created` int(90) NOT NULL,
 `parentid` int(10) NOT NULL,
 `path` varchar(500) NOT NULL,
 `rights` int(2) NOT NULL,
 `mainbar` int(1) NOT NULL DEFAULT '1',
 `mainbartitle` varchar(200) NOT NULL,
 `rating` int(1) NOT NULL,
 `title` varchar(200) NOT NULL,
 `data` text NOT NULL,
 `sort` int(8) NOT NULL DEFAULT '10000',
 `search` int(1) NOT NULL DEFAULT '1',
 `editright` text NOT NULL,
 `showcomments` int(1) NOT NULL,
 `meta_description` varchar(200) NOT NULL,
 `meta_keywords` varchar(200) NOT NULL,
 `edit_type` varchar(10) NOT NULL,
 `readpassword` varchar(50) NOT NULL,
 `viewer_type` varchar(20) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `mainbar` (parentid, mainbar, class_name),
KEY `parentid` (parentid),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `path` (path(111), sort, class_name),
KEY `sitesearch` (class_name, data(55), title(55), mainbartitle(55), meta_keywords(55), id),
KEY `searchable_fields` (data(83), title(83), mainbartitle(83), meta_keywords(83)),
KEY `autorid` (autorid),
KEY `editorid` (editorid),
KEY `recordid` (recordid));

-- INSERT 
 INSERT INTO {!#PREFIX}pages (`id` , `autorid` , `last_modified` , `class_name` , `created` , `parentid` , `path` , `rights` , `mainbar` , `mainbartitle` , `rating` , `title` , `data` , `sort` , `search` , `editright` , `showcomments` , `meta_description` , `meta_keywords` , `edit_type` , `readpassword` , `viewer_type` , `editorid` , `recordid` , `snap_priority`) VALUES  ( '1','1','1320012475','boxpage','1320011884','0','startseite','1','1','Startseite','0','Startseite','','0','1','','0','','','all','','all','1','1','2' )
, ( '2','1','1320012475','errorpage','1320012085','0','seite-nicht-gefunden','1','1','Seite nicht gefunden','0','Seite nicht gefunden','<h3>\\n\\n	Ups! Ihre Seite konnte auch nach langer Suche leider nicht gefunden werden :(</h3>\\n\\n','5','1','','0','','','all','','all','1','2','2' )
, ( '3','1','1320012475','errorpage','1320012085','0','seite-nicht-gefunden','1','0','Seite nicht gefunden','0','Seite nicht gefunden','<h3>\\n\\n	Ups! Ihre Seite konnte auch nach langer Suche leider nicht gefunden werden :(</h3>\\n\\n','5','0','','0','','','all','','all','1','2','2' )
, ( '4','1','1320012475','contact','1320012159','0','kontaktformular','1','0','Kontaktformular','0','Kontaktformular','','3','1','','0','','','all','','all','1','3','2' )
, ( '5','1','1320012475','boxpage','1320012176','0','impressum','1','0','Impressum','0','Impressum','','4','1','','0','','','all','','all','1','4','2' )
, ( '6','1','1320012475','articlecategory','1320012252','0','artikel','1','1','Artikel','0','Artikel','','1','1','','0','','','all','','all','1','5','2' )
, ( '7','1','1320012401','article','1320012401','5','der-erste-artikel','1','1','Der Erste Artikel','0','Der Erste Artikel','<p>\\n\\n	Dies ist das Artikelsystem von Goma. Um einen neuen Artikel anzulegen, melden Sie sich in der Administration an. Dort w&auml;hlen Sie in der rechten Spalte die Kategorie Inhalt. Im rechten Bereich sehen Sie eine Box Erstellen, dort w&auml;hlen Sie Artikel aus und dr&uuml;cken auf Erstellen. Nun f&uuml;hrt Sie Goma durch die Erstellung automatisch hindurch.</p>\\n\\n','10000','1','','1','','','all','','all','1','6','2' )
, ( '8','1','1320012475','members','1320012473','0','mitglieder','1','1','Mitglieder','0','Mitglieder','','2','1','','0','','','all','','all','1','7','2' )
, ( '9','1','1320012784','contact','1320012159','0','kontakt','1','0','Kontaktformular','0','Kontaktformular','','3','1','','0','','','all','','all','1','3','2' )
, ( '10','1','1320013312','contact','1320012159','0','kontakt','1','0','Kontaktformular','0','Kontaktformular','','3','1','','0','','','all','','all','1','3','1' )
, ( '11','1','1320013316','contact','1320012159','0','kontakt','1','0','Kontaktformular','0','Kontaktformular','','3','1','','0','','','all','','all','1','3','2' )
, ( '12','1','1320013428','contact','1320012159','0','kontakt','1','0','Kontaktformular','0','Kontaktformular','','3','1','','0','','','all','','all','1','3','1' )
, ( '13','1','1320013431','contact','1320012159','0','kontakt','1','0','Kontaktformular','0','Kontaktformular','','3','1','','0','','','all','','all','1','3','2' )
, ( '14','1','1320013439','contact','1320012159','0','kontakt','1','0','Kontaktformular','0','Kontaktformular','','3','1','','0','','','all','','all','1','3','2' )
; 





