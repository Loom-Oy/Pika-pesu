<?php

/*
 * @package    Loom_ColorOrderStatus
 * @copyright  Loom Oy - 2020
 */

// @codingStandardsIgnoreFile

namespace Loom\ColorOrderStatus\Observer;

use Magento\Framework\Event\ObserverInterface;

class EditStatusFormBeforeHtml implements ObserverInterface {

    protected $request;
    protected $helper;
    
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Loom\ColorOrderStatus\Helper\Data $helper
    )
    {
        $this->request = $request;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $block =  $observer->getEvent()->getBlock();
         if($block->getId() == 'new_order_status') {

             $form = $block->getForm();
             $fieldset = $form->addFieldset('base_fieldset_color', ['legend' => __('Customize')]);

             $fieldset->addType('color',
                 \Loom\ColorOrderStatus\Block\System\Config\Form\Field\Color::class);

             $status = $this->request->getParam('status');

             $fieldset->addField(
                 'color',
                 'color',
                 [
                     'name' => 'color',
                     'label' => __('Color'),
                     'required' => false,
                     'value'=>$this->helper->getStatusColor($status)
                 ]
             );
         }
    }
}
