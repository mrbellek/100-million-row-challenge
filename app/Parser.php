<?php

namespace App;

final class Parser
{
    private array $output = [];

    public function parse(string $inputPath, string $outputPath): void
    {
        $handle = \fopen($inputPath, 'r');
        //@TODO try doing this with fscanf, might be faster?
        /*while ($lineParts = fscanf($handle, '%19s%99s,%10sT%14s')) {
            var_dump($lineParts);
            die();
        }*/
        while ($line = \fgets($handle)) {
            $url = \substr($line, 19, -27);
            $timestamp = \substr($line, -26, 10);

            if (isset($this->output[$url][$timestamp])) {
                $this->output[$url][$timestamp]++;
            } else {
                $this->output[$url][$timestamp] = 1;
            }
        }
        \fclose($handle);

        $this->writeOutput($outputPath);
    }

    private function writeOutput(string $outputPath): void
    {
        foreach ($this->output as $url => $timestamps) {
            \ksort($timestamps);
            $this->output[$url] = $timestamps;
        }

        \file_put_contents($outputPath, \json_encode($this->output, JSON_PRETTY_PRINT));
        unset($this->output);
    }
}