<?php

namespace App\Console\Commands;

use App\Services\GraduationData;
use App\Services\ListPdfCacheService;
use Illuminate\Console\Command;

/**
 * Pre-generate full Graduands and Best Students PDFs for fast downloads.
 *
 * Usage:
 *   php artisan graduation:cache-pdfs
 *   php artisan graduation:cache-pdfs --clear
 */
class CacheGraduationPdfs extends Command
{
    protected $signature = 'graduation:cache-pdfs
                            {--clear : Delete existing PDF cache before warming}';

    protected $description = 'Pre-generate cached Graduands and Best Students PDF reports';

    public function handle(ListPdfCacheService $pdfCache, GraduationData $data): int
    {
        if ($this->option('clear')) {
            $pdfCache->clear();
            $this->info('Cleared PDF cache.');
        }

        $this->info('Warming full PDF reports…');
        $built = $pdfCache->warmFullReports($data);

        foreach ($built as $type => $path) {
            $size = is_file($path) ? number_format(filesize($path) / 1024, 1).' KB' : 'n/a';
            $this->line("  [{$type}] {$path} ({$size})");
        }

        $this->info('Done. Subsequent downloads will be served from cache.');

        return self::SUCCESS;
    }
}
