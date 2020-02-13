#!/usr/bin/php
<?php
ini_set('display_errors', 1);

$config = [
    'directories' => [
        '/etc',
        '/home',
        '/var/www',
    ],
    'mysql' => [
        'user' => 'root',
        'password' => 'secret',
    ],
    'pgsql' => [
        'user' => 'postgres',
    ],
    'destination' => [
        'local' => '/var/archives',
        'remotes' => [
            'dedibackup:/tmp',
        ],
    ],
    'email' => 'spam@gmail.com',
    'ttl' => 10,
];

function logLine($msg)
{
    $date = date('Y-m-d H:i:s');
    echo "[{$date}] {$msg}" . PHP_EOL;
}

function byteconvert($bytes)
{
    $symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $exp = floor(log($bytes) / log(1024));
    return sprintf('%.2f ' . $symbol[$exp], ($bytes / pow(1024, floor($exp))));
}

$hostname = gethostname();
$now = new \DateTimeImmutable();
$timestampFormat = 'Ymd\THis';
$timestamp = $now->format($timestampFormat);
$prefix = "{$hostname}-{$timestamp}";

if (!empty($config['directories'])) {
    logLine("Starting directories backup");

    foreach ($config['directories'] as $directory) {
        logLine("Archiving directory '{$directory}'");

        $directoryClean = str_replace('/', '-', trim($directory, '/'));
        $filename = "{$prefix}-files-{$directoryClean}.tar.gz";
        $path = "{$config['destination']['local']}/{$filename}";
        $directory = ltrim($directory, '/'); // Removing leading `/' from member names

        exec("cd / && tar -zcvf {$path} {$directory}");
    }
}


if (!empty($config['mysql'])) {
    logLine("Starting MySQL backup");

    exec("mysql --user={$config['mysql']['user']} --password={$config['mysql']['password']} -e 'show databases' --batch --skip-column-names", $databases, $return);

    foreach ($databases as $database) {
        logLine("Dumping MySQL database '{$database}'");

        $filename = "{$prefix}-mysql-{$database}.sql.gz";
        $path = "{$config['destination']['local']}/{$filename}";

        exec("mysqldump --user={$config['mysql']['user']} --password={$config['mysql']['password']} --quick --lock-tables=false {$database} | gzip > {$path}");
    }
}

if (!empty($config['pgsql'])) {
    logLine("Starting PostgreSQL backup");

    $filename = "{$prefix}-pgsql-all.sql.gz";
    $path = "{$config['destination']['local']}/{$filename}";

    exec("pg_dumpall --user={$config['pgsql']['user']} | gzip > {$path}");
}

logLine("Deleting old backups");

foreach (glob("{$config['destination']['local']}/*") as $filename) {
    if (preg_match('/^(.*)-(?P<date>\d{8})T(?P<time>\d{6})-(.*)$/', basename($filename), $matches)) {
        $filenameDate = \DateTimeImmutable::createFromFormat('Ymd', $matches['date']);

        if ($now > $filenameDate->modify("+{$config['ttl']} days")) {
            logLine("Deleting {$filename}");
            unlink($filename);
        }
    }
}

if (!empty($config['destination']['remotes'])) {
    foreach ($config['destination']['remotes'] as $remote) {
        logLine("Rclone sync to '{$remote}'");

        exec("rclone sync {$config['destination']['local']} {$remote}");
    }
}

logLine("Sending mail");

$message = [];
$totalSize = [];

foreach (glob("{$config['destination']['local']}/*") as $filename) {
    $basename = basename($filename);
    $size = filesize($filename);
    $totalSize[] = $size;
    $message[] = sprintf('%s (%s)', $basename, byteconvert($size));
}

$message[] = '';
$message[] = sprintf('Total: %s', byteconvert(array_sum($totalSize)));

mail($config['email'], "[$hostname] Backup OK", implode("\n", $message));

logLine("Finished");
