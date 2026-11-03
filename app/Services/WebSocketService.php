<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebSocketService
{
    /**
     * Broadcast an event and payload to connected WebSocket clients.
     *
     * @param string $event
     * @param array $data
     * @return bool
     */
    public static function broadcast(string $event, array $data = []): bool
    {
        try {
            $response = Http::timeout(0.8)->post('http://127.0.0.1:8080/publish', [
                'event'     => $event,
                'data'      => $data,
                'timestamp' => now()->toIso8601String(),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            // Fails silently if the WebSocket daemon is offline, app remains fully functional
            Log::debug('WebSocketService broadcast unreachable: ' . $e->getMessage());
            return false;
        }
    }
}

