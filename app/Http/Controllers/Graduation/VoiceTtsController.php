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
 * Google Translate TTS fallback when Edge/WebSocket is blocked (typical on cPanel).
 * No shell_exec/ffmpeg — those are often disabled and caused HTTP 500.
 */
class VoiceTtsController extends Controller
{
    private const VOICES_GIRL = ['sw-KE-ZuriNeural', 'sw-TZ-RehemaNeural'];

    private const VOICES_BOY = ['sw-TZ-DaudiNeural', 'sw-KE-RafikiNeural'];

    public function __invoke(Request $request)
    {
        try {
            return $this->handle($request);
        } catch (\Throwable $e) {
            Log::error('TTS crashed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile().':'.$e->getLine(),
            ]);

            return response('TTS error: '.$e->getMessage(), 503, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }
    }

    protected function handle(Request $request)
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
        File::ensureDirectoryExists($cacheDir);

        $edgeCache = $cacheDir.DIRECTORY_SEPARATOR.hash('sha256', 'edge-v8|'.$gender.'|'.$speed.'|'.$text).'.mp3';
        $googleCache = $cacheDir.DIRECTORY_SEPARATOR.hash('sha256', 'google-v3|'.$gender.'|'.$speed.'|'.$text).'.mp3';
        $edgeMeta = $edgeCache.'.voice';

        if (is_file($edgeCache) && filesize($edgeCache) > 0) {
            $used = is_file($edgeMeta) ? trim((string) file_get_contents($edgeMeta)) : $voices[0];

            return $this->audioFile($edgeCache, $used, $gender, $speed, 'edge');
        }
        if (is_file($googleCache) && filesize($googleCache) > 0) {
            return $this->audioFile($googleCache, 'google-sw-'.$gender, $gender, $speed, 'google');
        }

        $lock = $this->acquireLock($cacheDir);
        $binary = null;
        $source = null;
        $usedVoice = $voices[0];

        try {
            if (is_file($edgeCache) && filesize($edgeCache) > 0) {
                $used = is_file($edgeMeta) ? trim((string) file_get_contents($edgeMeta)) : $voices[0];

                return $this->audioFile($edgeCache, $used, $gender, $speed, 'edge');
            }
            if (is_file($googleCache) && filesize($googleCache) > 0) {
                return $this->audioFile($googleCache, 'google-sw-'.$gender, $gender, $speed, 'google');
            }

            foreach ($voices as $voice) {
                $binary = $this->synthesizeEdge($text, $voice, $gender, $speed);
                if ($binary !== null && $binary !== '') {
                    $source = 'edge';
                    $usedVoice = $voice;
                    break;
                }
            }

            if ($binary === null || $binary === '') {
                $binary = $this->synthesizeGoogle($text, $speed, $gender);
                if ($binary !== null && $binary !== '') {
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

            return response('TTS unavailable', 503, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
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

    protected function audioFile(string $path, string $voice, string $gender, string $speed, string $source)
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
            Log::warning('afaya/edge-tts missing — run composer install');

            return null;
        }

        try {
            $tts = new EdgeTTS(['timeout' => 12]);
            $result = $tts->generate($text, $voice, [
                'volume' => '+100%',
                'rate' => $this->edgeRate($speed),
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

    /**
     * Google Swahili TTS (single voice) — used only when Edge neural voices fail.
     */
    protected function synthesizeGoogle(string $text, string $speed, string $gender): ?string
    {
        $ttsspeed = match ($speed) {
            'slow' => '0.5',
            'fast' => '1',
            default => '0.8',
        };

        unset($gender); // reserved for future gendered fallback without shell tools

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
