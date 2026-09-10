<?php

return [
    /*
     * Directory containing the mysqldump/mysql binaries, if they aren't on
     * PATH. Left empty relies on PATH (the normal case on most hosting).
     * The old ZentaraStartProject module hardcoded a Windows/OSPanel-specific
     * fallback path here instead — not portable to any other environment.
     */
    'mysql_bin_path' => env('BACKUP_MYSQL_BIN_PATH', ''),

    /*
     * Directory containing the pg_dump/psql binaries, if they aren't on PATH.
     */
    'pgsql_bin_path' => env('BACKUP_PGSQL_BIN_PATH', ''),
];
