<?php
namespace Gsoft\Barcode\Block\Adminhtml\Product;
class Barcodebutton extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{


    public function getButtonData()
    {
        $params=[];
        $params[$this->getProduct()->getId()]=1;

        return [
            'label' => __('Print label'),
            'class' => 'action-secondary',
            'on_click' => 'window.open("'.$this->getUrl('barcode/index/printlabels', ['qty'=>urlencode(serialize($params))]).'")',
            'sort_order' => 10
        ];
    }
}
