<?php

declare(strict_types=1);

namespace Tempest\Support\VarExport;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use Tempest\Log\LogConfig;

final readonly class Debug
{
    public function __construct(private LogConfig $logConfig)
    {
    }

    public function log(mixed ...$input): void
    {
        $handle = fopen($this->logConfig->debugLogPath, 'a');

        $cloner = new VarCloner();

        foreach ($input as $key => $item) {
            $output = '';

            $dumper = new CliDumper(function ($line, $depth) use (&$output) {
                if ($depth < 0) {
                    return;
                }

                $output .= str_repeat(' ', $depth) . $line . "\n";
            });

            $dumper->setColors(true);

            $dumper->dump($cloner->cloneVar($item));

            fwrite($handle, "[{$key}]" . PHP_EOL . $output . PHP_EOL);
        }

        fclose($handle);

        VarDumper::dump($input);

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        fwrite(STDOUT, PHP_EOL . "Called in " . $trace[1]['file'] . ':' . $trace[1]['line'] . PHP_EOL);
    }
}