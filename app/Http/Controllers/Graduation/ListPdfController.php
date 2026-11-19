<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;
use App\Services\ListPdfCacheService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves Graduands / Best Students PDF downloads from disk cache.
 *
 * PDFs are generated once per filter set + student data version, then reused
 * for near-instant subsequent downloads.
 */
class ListPdfController extends Controller
{
    public function __construct(
        protected ListPdfCacheService $pdfCache,
    ) {}

    /**
     * Export the graduands list as PDF (cached).
     */
    public function graduands(Request $request, GraduationData $data): BinaryFileResponse
    {
        return $this->pdfCache->download(ListPdfCacheService::TYPE_GRADUANDS, $request, $data);
    }

    /**
     * Export the best-students list as PDF (cached).
     */
    public function bestStudents(Request $request, GraduationData $data): BinaryFileResponse
    {
        return $this->pdfCache->download(ListPdfCacheService::TYPE_BEST_STUDENTS, $request, $data);
    }
}
