<?php
/**
 * MageVision Display Filters Per Category Extension
 *
 * @category     MageVision
 * @package      MageVision_DisplayFiltersPerCategory
 * @author       MageVision Team
 * @copyright    Copyright (c) 2019 MageVision (http://www.magevision.com)
 * @license      LICENSE_MV.txt or http://www.magevision.com/license-agreement/
 */
declare(strict_types=1);

namespace MageVision\DisplayFiltersPerCategory\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use MageVision\DisplayFiltersPerCategory\Model\Info as ExtensionInfo;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\AbstractBlock;

class Info extends AbstractBlock implements RendererInterface
{
    /**
     * @var ExtensionInfo
     */
    protected $info;
    
    /**
     * Constructor
     * @param Context $context
     * @param ExtensionInfo $info
     */
    public function __construct(
        Context $context,
        ExtensionInfo $info
    ) {
        $this->info = $info;
        parent::__construct($context);
    }
    
    /**
     * Render form element as HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $version  = $this->info->getExtensionVersion();
        $name = $this->info->getExtensionName();
        $logoUrl = 'https://www.magevision.com/pub/media/logo/default/magevision.png';
        
        $html  = <<<HTML
<div style="background: url('$logoUrl') no-repeat scroll 15px 15px #fff;
border:1px solid #e3e3e3; min-height:100px; display;block;
padding:15px 15px 15px 130px;">
<p>
<strong>$name Extension v$version</strong> by <strong><a href="https://www.magevision.com" target="_blank">MageVision</a></strong><br />
Display Filters in Layered Navigation defined per Category.
</p>
<p>
Check more extensions you might be interested in our <a href="https://www.magevision.com" target="_blank">website</a>.
<br />Like and follow us on 
<a href="https://www.facebook.com/magevision" target="_blank">Facebook</a>,
<a href="https://www.linkedin.com/company/magevision" target="_blank">LinkedIn</a> and
<a href="https://twitter.com/magevision" target="_blank">Twitter</a>.<br />
If you need support or have any questions, please contact us at
<a href="mailto:support@magevision.com">support@magevision.com</a>.
</p>
</div>
HTML;
        return $html;
    }
}
