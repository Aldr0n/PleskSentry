<?php

return [
    'window_size'  => 400,
    'default_jail' => 'plesk-permanent-ban',

    'paths'        => [
        'banned_ips'           => 'bin/banned',
        'ips_to_ban'           => 'bin/to_ban',
        'trusted_ips'          => 'bin/trusted',
        'fetch_banned_script'  => 'src/Scripts/fetch_banned_ips',
        'fetch_trusted_script' => 'src/Scripts/fetch_trusted_ips',
        'ban_script'           => 'src/Scripts/process_banned_ips',
        'operations_log'       => 'logs/scan.log',
    ],
];