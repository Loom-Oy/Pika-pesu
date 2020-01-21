<?php

/*
 * @package    Loom_ColorOrderStatus
 * @copyright  Loom Oy - 2020
 */

// @codingStandardsIgnoreFile

namespace Loom\ColorOrderStatus\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class Color extends  \Magento\Framework\Data\Form\Element\Text {

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('color');
    }

    public function getHtml()
    {
        if ($this->getRequired()) {
            $this->addClass('required-entry _required');
        }
        if ($this->_renderer) {
            $html = $this->_renderer->render($this);
        } else {
            $html = $this->getDefaultHtml();
        }

        return $html;
    }
}
