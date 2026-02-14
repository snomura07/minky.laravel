<?php

namespace App\Actions;

use Illuminate\Support\Facades\Http;
use Throwable;

class DiscodeAction
{
    public function sendMessage(string $message): bool
    {
        $webhookUrl = $this->resolveWebhookUrl();
        if ($webhookUrl === null) {
            return false;
        }

        try {
            Http::timeout(10)->post($webhookUrl, [
                'content' => $message,
            ])->throw();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function resolveWebhookUrl(): ?string
    {
        $url = (string) config('services.discord.webhook_url');
        if ($url !== '') {
            return $url;
        }

        $id = (string) config('services.discord.discode_id');
        $token = (string) config('services.discord.discode_token');
        if ($id === '' || $token === '') {
            return null;
        }

        return "https://discord.com/api/webhooks/{$id}/{$token}";
    }
}
