<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Config\Config;

class AISearchService {
    private $httpClient;
    private $apiKey;

    public function __construct() {
        $this->httpClient = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout'  => 30,
        ]);
        $this->apiKey = Config::get('OPENAI_API_KEY');
    }

    public function getParamsFromQuery(string $userQuery, array $context): array {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API key is not configured.');
        }

        $systemPrompt = $this->buildSystemPrompt($context);

        try {
            $response = $this->httpClient->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userQuery],
                    ],
                    'temperature' => 0.5,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            
            if (isset($body['choices'][0]['message']['content'])) {
                $jsonResponse = $body['choices'][0]['message']['content'];
                $decodedJson = json_decode($jsonResponse, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Failed to parse AI JSON response: ' . json_last_error_msg());
                }
                return $decodedJson;
            }

            throw new \Exception('Could not extract content from OpenAI response.');

        } catch (GuzzleException $e) {
            error_log('Guzzle Error: ' . $e->getMessage());
            throw new \Exception('Error communicating with AI service.');
        }
    }

    private function buildSystemPrompt(array $context): string {
        $cities = implode(', ', $context['cities'] ?? []);
        $neighborhoods = implode(', ', $context['neighborhoods'] ?? []);
        $htypes = implode(', ', $context['htypes'] ?? []);
        $styles = implode(', ', $context['styles'] ?? []);
        $amenities = implode(', ', $context['amenities'] ?? []);

        return <<<PROMPT
You are a highly intelligent assistant for a real estate website. Your task is to extract search parameters from a user's natural language query.
The user will provide a query, and you must return a JSON object with the extracted parameters.
The possible parameters are:
- "city": string (must be one of: {$cities})
- "neighborhood": string (must be one of: {$neighborhoods})
- "minRent": integer
- "maxRent": integer
- "htype": string (must be one of: {$htypes})
- "style": string (must be one of: {$styles})
- "furnished": boolean
- "verified": boolean
- "amenities": array of strings (must be one of: {$amenities})

Analyze the user's query and construct a valid JSON object. If a parameter is not mentioned, do not include it in the JSON object.
For example, if the user query is "a furnished 2 bedroom apartment in Kilimani with a gym for less than 50000", the JSON output should be:
{
    "htype": "TWO_BEDROOM",
    "neighborhood": "Kilimani",
    "furnished": true,
    "maxRent": 50000,
    "amenities": ["gym"]
}
Only output the JSON object, with no other text before or after it.
PROMPT;
    }
}
