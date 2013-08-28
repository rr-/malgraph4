<?php
require_once 'src/core.php';

touch(Config::$userQueuePath);
touch(Config::$bannedUsersListPath);
WebMediaHelper::download();
