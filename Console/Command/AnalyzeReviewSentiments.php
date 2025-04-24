<?php
namespace Twinkle\ReviewSentiment\Console\Command;

use Magento\Framework\App\State;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;

class AnalyzeReviewSentiments extends Command
{
    protected $reviewCollectionFactory;
    protected $scopeConfig;
    protected $state;
    protected $resourceConnection;

    public function __construct(
        ReviewCollectionFactory $reviewCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection,
        State $state
    ) {
        parent::__construct();
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->resourceConnection = $resourceConnection;
    }

    protected function configure()
    {
        $this->setName('reviewsentiment:analyze')
            ->setDescription('Analyze existing reviews for sentiment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('adminhtml');

        $apiKey = $this->scopeConfig->getValue('reviewsentiment/settings/api_key');
        $model = $this->scopeConfig->getValue('reviewsentiment/settings/model');

        $collection = $this->reviewCollectionFactory->create()
            ->addFieldToFilter('review_sentiment', ['null' => true])
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED);

        $count = 0;
        try {
            foreach ($collection as $review) {
                $text = $review->getDetail();
                $sentiment = $this->analyzeSentiment($text, $apiKey, $model);
                
              //  $review->setData('review_sentiment', $sentiment);
              //  $review->save();

                $connection = $this->resourceConnection->getConnection();
                $tableName = $connection->getTableName('review');
    
                $connection->update(
                    $tableName,
                    ['review_sentiment' => $sentiment],
                    ['review_id = ?' => (int)$review->getId()]
                );

                $count++;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln("Analyzed and updated $count reviews.");
        return Cli::RETURN_SUCCESS;
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
            return 'NEUTRAL'; // fallback
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (!empty($result) && is_array($result)) {
            $best = $result[0][0];
            if(!isset($best['label'])){
                return 'NEUTRAL';
            }

            return strtoupper($best['label']);
        }

        return 'NEUTRAL'; // fallback
    }
}
