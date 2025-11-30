<?php

namespace Codewithkyrian\ChromaDB\Tests;

use Symfony\Component\Process\Process;

class ChromaServer
{
    private static ?Process $process = null;

    public static function start(int $port = 8000): void
    {
        if (self::$process !== null && self::$process->isRunning()) {
            return;
        }

        if (self::isPortInUse($port)) {
            echo "Port $port is already in use. Assuming Chroma is running externally.\n";
            return;
        }

        $command = ['chroma', 'run', '--port', (string)$port, '--path', '.chroma'];
        
        self::$process = new Process($command, env: [
            'IS_PERSISTENT' => false,
            'ALLOW_RESET' => true
        ]);

        self::$process->start();

        $retries = 20;
        while ($retries > 0) {
            if (self::isPortInUse($port)) {
                return;
            }
            usleep(500000); // 0.5 seconds
            $retries--;
        }

        throw new \RuntimeException("Failed to start Chroma server on port $port.");
    }

    public static function stop(): void
    {
        if (self::$process !== null && self::$process->isRunning()) {
            self::$process->stop();
            self::$process = null;
        }
    }

    private static function isPortInUse(int $port): bool
    {
        $connection = @fsockopen('localhost', $port);

        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }

        return false;
    }

}
