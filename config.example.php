<?php

// General configuration.
define('GAME_SERVER_ROOT', '/your/root/folder');
define('GAME_SERVER_LOG_FILE', GAME_SERVER_ROOT . '/log');
define('GAME_SERVER_PUBLIC_DIR', GAME_SERVER_ROOT . '/public');
// NOTE: Public URL must be HTTPS. Use LetsEncrypt to generate certificate if needed.
define('GAME_SERVER_PUBLIC_URL', 'https://your.domain');

// Slack API configuration.
define('SLACK_OAUTH_URL', "https://slack.com/api/oauth.access");
define('SLACK_OAUTH_CLIENT_ID', "XXXXXXXXXX.XXXXXXXXXXX");
define('SLACK_OAUTH_CLIENT_SECRET', "XXXXXXXXXXXXXXXXXX");
define('SLACK_OAUTH_VERIFICATION', "XXXXXXXXXXXXXXXXXXX");
define('SLACK_BOT_USERNAME', 'LazyQuest');
define('SLACK_BOT_ICON', ':lazyquest:');

// Database configuration.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'XXXX');
define('DB_USER', 'XXXX');
define('DB_PASS', 'XXXX');

// Cache configuration.
define('GAME_CACHE_SERVER', 'localhost');
define('GAME_CACHE_PORT', 11211);

// Map configuration.
define('GAME_MAP_IMAGE_PATH', '/images/map.png'); // NOTE: Relative to the public directory
define('GAME_MAP_ICONS_DIR', '/icons');
define('GAME_MAP_FONT_PATH', '/fonts/RobotoMono-Regular.ttf');

// Spritesheet configuration.
define('GAME_SPRITESHEET_DEBUG_DIR', GAME_SERVER_PUBLIC_DIR . '/debug');
define('GAME_SPRITESHEET_DIR', '/sprites');
define('GAME_SPRITESHEET_RAW_JSON', GAME_SPRITESHEET_DIR . '/sprite-raw-information.json');
define('GAME_SPRITESHEET_RAW_DIR', GAME_SPRITESHEET_DIR . '/raw');
define('GAME_SPRITESHEET_ALTERED_DIR', GAME_SPRITESHEET_DIR . '/altered');
define('GAME_SPRITESHEET_FILENAME', GAME_SPRITESHEET_DIR . '/sprites.png');
define('GAME_SPRITESHEET_JSON', GAME_SPRITESHEET_DIR . '/sprites.json');