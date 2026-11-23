<?php

namespace App\Http\Controllers\Graduation;

use Afaya\EdgeTTS\Config\Constants;
use Afaya\EdgeTTS\Service\EdgeTTS;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Free Kiswahili TTS — one voice per choice (no mix).
 * Girl = Zuri only. Boy = Daudi only.
 */
class VoiceTtsController extends Controller
{
    private const VOICE_GIRL = 'sw-KE-ZuriNeural';

    private const VOICE_BOY = 'sw-TZ-DaudiNeural';

    public function __invoke(Request $request)
    {
        $text = trim((string) $request->query('q', ''));
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        $text = $this->prepareSwahiliText($text);

        $gender = strtolower((string) $request->query('g', 'female'));
        $voicePin = strtolower((string) $request->query('voice', ''));

        if (in_array($gender, ['male', 'boy', 'mwanaume', 'm'], true) || $voicePin === 'daudi') {
            $gender = 'male';
            $voice = self::VOICE_BOY;
        } else {
            $gender = 'female';
            $voice = self::VOICE_GIRL; // Zuri only — never Daudi / Rehema mix
        }

        if ($text === '' || mb_strlen($text) > 180) {
            abort(422, 'Invalid text');
        }

        $cacheDir = storage_path('app/graduation/tts-cache');
        File::ensureDirectoryExists($cacheDir);
        $cacheFile = $cacheDir.DIRECTORY_SEPARATOR.hash('sha256', 'edge-v6|'.$voice.'|'.$text).'.mp3';

        if (is_file($cacheFile) && filesize($cacheFile) > 0) {
            return response()->file($cacheFile, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'public, max-age=604800',
                'X-SG-Voice' => $voice,
                'X-SG-Gender' => $gender,
            ]);
        }

        $lock = $this->acquireLock($cacheDir);
        try {
            if (is_file($cacheFile) && filesize($cacheFile) > 0) {
                return response()->file($cacheFile, [
                    'Content-Type' => 'audio/mpeg',
                    'Cache-Control' => 'public, max-age=604800',
                    'X-SG-Voice' => $voice,
                    'X-SG-Gender' => $gender,
                ]);
            }

            $binary = $this->synthesizeEdge($text, $voice, $gender);
        } finally {
            $this->releaseLock($lock);
        }

        if ($binary === null || $binary === '') {
            abort(503, 'TTS busy — try again');
        }

        file_put_contents($cacheFile, $binary);

        return response($binary, 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=604800',
            'X-SG-Voice' => $voice,
            'X-SG-Gender' => $gender,
        ]);
    }

    /**
     * @return resource|null
     */
    protected function acquireLock(string $cacheDir)
    {
        $path = $cacheDir.DIRECTORY_SEPARATOR.'.tts.lock';
        $fh = @fopen($path, 'c+');
        if ($fh === false) {
            return null;
        }
        $deadline = microtime(true) + 6;
        while (! flock($fh, LOCK_EX | LOCK_NB)) {
            if (microtime(true) >= $deadline) {
                fclose($fh);

                return null;
            }
            usleep(50000);
        }

        return $fh;
    }

    /**
     * @param  resource|null  $fh
     */
    protected function releaseLock($fh): void
    {
        if (! is_resource($fh)) {
            return;
        }
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    protected function synthesizeEdge(string $text, string $voice, string $gender): ?string
    {
        try {
            $tts = new EdgeTTS(['timeout' => 14]);
            $result = $tts->generate($text, $voice, [
                'volume' => '+100%',
                'rate' => '+0%',
                'pitch' => $gender === 'female' ? '+20Hz' : '+0Hz',
                'outputFormat' => Constants::OUTPUT_FORMAT['AUDIO_24KHZ_48KBITRATE_MONO_MP3'],
                'timeout' => 14,
            ]);

            return $result->audio !== '' ? $result->audio : null;
        } catch (\Throwable $e) {
            Log::warning('Edge TTS failed', [
                'voice' => $voice,
                'gender' => $gender,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function prepareSwahiliText(string $text): string
    {
        return Str::of($text)
            ->replace(['.', ',', ';', ':', '/', '\\', '_', '(', ')', '[', ']', '{', '}'], ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
    }
}
