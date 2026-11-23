<?php

namespace App\Http\Controllers\Graduation;

use Afaya\EdgeTTS\Config\Constants;
use Afaya\EdgeTTS\Service\EdgeTTS;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Kiswahili TTS for lists.
 * Girl → Edge Zuri / Rehema. Boy → Edge Daudi / Rafiki.
 * If Edge is blocked (cPanel), Google Swahili + pitch shift so girl ≠ boy.
 */
class VoiceTtsController extends Controller
{
    private const VOICES_GIRL = ['sw-KE-ZuriNeural', 'sw-TZ-RehemaNeural'];

    private const VOICES_BOY = ['sw-TZ-DaudiNeural', 'sw-KE-RafikiNeural'];

    public function __invoke(Request $request)
    {
        $raw = $request->query('q', $request->query('text', ''));
        if ($raw === null) {
            $raw = '';
        }
        $raw = trim((string) $raw);
        $raw = preg_replace('/\s+/u', ' ', $raw) ?? '';
        $text = $this->prepareSwahiliText($raw);
        if ($text === '' && $raw !== '') {
            $text = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $raw) ?? '';
            $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        }

        $gender = strtolower((string) $request->query('g', 'female'));
        $voicePin = strtolower((string) $request->query('voice', ''));
        $speed = strtolower((string) $request->query('s', 'normal'));

        // Pin must not force the wrong gender: only honour daudi/rafiki for male, zuri/rehema for female.
        if (in_array($gender, ['male', 'boy', 'mwanaume', 'm'], true) || in_array($voicePin, ['daudi', 'rafiki', 'boy'], true)) {
            $gender = 'male';
            $voices = self::VOICES_BOY;
        } else {
            $gender = 'female';
            $voices = self::VOICES_GIRL;
        }

        if (! in_array($speed, ['slow', 'normal', 'fast'], true)) {
            $speed = 'normal';
        }

        if ($text === '') {
            return response(
                "Missing text. Use: /voice/tts?q=Karibu&g=female&voice=zuri&s=normal",
                400,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        if (mb_strlen($text) > 180) {
            return response('Text too long (max 180 characters).', 422, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $cacheDir = storage_path('app/graduation/tts-cache');
        try {
            File::ensureDirectoryExists($cacheDir);
        } catch (\Throwable $e) {
            Log::error('TTS cache dir failed', ['message' => $e->getMessage()]);
            abort(503, 'TTS cache unavailable');
        }

        // v8 busts old google-v1 caches that sounded the same for girl and boy.
        $edgeCache = $cacheDir.DIRECTORY_SEPARATOR.hash('sha256', 'edge-v8|'.$gender.'|'.$speed.'|'.$text).'.mp3';
        $googleCache = $cacheDir.DIRECTORY_SEPARATOR.hash('sha256', 'google-v2|'.$gender.'|'.$speed.'|'.$text).'.mp3';
        $edgeMeta = $edgeCache.'.voice';

        if (is_file($edgeCache) && filesize($edgeCache) > 0) {
            $used = is_file($edgeMeta) ? trim((string) file_get_contents($edgeMeta)) : $voices[0];

            return $this->audioResponse($edgeCache, $used, $gender, $speed, 'edge');
        }
        if (is_file($googleCache) && filesize($googleCache) > 0) {
            return $this->audioResponse($googleCache, 'google-sw-'.$gender, $gender, $speed, 'google');
        }

        $lock = $this->acquireLock($cacheDir);
        $binary = null;
        $source = null;
        $usedVoice = $voices[0];

        try {
            if (is_file($edgeCache) && filesize($edgeCache) > 0) {
                $used = is_file($edgeMeta) ? trim((string) file_get_contents($edgeMeta)) : $voices[0];

                return $this->audioResponse($edgeCache, $used, $gender, $speed, 'edge');
            }
            if (is_file($googleCache) && filesize($googleCache) > 0) {
                return $this->audioResponse($googleCache, 'google-sw-'.$gender, $gender, $speed, 'google');
            }

            // 1) Real girl/boy Edge voices (try primary then alternate).
            foreach ($voices as $voice) {
                $binary = $this->synthesizeEdge($text, $voice, $gender, $speed);
                if ($binary !== null && $binary !== '') {
                    $source = 'edge';
                    $usedVoice = $voice;
                    break;
                }
            }

            // 2) Google fallback — pitch-shift so girl sounds higher than boy.
            if ($binary === null || $binary === '') {
                $binary = $this->synthesizeGoogle($text, $speed);
                if ($binary !== null && $binary !== '') {
                    $binary = $this->shapeGoogleGender($binary, $gender);
                    $source = 'google';
                    $usedVoice = 'google-sw-'.$gender;
                }
            }
        } finally {
            $this->releaseLock($lock);
        }

        if ($binary === null || $binary === '') {
            Log::error('TTS unavailable after Edge + Google', [
                'text' => $text,
                'gender' => $gender,
            ]);
            abort(503, 'TTS unavailable');
        }

        if ($source === 'edge') {
            @file_put_contents($edgeCache, $binary);
            @file_put_contents($edgeMeta, $usedVoice);
        } else {
            @file_put_contents($googleCache, $binary);
        }

        return response($binary, 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=604800',
            'X-SG-Voice' => $usedVoice,
            'X-SG-Gender' => $gender,
            'X-SG-Speed' => $speed,
            'X-SG-Source' => $source ?? 'unknown',
        ]);
    }

    /**
     * @param  string  $path
     */
    protected function audioResponse(string $path, string $voice, string $gender, string $speed, string $source)
    {
        return response()->file($path, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=604800',
            'X-SG-Voice' => $voice,
            'X-SG-Gender' => $gender,
            'X-SG-Speed' => $speed,
            'X-SG-Source' => $source,
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
        $deadline = microtime(true) + 8;
        while (! flock($fh, LOCK_EX | LOCK_NB)) {
            if (microtime(true) >= $deadline) {
                fclose($fh);

                return null;
            }
            usleep(80000);
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

    protected function edgeRate(string $speed): string
    {
        return match ($speed) {
            'slow' => '-20%',
            'fast' => '+12%',
            default => '-5%',
        };
    }

    protected function synthesizeEdge(string $text, string $voice, string $gender, string $speed): ?string
    {
        if (! class_exists(EdgeTTS::class)) {
            Log::error('afaya/edge-tts not installed — run composer install on server');

            return null;
        }

        try {
            $tts = new EdgeTTS(['timeout' => 12]);
            $result = $tts->generate($text, $voice, [
                'volume' => '+100%',
                'rate' => $this->edgeRate($speed),
                // Stronger female pitch so Girl never reads as Boy.
                'pitch' => $gender === 'female' ? '+18Hz' : '-2Hz',
                'outputFormat' => Constants::OUTPUT_FORMAT['AUDIO_24KHZ_48KBITRATE_MONO_MP3'],
                'timeout' => 12,
            ]);

            return $result->audio !== '' ? $result->audio : null;
        } catch (\Throwable $e) {
            Log::warning('Edge TTS failed', [
                'voice' => $voice,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function synthesizeGoogle(string $text, string $speed): ?string
    {
        $ttsspeed = match ($speed) {
            'slow' => '0.5',
            'fast' => '1',
            default => '0.8',
        };

        $chunks = $this->chunkText($text, 160);
        $binary = '';

        foreach ($chunks as $index => $chunk) {
            $url = 'https://translate.google.com/translate_tts?'.http_build_query([
                'ie' => 'UTF-8',
                'client' => 'gtx',
                'tl' => 'sw',
                'ttsspeed' => $ttsspeed,
                'q' => $chunk,
                'textlen' => (string) mb_strlen($chunk),
                'idx' => (string) $index,
                'total' => (string) count($chunks),
            ]);

            try {
                $response = Http::timeout(12)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => '*/*',
                        'Accept-Language' => 'sw,en;q=0.8',
                        'Referer' => 'https://translate.google.com/',
                    ])
                    ->get($url);
            } catch (\Throwable $e) {
                Log::warning('Google TTS failed', ['message' => $e->getMessage()]);

                return null;
            }

            if (! $response->successful() || $response->body() === '') {
                Log::warning('Google TTS bad response', ['status' => $response->status()]);

                return null;
            }

            $binary .= $response->body();
        }

        return $binary !== '' ? $binary : null;
    }

    /**
     * Google has one Swahili voice — raise pitch for girl, lower for boy when ffmpeg exists.
     */
    protected function shapeGoogleGender(string $binary, string $gender): string
    {
        $factor = $gender === 'female' ? 1.18 : 0.88;
        $shaped = $this->ffmpegPitch($binary, $factor);

        return $shaped ?? $binary;
    }

    protected function ffmpegPitch(string $binary, float $factor): ?string
    {
        if (abs($factor - 1.0) < 0.01) {
            return $binary;
        }

        $ffmpeg = $this->findFfmpeg();
        if ($ffmpeg === null) {
            return null;
        }

        $in = tempnam(sys_get_temp_dir(), 'tts');
        $out = $in.'.mp3';
        if ($in === false || @file_put_contents($in, $binary) === false) {
            return null;
        }

        // Pitch change with tempo compensation.
        $tempo = 1 / $factor;
        $af = sprintf('asetrate=24000*%F,aresample=24000,atempo=%F', $factor, $tempo);
        $cmd = sprintf(
            '%s -y -i %s -af %s -f mp3 %s 2>&1',
            escapeshellcmd($ffmpeg),
            escapeshellarg($in),
            escapeshellarg($af),
            escapeshellarg($out)
        );

        @exec($cmd, $output, $code);
        @unlink($in);

        if ($code !== 0 || ! is_file($out) || filesize($out) < 64) {
            @unlink($out);
            Log::warning('ffmpeg pitch failed', ['code' => $code, 'out' => implode("\n", $output ?? [])]);

            return null;
        }

        $data = file_get_contents($out) ?: null;
        @unlink($out);

        return $data !== null && $data !== '' ? $data : null;
    }

    protected function findFfmpeg(): ?string
    {
        foreach (['ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg'] as $bin) {
            $path = trim((string) @shell_exec('command -v '.escapeshellarg($bin).' 2>/dev/null'));
            if ($path !== '' && is_executable($path)) {
                return $path;
            }
            if (is_executable($bin)) {
                return $bin;
            }
        }

        return null;
    }

    protected function prepareSwahiliText(string $text): string
    {
        return Str::of($text)
            ->replace(['.', ',', ';', ':', '/', '\\', '_', '(', ')', '[', ']', '{', '}'], ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
    }

    /**
     * @return list<string>
     */
    protected function chunkText(string $text, int $max): array
    {
        if (mb_strlen($text) <= $max) {
            return [$text];
        }

        $parts = preg_split('/\s+/u', $text) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($parts as $part) {
            $next = $current === '' ? $part : $current.' '.$part;
            if (mb_strlen($next) > $max && $current !== '') {
                $chunks[] = $current;
                $current = $part;
            } else {
                $current = $next;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks ?: [$text];
    }
}
