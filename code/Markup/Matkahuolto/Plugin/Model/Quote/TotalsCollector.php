<?php
namespace Markup\Matkahuolto\Plugin\Model\Quote;

use Magento\Quote\Model\Quote;

class TotalsCollector
{
    /**
     * Reset fee for Bussiennakko
     *
     * @param \Magento\Quote\Model\Quote\TotalsCollector $subject
     * @param Quote $quote
     *
     * @return void
     */
    public function beforeCollect(
        \Magento\Quote\Model\Quote\TotalsCollector $subject,
        Quote $quote
    ) {
        $quote->setBussiennakkoFee(0);
        $quote->setBaseBussiennakkoFee(0);
    }
}
