<?php

namespace Synerise\Integration\Block\Opengraph;

use Magento\Catalog\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Synerise\Integration\Helper\Category;

class Additional extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_PAGE_TRACKING_ENABLED = 'synerise/page_tracking/enabled';

    const XML_PATH_PAGE_TRACKING_OPENGRAPH = 'synerise/page_tracking/opengraph';

    /**
     * @var Data
     */
    protected $catalogHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Category
     */
    private $categoryHelper;

    public function __construct(
        Context $context,
        Data $catalogHelper,
        ScopeConfigInterface $scopeConfig,
        Category $categoryHelper,
        array $data = []
    )
    {
        $this->catalogHelper = $catalogHelper;
        $this->scopeConfig = $scopeConfig;
        $this->categoryHelper = $categoryHelper;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->catalogHelper->getProduct();
    }

    public function getFormattedCategoryPath($categoryId)
    {
        return $this->categoryHelper->getFormattedCategoryPath($categoryId);
    }

    /**
     * Is the page tracking enabled.
     *
     * @return bool
     */
    public function isPageTrackingEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAGE_TRACKING_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isOpengraphEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAGE_TRACKING_OPENGRAPH
        );
    }
}