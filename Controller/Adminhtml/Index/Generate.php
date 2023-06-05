<?php
/**
 * Copyright © Gsoft All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Gsoft\Barcode\Controller\Adminhtml\Index;

class Generate extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $prodCollFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected $resultPageFactory;

    protected $StockState;
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $prodCollFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     *  * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context  $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $prodCollFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $StockState

    )
    {
        $this->filter = $filter;
        $this->prodCollFactory = $prodCollFactory;
        $this->productRepository = $productRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->StockState = $StockState;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException | \Exception
     */
    public function execute()
    {
        try {

            $collection = $this->filter->getCollection($this->prodCollFactory->create());
            $products = [];
            foreach ($collection->getAllIds() as $productId) {
                $p = $this->productRepository->getById($productId);
                if ($p->getTypeId() != "simple" && $p->getTypeId() != "virtual") continue;
                $qty = $this->StockState->execute($p->getSku());

                $products[] = ['p' => $p, 'qty' => $qty[0]['qty']];

            }
            $page = $this->resultPageFactory->create();
            $block = $page->getLayout()->getBlock('index.generate');
            $block->setData("products", $products);
            return $page;
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/product/index');
        }


    }
}
