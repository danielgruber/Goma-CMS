<?php
SQL::Query("UPDATE " . DB_PREFIX . "imageuploads SET thumbleft = 50, thumbtop = 50");
SQL::Query("UPDATE " . DB_PREFIX . "uploads SET deletable = 0");
SQL::Query("DELETE FROM " . DB_PREFIX . "uploads WHERE type = 'collection'");