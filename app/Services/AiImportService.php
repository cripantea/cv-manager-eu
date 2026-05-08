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
        $this->apiKey = config('services.anthropic.key');
        $this->model  = config('services.anthropic.model');;
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
                'Anthropic API error: ' . ($response->json('error.message') ?? $response->status())
            );
        }

        return $this->parseResponse($response->json());
    }

    public function importFromPdf(string $pdfPath): array
    {
        set_time_limit(180);

        $text = $this->extractTextFromPdf($pdfPath);

        if (empty(trim($text))) {
            throw new RuntimeException('Unable to extract text from the PDF. The file may be scanned or password-protected.');
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
            throw new RuntimeException('pdftotext is not available or an extraction error occurred.');
        }

        return $output;
    }

    private function buildPrompt(): string
    {
        return <<<'PROMPT'
Extract the work experiences from the following text and return ONLY a JSON array
with this structure for each experience:
[{
  "project_name": "",
  "employer": "company name",
  "client": "",
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD or null if still ongoing",
  "project_size": "S|M|L|XL",
  "description": "description of the project/role",
  "roles": ["role1", "role2"],
  "responsibilities": ["responsibility1", "responsibility2"],
  "technologies": ["tech1", "tech2"]
}]
Do not add any text before or after the JSON.
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
            throw new RuntimeException('Invalid AI response: unable to parse the JSON.');
        }

        return $decoded;
    }
}
