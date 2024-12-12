<?php
/**
 * Copyright © Gsoft All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Gsoft\Barcode\Controller\Adminhtml\Index;


use Dompdf\Dompdf;

class Printlabels extends \Magento\Backend\App\Action
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

    protected $priceCurrency;
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
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $StockState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface  $priceCurrency
    )
    {
        $this->filter = $filter;
        $this->prodCollFactory = $prodCollFactory;
        $this->productRepository = $productRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->StockState = $StockState;
        $this->scopeConfig = $scopeConfig;
        $this->priceCurrency=$priceCurrency;
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
            $unit = "mm";
            $col = $this->getRequest()->getParam('qty');
			if(is_string($col)) $col=unserialize(urldecode($col));
            $label = $this->scopeConfig->getValue("barcode/general/label_content");
            preg_match("/{name height=(.+)}/", $label, $matches);
            $height_name = 0;
            if (!empty($matches)) {
                $height_name = $matches[count($matches) - 1];
                $label = preg_replace("/{name height=(.+)}/", "{name}", $label);
            }
            $paper_height = 0;

            $show_special_price = $this->scopeConfig->getValue("barcode/general/special_price");
            $label_width = $this->scopeConfig->getValue("barcode/general/label_width");
            $label_height = $this->scopeConfig->getValue("barcode/general/label_height");
            $row_labels = $this->scopeConfig->getValue("barcode/general/row_labels");
            $page_rows = $this->scopeConfig->getValue("barcode/general/page_rows");
            $type_barcode = $this->scopeConfig->getValue("barcode/general/barcode");
            $sku = $this->scopeConfig->getValue("barcode/general/code");
            $paper_paddings = json_decode($this->scopeConfig->getValue("barcode/general/paper_paddings"), true);
            $label_paddings = json_decode($this->scopeConfig->getValue("barcode/general/label_paddings"), true);
            $custom_css = $this->scopeConfig->getValue("barcode/general/custom_css");
            if (!is_array($paper_paddings) || count($paper_paddings) != 4) throw new \Exception("Check paper paddings. Must be this format 1,1,1,1");
            if (!is_array($label_paddings) || count($label_paddings) != 4) throw new \Exception("Check label paddings. Must be this format 1,1,1,1");

            $paper_width = $label_width * $row_labels;


            $html = "<style type='text/css'>";
            $html .= '*{box-sizing: border-box; font-size:12px; font-family: Helvetica; padding:0; margin:0;}';
            $html .= "html,body,table,tr,td,div,p{border:0;padding:0;margin:0}";
            $html .= ".paper{ width: " . $paper_width . $unit . "; margin:" . implode($unit . " ", $paper_paddings) . $unit . " }";
            $html .= ".label{ width: " . $label_width . $unit . "; padding:" . implode($unit . " ", $label_paddings) . $unit . "; height:" . $label_height . $unit . "; display:inline-block;overflow:hidden }";
            $html .= ".old_price .price{    text-decoration: line-through; font-size:12px; padding-right:15px;}";
            $html .= ".final_price .price{font-size:16px}";
            $html .= '.barcode {text-align: center;}';
            $html .= '.prices{text-align:right; clear:both;padding-top:5px;}';
            if ($height_name > 0) {
                $html .= ".name{padding-left:10px;margin-top:20px;height:" . $height_name . $unit . "; overflow:hidden; white-space: nowrap;}";
            }
            $html .= $custom_css;
            $html .= "</style>";
            $html .= "<div class='paper'>";
            $current_page = 0;
            $perpage = 0;
            if ($page_rows > 0) {
                $perpage = $page_rows * $row_labels;
                $paper_height = $page_rows * $label_height;
            } else {
                $paper_height = $label_height * count($col) / $row_labels;
            }

            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $current_label = 0;
            if(empty($col)){
                $this->messageManager->addErrorMessage(__("No hay productos seleccionados para imprimir. Compruebe que los productos no sean configurables, grouped o bundled."));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('catalog/product/index');
            }
            foreach ($col as $productId => $qty) {

                if ($current_label > 0 && $row_labels > 1 && $current_label % $row_labels == 0) {
                    //$html.="";
                }
                $p = $this->productRepository->getById($productId);

                $name = '<div class="name">' . $p->getName() . "</div>";

                $finalPrice = $p->getFinalPrice();
                $originalPrice = $p->getPrice();

                $price = "<div class='prices'>";
                if ($show_special_price && $finalPrice != $originalPrice) {
                    $price .= "<span class='old_price'>" . $this->priceCurrency->convertAndFormat($originalPrice) . "</span>";
                }
                $price .= "<span class='final_price'>" . $this->priceCurrency->convertAndFormat($finalPrice) . "</span>";
                $price .= "</div>";

                $barcode = "<div class='barcode'><img src='data:image/png;base64," . base64_encode($generator->getBarcode($p->getData($sku), constant(get_class($generator) . '::' . $type_barcode), 3, 50)) . "' width='100%' /></div>";

                for ($i = 0; $i < $qty; $i++) {
                    if ($perpage > 0) {
                        if ($current_label > 0 && $current_label % $perpage == 0) {
                            $html .= '<p style="page-break-before: always"></p>';
                        }
                    }
                    $html .= '<div class="label">';
                    $labelFormated = str_replace(["{name}", "{price}", "{barcode}", "{sku}"], [$name, $price, $barcode, $p->getData($sku)], $label);
                    $html .= $labelFormated;
                    $html .= "</div>";
                    $current_label++;
                }
            }
            $html .= "</div>";
//echo $html;die();

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);

            //transformamos mm a px

            $dompdf->setPaper([0, 0, $this->convertMMtoPX($paper_width), $this->convertMMtoPX($paper_height)], 'portrait');
            $dompdf->render();
            $dompdf->stream(uniqid() . ".pdf", array("Attachment" => false));
            exit();
        }catch(\Exception $e){
           $msg=$e->getMessage();
           if(empty($msg)) $msg="El SKU no es un código válido para el tipo de código de barras seleccionado";
            $this->messageManager->addErrorMessage($msg);
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/product/index');
        }


        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
       /* $this->messageManager->addSuccess(__('A total of %1 record(s) have been modified.', $collection->getSize()));

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/product/index');*/
    }
    function convertMMtoPX($mm,  $mm_to_px=2.84){
       // $mm_to_px = 3.78;
         if(is_array($mm)){
            foreach($mm as $k=>$v){
                $mm[$k]=$v*$mm_to_px;
            }
            return $mm;
        }
        return  $mm_to_px*$mm;
    }
}
