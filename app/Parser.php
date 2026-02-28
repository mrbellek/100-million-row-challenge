<?php

namespace App;

use RuntimeException;

final class Parser
{
    private const string DOMAIN = 'https://stitcher.io';
    private const string EOL = "\n";
    private array $output = [];

    public function parse(string $inputPath, string $outputPath): void
    {
        $handle = fopen($inputPath, 'r');
        while ($line = fgetcsv($handle, null, ',', '"', '\\')) {
            if (count($line) !== 2) {
                throw new RuntimeException('invalid data! ' . implode(',', $line));
            }

            [$url, $timestamp] = $line;
            $timestampDate = substr($timestamp,0 , strpos($timestamp, 'T'));
            if (isset($this->output[$url][$timestampDate])) {
                $this->output[$url][$timestampDate]++;
            } else {
                $this->output[$url][$timestampDate] = 1;
            }
        }
        fclose($handle);

        $this->writeOutput($outputPath);
    }

    private function writeOutput(string $outputPath): void
    {
        // Open file for writing
        $handle = fopen($outputPath, 'w');

        // Begin url line block
        fwrite($handle, '{' . self::EOL);

        $j = 0;
        $urlCount = count($this->output);
        foreach ($this->output as $url => $timestamps) {
            // Format url and write
            $urlLine = str_replace([self::DOMAIN, '/'], ['', '\/'], $url);
            $urlLine = '    "' . $urlLine . '": {';
            fwrite($handle, $urlLine . self::EOL);

            // Sort timestamps
            ksort($timestamps);
            $i = 0;
            $timestampCount = count($timestamps);
            foreach ($timestamps as $timestamp => $count) {
                // Format timestamp lines and write
                if (++$i === $timestampCount) {
                    $timestampLine = sprintf('        "%s": %d', $timestamp, $count);
                } else {
                    $timestampLine = sprintf('        "%s": %d,', $timestamp, $count);
                }
                fwrite($handle, $timestampLine . self::EOL);
            }

            // Close timestamps line block
            if (++$j === $urlCount) {
                fwrite($handle, '    }' . self::EOL);
            } else {
                fwrite($handle, '    },' . self::EOL);
            }
        }

        // Close url line block
        fwrite($handle, '}');
        fclose($handle);
    }
}