<?php
SQL::Query("ALTER TABLE " . DB_PREFIX . "permission change oinheritorid parentid int(10)");