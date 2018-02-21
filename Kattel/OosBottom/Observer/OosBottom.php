<?php

    namespace Kattel\OosBottom\Observer;
 
    use Magento\Framework\Event\ObserverInterface;
    use Magento\Framework\App\RequestInterface;
 
    class OosBottom implements ObserverInterface
    {

        /**
 * @var \Magento\Customer\Model\Session
 */
//protected $customerSession;
protected $scopeConfig;
protected $_storeManager;
protected $ossBottom;
const XML_PATH_SORT_OUT_OF_STOCK    = 'catalog/OosBottom/sort_out_of_stock_at_bottom';
const XML_PATH_SORT_OUT_OF_STOCK_SEARCH_RESULT = 'catalog/OosBottom/sort_out_of_stock_at_bottom_for_search';


public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
    \Magento\Store\Model\StoreManagerInterface $storeManager, 
    \Kattel\OosBottom\Helper\Data $ossBottom)
    {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->ossBottom = $ossBottom;
    }

        public function execute(\Magento\Framework\Event\Observer $observer) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            if (!$this->scopeConfig->getValue(self::XML_PATH_SORT_OUT_OF_STOCK, $storeScope)) {
                return false;
            }
        $collection = $observer->getEvent()->getData('collection');
        try {
            //If you have multi location inventory
            //$websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $websiteId = 0;

            $stockId = 'stock_id';

            $collection->getSelect()->joinLeft(
                array('_inv' => $collection->getResource()->getTable('cataloginventory_stock_status')),
                "_inv.product_id = e.entity_id and _inv.website_id=$websiteId and _inv.stock_id=$stockId",
                array('stock_status')
            );
            $collection->addExpressionAttributeToSelect('in_stock', 'IFNULL(_inv.stock_status,0)', array());

            $collection->getSelect()->reset('order');
            $collection->getSelect()->order('in_stock DESC');

        } 
        catch (Exception $e) {}
        return $this;
        }
    }