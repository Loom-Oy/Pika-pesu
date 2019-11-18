<?php
namespace Markup\Matkahuolto\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ModelObserver implements ObserverInterface
{
    protected $configWriter;

    /**
     *
     */
    public function __construct(
      \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    )
    {
      $this->configWriter = $configWriter;
    }

    /**
     * Execute
     */
    public function execute(EventObserver $observer)
    {
      $object = $observer->getEvent()->getObject();

      if ( ! is_object($object) || get_class($object) !== 'Magento\Variable\Model\Variable' || ! method_exists($object, 'getValue')) {
        return;
      }

      $vakEnabled = !! $object->getPlainValue() ? '1' : '0';

      $this->configWriter->save('carriers/matkahuolto/vak_enabled', $vakEnabled, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
    }
}
