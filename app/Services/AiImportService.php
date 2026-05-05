<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiImportService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = env('ANTHROPIC_API_KEY', '');
        $this->model  = env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001');
    }

    public function importFromText(string $text): array
    {
        $prompt = $this->buildPrompt();

        $response = Http::timeout(120)->withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => 4096,
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => $prompt . "\n\n" . $text,
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Errore API Anthropic: ' . ($response->json('error.message') ?? $response->status())
            );
        }

        return $this->parseResponse($response->json());
    }

    public function importFromPdf(string $pdfPath): array
    {
        set_time_limit(180);

        $text = $this->extractTextFromPdf($pdfPath);

        if (empty(trim($text))) {
            throw new RuntimeException('Impossibile estrarre testo dal PDF. Il file potrebbe essere scansionato o protetto.');
        }

        // Truncate to avoid sending too many tokens (~30k chars ≈ 7500 tokens)
        if (strlen($text) > 30000) {
            $text = substr($text, 0, 30000);
        }

        return $this->importFromText($text);
    }

    private function extractTextFromPdf(string $pdfPath): string
    {
        $escaped = escapeshellarg($pdfPath);
        $output  = shell_exec("pdftotext {$escaped} -");

        if ($output === null) {
            throw new RuntimeException('pdftotext non disponibile o errore durante l\'estrazione.');
        }

        return $output;
    }

    private function buildPrompt(): string
    {
        return <<<'PROMPT'
Estrai le esperienze lavorative dal seguente testo e restituisci SOLO un JSON array
con questa struttura per ogni esperienza:
[{
  "project_name": "",
  "employer": "nome azienda",
  "client": "",
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD o null se ancora in corso",
  "project_size": "S|M|L|XL",
  "description": "descrizione del progetto/ruolo",
  "roles": ["ruolo1", "ruolo2"],
  "responsibilities": ["responsabilità1", "responsabilità2"],
  "technologies": ["tech1", "tech2"]
}]
Non aggiungere nessun testo prima o dopo il JSON.
PROMPT;
    }

    private function parseResponse(array $responseBody): array
    {
        $rawText = $responseBody['content'][0]['text'] ?? '';

        // Strip potential markdown code fences
        $json = preg_replace('/^```(?:json)?\s*/i', '', trim($rawText));
        $json = preg_replace('/\s*```$/', '', $json);

        $decoded = json_decode(trim($json), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new RuntimeException('Risposta AI non valida: impossibile parsare il JSON.');
        }

        return $decoded;
    }
}
