<?php

/*
 * @package    Loom_Swatches
 * @copyright  Loom Oy - 2020
 */

namespace Loom\Swatches\Block\ConfigurableProduct\Product\View\Type;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;

class Configurable
{
    protected $jsonEncoder;
    protected $jsonDecoder;
    protected $_productRepository;

    public function __construct(
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            EncoderInterface $jsonEncoder,
            DecoderInterface $jsonDecoder
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->_productRepository = $productRepository;
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function aroundGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        \Closure $proceed
    )
    {
        $sku = [];

        $config = $proceed();
        $config = $this->jsonDecoder->decode($config);

        foreach ($subject->getAllowProducts() as $prod) {
            $id = $prod->getId();
            $product = $this->getProductById($id);
            $sku[$id] = $product->getSku();
        }
        $config['sku'] = $sku;

        return $this->jsonEncoder->encode($config);
    }
}