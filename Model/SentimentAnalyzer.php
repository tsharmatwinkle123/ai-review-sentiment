<?php
namespace Twinkle\ReviewSentiment\Model;

class SentimentAnalyzer
{
    const HUGGING_FACE_API_KEY = '';
    public function analyze(string $text): string
    {
        $ch = curl_init('https://api-inference.huggingface.co/models/distilbert-base-uncased-finetuned-sst-2-english');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer YOUR_HUGGINGFACE_API_KEY',
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode(['inputs' => $text])
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        return $result[0][0]['label'] ?? 'unknown';
    }
}
