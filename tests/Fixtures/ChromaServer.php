<?php

namespace Codewithkyrian\ChromaDB\Tests\Fixtures;

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

        self::$process = new Process(['chroma', 'run', 'tests/Fixtures/chroma.yaml']);

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
