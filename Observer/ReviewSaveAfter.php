<?php
namespace Twinkle\ReviewSentiment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class ReviewSaveAfter implements ObserverInterface
{
    protected $scopeConfig;
    protected $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $review = $observer->getEvent()->getObject();

        if (!$review || !$review->getId()) {
            return;
        }
        try {
            $apiKey = $this->scopeConfig->getValue('reviewsentiment/settings/api_key');
            $model = $this->scopeConfig->getValue('reviewsentiment/settings/model');

            $text = $review->getDetail();
            $sentiment = $this->analyzeSentiment($text, $apiKey, $model);

            //$review->setData('review_sentiment', $sentiment);
           // $review->save(); 

            $connection = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName('review');

            $connection->update(
                $tableName,
                ['review_sentiment' => $sentiment],
                ['review_id = ?' => (int)$review->getId()]
            );



        } catch (\Exception $e) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/reviw_log.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r($e->getMessage(),true));
        }
    }

    private function analyzeSentiment($text, $apiKey, $model)
    {
        $url = 'https://api-inference.huggingface.co/models/' . $model;
        $headers = [
            "Authorization: Bearer " . $apiKey,
            "Content-Type: application/json"
        ];
        $postData = json_encode(["inputs" => $text]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return 'NEUTRAL'; // fallback if error
        }
        curl_close($ch);

        $result = json_decode($response, true);
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/reviw_log.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
       
        if (!empty($result) && is_array($result)) {
            $best = $result[0][0];
            $logger->info(print_r($best,true));
            return strtoupper($best['label']);
        }

        return 'NEUTRAL'; // fallback
    }
}
