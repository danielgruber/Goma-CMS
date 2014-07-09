<?php
SQL::Query("UPDATE " . DB_PREFIX . "users SET nickname = LOWER(nickname)");