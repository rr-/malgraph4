<?php
require_once __DIR__ . '/../src/core.php';

touch(Config::$userQueuePath);
touch(Config::$userQueueSizesPath);
touch(Config::$mediaQueuePath);
touch(Config::$bannedUsersListPath);
WebMediaHelper::download();
