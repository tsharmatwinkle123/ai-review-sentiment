<?php
namespace Twinkle\ReviewSentiment\Block;

use Magento\Framework\View\Element\Template;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Framework\App\RequestInterface;

class ReviewSentiment extends Template
{
    protected $reviewCollectionFactory;
    protected $request;

    public function __construct(
        Template\Context $context,
        ReviewCollectionFactory $reviewCollectionFactory,
        RequestInterface $request,
        array $data = []
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Get the overall sentiment of the product based on reviews
     * @return string
     */
    public function getOverallSentiment()
    {
        $productId = $this->request->getParam('id');
        
        $reviews = $this->reviewCollectionFactory->create()
            ->addFieldToFilter('entity_id', 1)
            ->addFieldToFilter('entity_pk_value', $productId)
            ->addFieldToFilter('status_id', 1)
            ->addFieldToFilter('review_sentiment', ['notnull' => true]);

        $sentimentScores = ['POSITIVE' => 0, 'NEGATIVE' => 0, 'NEUTRAL' => 0];

        foreach ($reviews as $review) {
            $sentiment = $review->getData('review_sentiment');
            if (isset($sentimentScores[$sentiment])) {
                $sentimentScores[$sentiment]++;
            }
        }

        // Calculate the overall sentiment
        if ($sentimentScores['POSITIVE'] > $sentimentScores['NEGATIVE']) {
            return 'POSITIVE';
        } elseif ($sentimentScores['NEGATIVE'] > $sentimentScores['POSITIVE']) {
            return 'NEGATIVE';
        } else {
            return 'NEUTRAL';
        }
    }
}
