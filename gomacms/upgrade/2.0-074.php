<?php defined("IN_GOMA") OR die();
SQL::Query("ALTER TABLE " . DB_PREFIX . "pages change search include_in_search int(1) NOT NULL DEFAULT '1'");