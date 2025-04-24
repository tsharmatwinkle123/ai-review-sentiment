<?php
namespace Twinkle\ReviewSentiment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ModelList implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'distilbert-base-uncased-finetuned-sst-2-english', 'label' => __('DistilBERT (SST-2 English)')],
            ['value' => 'cardiffnlp/twitter-roberta-base-sentiment', 'label' => __('Twitter RoBERTa Sentiment')],
            ['value' => 'siebert/sentiment-roberta-large-english', 'label' => __('Large RoBERTa English')],
            ['value' => 'nlptown/bert-base-multilingual-uncased-sentiment', 'label' => __('Multilingual BERT Sentiment')],
        ];
    }
}
