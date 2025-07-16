<?php
namespace Database;

class DBPersistaroo
{
    private \Config $config;
    private string $backupDir;

    public function __construct(\Config $config)
    {
        $this->config = $config;
        // Store backups in a directory outside the web root for security
        $this->backupDir = $this->config->app_path . '/db_backups';

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Checks if a backup has been made in the last hour and creates one if not.
     */
    public function ensureBackupIsRecent(): void
    {
        $latestBackupTime = $this->getLatestBackupTime();

        // Check if the latest backup is older than 1 hour (3600 seconds)
        if ((time() - $latestBackupTime) > 3600) {
            $this->createBackup();
        }
    }

    /**
     * Creates a new database backup using mysqldump.
     * Runs the process in the background to avoid blocking page loads.
     */
    private function createBackup(): void
    {
        $user = escapeshellarg($this->config->dbUser);
        $pass = escapeshellarg($this->config->dbPass);
        $host = escapeshellarg($this->config->dbHost);
        $name = escapeshellarg($this->config->dbName);

        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = escapeshellarg("{$this->backupDir}/db_backup_{$timestamp}.sql");
        $logFile = escapeshellarg("{$this->backupDir}/backup.log");

        // Build the command
        $command = "mysqldump --user={$user} --password={$pass} --host={$host} {$name} > {$backupFile}";

        // Execute and capture any output/errors to a log file for debugging.
        // NOTE: This is now a synchronous operation and will cause the page to hang.
        $output = shell_exec($command . " 2>&1");
        file_put_contents($logFile, "Timestamp: " . date('Y-m-d H:i:s') . "\n");
        file_put_contents($logFile, "Command Output:\n" . $output . "\n", FILE_APPEND);

        // After attempting the backup, prune old ones.
        $this->pruneOldBackups();
    }

    /**
     * Deletes old backups based on the retention policy.
     * - Keep a minimum of 5 backups.
     * - Delete backups older than 1 month, but only if more than 5 exist.
     */
    private function pruneOldBackups(): void
    {
        $backups = glob($this->backupDir . '/*.sql');
        if (empty($backups)) {
            return;
        }

        // Sort backups by modification time (oldest first)
        usort($backups, fn($a, $b) => filemtime($a) - filemtime($b));

        $totalBackups = count($backups);
        $oneMonthAgo = strtotime('-1 month');

        foreach ($backups as $backup) {
            if ($totalBackups <= 5) {
                // Don't delete if we have 5 or fewer backups
                break;
            }

            if (filemtime($backup) < $oneMonthAgo) {
                unlink($backup);
                $totalBackups--;
            }
        }
    }

    /**
     * Finds the timestamp of the most recent backup file.
     *
     * @return int Timestamp of the latest backup, or 0 if none exist.
     */
    public function getLatestBackupTime(): int
    {
        $backups = glob($this->backupDir . '/*.sql');
        if (empty($backups)) {
            return 0;
        }

        $latestTime = 0;
        foreach ($backups as $backup) {
            $time = filemtime($backup);
            if ($time > $latestTime) {
                $latestTime = $time;
            }
        }
        return $latestTime;
    }

    /**
     * Returns the number of existing backup files.
     *
     * @return int
     */
    public function getBackupCount(): int
    {
        $backups = glob($this->backupDir . '/*.sql');
        return count($backups);
    }
}
