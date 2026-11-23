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
 * Prefer Edge neural voices (Zuri girl / Daudi boy).
 * Fall back to Google Translate TTS when Edge/WebSocket is blocked (common on cPanel).
 */
class VoiceTtsController extends Controller
{
    private const VOICE_GIRL = 'sw-KE-ZuriNeural';

    private const VOICE_BOY = 'sw-TZ-DaudiNeural';

    public function __invoke(Request $request)
    {
        // App sends ?q=Name. Opening /voice/tts alone (no q) is not a valid play request.
        $raw = $request->query('q', $request->query('text', ''));
        if ($raw === null) {
            $raw = '';
        }
        $raw = trim((string) $raw);
        $raw = preg_replace('/\s+/u', ' ', $raw) ?? '';
        $text = $this->prepareSwahiliText($raw);
        // If cleanup removed everything but letters existed, keep a safe raw form.
        if ($text === '' && $raw !== '') {
            $text = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $raw) ?? '';
            $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        }

        $gender = strtolower((string) $request->query('g', 'female'));
        $voicePin = strtolower((string) $request->query('voice', ''));
        $speed = strtolower((string) $request->query('s', 'normal'));

        if (in_array($gender, ['male', 'boy', 'mwanaume', 'm'], true) || $voicePin === 'daudi') {
            $gender = 'male';
            $voice = self::VOICE_BOY;
        } else {
            $gender = 'female';
            $voice = self::VOICE_GIRL;
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

        // Prefer Edge cache; also accept Google fallback cache so replays work offline from Edge.
        $edgeCache = $cacheDir.DIRECTORY_SEPARATOR.hash('sha256', 'edge-v7|'.$voice.'|'.$speed.'|'.$text).'.mp3';
        $googleCache = $cacheDir.DIRECTORY_SEPARATOR.hash('sha256', 'google-v1|'.$gender.'|'.$speed.'|'.$text).'.mp3';

        foreach ([$edgeCache => 'edge', $googleCache => 'google'] as $cached => $src) {
            if (is_file($cached) && filesize($cached) > 0) {
                return response()->file($cached, [
                    'Content-Type' => 'audio/mpeg',
                    'Cache-Control' => 'public, max-age=604800',
                    'X-SG-Voice' => $src === 'google' ? 'google-sw' : $voice,
                    'X-SG-Gender' => $gender,
                    'X-SG-Speed' => $speed,
                    'X-SG-Source' => $src,
                ]);
            }
        }

        $lock = $this->acquireLock($cacheDir);
        $binary = null;
        $source = null;
        $usedVoice = $voice;

        try {
            foreach ([$edgeCache => 'edge', $googleCache => 'google'] as $cached => $src) {
                if (is_file($cached) && filesize($cached) > 0) {
                    return response()->file($cached, [
                        'Content-Type' => 'audio/mpeg',
                        'Cache-Control' => 'public, max-age=604800',
                        'X-SG-Voice' => $src === 'google' ? 'google-sw' : $voice,
                        'X-SG-Gender' => $gender,
                        'X-SG-Speed' => $speed,
                        'X-SG-Source' => $src,
                    ]);
                }
            }

            // 1) Edge neural (best quality / real girl-boy). Often blocked on shared hosting.
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $binary = $this->synthesizeEdge($text, $voice, $gender, $speed);
                if ($binary !== null && $binary !== '') {
                    $source = 'edge';
                    $usedVoice = $voice;
                    break;
                }
                usleep(200000 * $attempt);
            }

            // 2) Google Translate TTS — HTTPS GET, works on most cPanel hosts.
            if ($binary === null || $binary === '') {
                $binary = $this->synthesizeGoogle($text, $speed);
                if ($binary !== null && $binary !== '') {
                    $source = 'google';
                    $usedVoice = 'google-sw';
                }
            }
        } finally {
            $this->releaseLock($lock);
        }

        if ($binary === null || $binary === '') {
            Log::error('TTS unavailable after Edge + Google', [
                'text' => $text,
                'gender' => $gender,
                'voice' => $voice,
            ]);
            abort(503, 'TTS unavailable');
        }

        $cacheFile = $source === 'google' ? $googleCache : $edgeCache;
        @file_put_contents($cacheFile, $binary);

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
                'pitch' => $gender === 'female' ? '+8Hz' : '+0Hz',
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

    /**
     * Free HTTPS fallback — single Swahili voice, but keeps the site working on cPanel.
     */
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
