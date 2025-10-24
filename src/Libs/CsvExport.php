<?php

declare(strict_types = 1);

namespace QuantumTecnology\HandlerBasicsExtension\Libs;

use App\Exceptions\InvalidCsvException;
use Carbon\Carbon;

use function count;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CsvExport
{
    protected $csvRaw;
    protected array $csv    = [];
    protected array $header = [];

    public function __construct(protected string $rowLimiter = "\n", protected string $collumLimiter = ';')
    {
    }

    public function getCollection(bool $header = true): Collection
    {
        return new Collection($this->load($header));
    }

    public function getCsv(bool $header = true): array
    {
        return $this->load($header);
    }

    public function getCsvRaw(): string
    {
        return $this->csvRaw;
    }

    public function setRestrictedHeader(array $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function importStream(string $stream): self
    {
        $this->csvRaw = str_replace(['﻿'], '', is_base64($stream) ? base64_decode($stream) : $stream);

        return $this;
    }

    public function importFile(string $path): self
    {
        $this->csvRaw = str_replace(['﻿'], '', file_get_contents($path));

        return $this;
    }

    public function export(): bool
    {
        if ([] === $this->csv) {
            return false;
        }

        foreach ($this->csv as $name => $csv) {
            if ('local' == config('app.env')) {
                $fp = fopen("{$name}.csv", 'w');

                foreach ($csv as $row) {
                    fputcsv(
                        $fp,
                        $row,
                        $this->collumLimiter,
                        $this->rowLimiter,
                    );
                }

                fclose($fp);
            } else {
                $output = fopen('php://memory', 'w');

                foreach ($csv as $row) {
                    fputcsv(
                        $output,
                        $row,
                        $this->collumLimiter,
                        $this->rowLimiter,
                    );
                }

                rewind($output);
                $this->saveS3("{$name}.csv", stream_get_contents($output));
                fclose($output);
            }
        }

        return true;
    }

    public function getStream(): string
    {
        $output = fopen('php://memory', 'w');

        fputcsv(
            $output,
            $this->header,
            $this->collumLimiter,
            $this->rowLimiter,
        );

        foreach ($this->csv as $row) {
            fputcsv(
                $output,
                $row,
                $this->collumLimiter,
                $this->rowLimiter,
            );
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    public function exportCsvRaw(string $name = 'file'): bool
    {
        if (!$this->csvRaw) {
            return false;
        }

        if ('local' != config('app.env')) {
            $storage = new Storage();
            $storage = $storage::disk('s3-files');

            return $storage->put(config('filesystems.disks.s3-files.path') . "{$name}.csv", $content ?? $this->csvRaw);
        }

        $fp = fopen(base_path("/storage/{$name}.csv"), 'w');
        fwrite($fp, (string) $this->csvRaw);
        fclose($fp);

        return true;
    }

    public function addCsv(Collection $collection): self
    {
        $this->csv = $collection->transform(function ($item) {
            return $item->resource->toArray();
        })->toArray();

        return $this;
    }

    public function guardCsv(array $row = [], string $name = 'file.csv', bool $withTimeStamp = true): self
    {
        $name = $withTimeStamp ? Carbon::now()->format('Y-m-d') . '_' . $name : $name;

        $this->csv[$name][] = $row;

        return $this;
    }

    public function saveS3($content = null, string $name = 'file', bool $withTimeStamp = false): bool
    {
        $name = $withTimeStamp ? Carbon::now()->format('Y-m-d') . '_' . $name : $name;

        $storage = new Storage();
        $storage = $storage::disk('s3-files');

        return $storage->put(config('filesystems.disks.s3-files.path') . "{$name}.csv", $content ?? $this->csvRaw);
    }

    private function load(bool $header): array
    {
        $csvRaw = str_getcsv((string) $this->csvRaw, $this->rowLimiter, escape: '\\');

        foreach ($csvRaw as &$row) {
            $this->csv[] = str_getcsv((string) $row, $this->collumLimiter, escape: '\\');
        }

        if ($header) {
            $this->checkHeader();

            foreach ($this->csv as $row) {
                $csvRow = [];

                foreach ($this->header as $index => $collum) {
                    $csvRow += [
                        mb_strtolower((string) $collum) => 'null' != $row[$index] ? $row[$index] : null,
                    ];
                }
                $csvFormated[] = $csvRow;
                unset($csvRow);
            }

            $this->csv = $csvFormated;
        }

        return $this->csv;
    }

    private function checkHeader(): void
    {
        $firstRowHeader = array_shift($this->csv);

        if (0 === count($this->header)) {
            $this->header = $firstRowHeader;

            return;
        }

        if (($result = array_diff($this->header, $firstRowHeader)) !== []) {
            throw new InvalidCsvException(trans('messages.csv.error.invalid_header'), 400, null, $result);
        }

        if (count($this->header) !== count($firstRowHeader)) {
            throw new InvalidCsvException(trans('messages.csv.error.less_collum_expected'), 400);
        }

        $this->header = $firstRowHeader;

    }
}
