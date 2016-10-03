<?php

// General information.
define('GAME_SERVER_ROOT', '/your/root/folder');
define('GAME_SERVER_LOG_FILE', GAME_SERVER_ROOT . '/log');
// NOTE: Public URL must be HTTPS.
define('GAME_SERVER_PUBLIC_URL', 'https://your.domain');

// Slack API information.
define('SLACK_OAUTH_URL', "https://slack.com/api/oauth.access");
define('SLACK_OAUTH_CLIENT_ID', "XXXXXXXXXX.XXXXXXXXXXX");
define('SLACK_OAUTH_CLIENT_SECRET', "XXXXXXXXXXXXXXXXXX");
define('SLACK_OAUTH_VERIFICATION', "XXXXXXXXXXXXXXXXXXX");
define('SLACK_BOT_USERNAME', 'LazyQuest');
define('SLACK_BOT_ICON', ':lazyquest:');

// Database information.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'XXXX');
define('DB_USER', 'XXXX');
define('DB_PASS', 'XXXX');

// Cache information.
define('GAME_CACHE_SERVER', 'localhost');
define('GAME_CACHE_PORT', 11211);