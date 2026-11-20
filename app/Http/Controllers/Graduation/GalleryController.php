<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

/**
 * Graduation gallery / memories page and upload endpoint.
 */
class GalleryController extends Controller
{
    public function index(GraduationData $data)
    {
        $groups = $data->galleryByProgramme();

        return view('SmartGradu_App.gallery', [
            'groups' => $groups,
            'programmes' => $data->programmeOptions(),
            'filterProgrammes' => $groups->pluck('programme')->values(),
        ]);
    }

    /**
     * Store a new memory: programme + up to 3 images.
     * Same programme uploads are merged when viewing the gallery.
     */
    public function store(Request $request, GraduationData $data)
    {
        $validated = $request->validate([
            'programme' => ['required', 'string', 'max:40'],
            'caption' => ['nullable', 'string', 'max:200'],
            'images' => ['required', 'array', 'min:1', 'max:3'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $option = $data->programmeOptions()
            ->first(fn ($row) => strcasecmp((string) $row->short, $validated['programme']) === 0);

        if (! $option) {
            return response()->json([
                'ok' => false,
                'message' => 'Please select a valid programme.',
            ], 422);
        }

        /** @var array<int, UploadedFile> $files */
        $files = array_slice(array_values($request->file('images', [])), 0, 3);

        $dir = public_path('SmartGradu_App/uploads/gallery');
        File::ensureDirectoryExists($dir);

        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->storeFittedImage($file, $dir);
        }

        $memory = $data->saveGalleryMemory([
            'programme' => (string) $option->short,
            'programme_name' => (string) $option->name,
            'caption' => trim((string) ($validated['caption'] ?? '')),
            'images' => $paths,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Memory added.',
                'memory' => $memory,
            ]);
        }

        return redirect()
            ->route('gallery')
            ->with('status', 'Memory added.');
    }

    /**
     * Force-download a single gallery photo.
     */
    public function download(Request $request): BinaryFileResponse
    {
        $relative = ltrim(str_replace('\\', '/', (string) $request->query('file', '')), '/');

        if ($relative === '' || str_contains($relative, '..') || ! str_starts_with($relative, 'SmartGradu_App/uploads/gallery/')) {
            abort(404);
        }

        $path = public_path($relative);
        if (! is_file($path)) {
            abort(404);
        }

        return response()->download($path, basename($path), [
            'Content-Type' => 'image/jpeg',
        ]);
    }

    /**
     * Download all photos for a programme as a ZIP.
     */
    public function downloadProgramme(string $programme, GraduationData $data)
    {
        $group = $data->galleryByProgramme()
            ->first(fn ($row) => strcasecmp((string) $row->programme, $programme) === 0);

        if (! $group || empty($group->images)) {
            abort(404);
        }

        if (! class_exists(ZipArchive::class)) {
            return redirect()->to(asset($group->images[0]));
        }

        $zipName = Str::slug($group->programme).'-gallery.zip';
        $zipPath = storage_path('app/temp/'.$zipName);
        File::ensureDirectoryExists(dirname($zipPath));

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create download.');
        }

        foreach ($group->images as $index => $relative) {
            $full = public_path($relative);
            if (is_file($full)) {
                $zip->addFile($full, sprintf('%s-%02d%s', $group->programme, $index + 1, '.'.pathinfo($full, PATHINFO_EXTENSION)));
            }
        }
        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Save an uploaded image resized to fit gallery cards (max 1280px, JPEG).
     */
    protected function storeFittedImage(UploadedFile $file, string $dir): string
    {
        $name = Str::uuid()->toString().'.jpg';
        $dest = $dir.DIRECTORY_SEPARATOR.$name;

        if (! function_exists('imagecreatefromstring')) {
            $file->move($dir, $name);

            return 'SmartGradu_App/uploads/gallery/'.$name;
        }

        $binary = (string) file_get_contents($file->getRealPath());
        $source = @imagecreatefromstring($binary);

        if ($source === false) {
            $fallback = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $fallback);

            return 'SmartGradu_App/uploads/gallery/'.$fallback;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $max = 1280;
        $scale = min(1, $max / max($srcW, $srcH));
        $dstW = max(1, (int) round($srcW * $scale));
        $dstH = max(1, (int) round($srcH * $scale));

        $canvas = imagecreatetruecolor($dstW, $dstH);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $dstW, $dstH, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagejpeg($canvas, $dest, 82);
        imagedestroy($source);
        imagedestroy($canvas);

        return 'SmartGradu_App/uploads/gallery/'.$name;
    }
}
