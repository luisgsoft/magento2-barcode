<?php
namespace Gsoft\Barcode\Model\Adminhtml\System\Config\Source;

class Barcode implements \Magento\Framework\Option\ArrayInterface
{
    protected $_options;


    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [
                'TYPE_CODE_39'=>"C39",
                'TYPE_CODE_39_CHECKSUM'=>"C39+",
                'TYPE_CODE_39E'=>"C39E",
                'TYPE_CODE_39E_CHECKSUM'=>"C39E+",
                'TYPE_CODE_93'=>"C93",
                'TYPE_STANDARD_2_5'=>"S25",
                'TYPE_STANDARD_2_5_CHECKSUM'=>"S25+",
                'TYPE_INTERLEAVED_2_5'=>"I25",
                'TYPE_INTERLEAVED_2_5_CHECKSUM'=>"I25+",
                'TYPE_CODE_128'=>"C128",
                'TYPE_CODE_128_A'=>"C128A",
                'TYPE_CODE_128_B'=>"C128B",
                'TYPE_CODE_128_C'=>"C128C",
                'TYPE_EAN_2'=>"EAN2",
                'TYPE_EAN_5'=>"EAN5",
                'TYPE_EAN_8'=>"EAN8",
                'TYPE_EAN_13'=>"EAN13",
                'TYPE_UPC_A'=>"UPCA",
                'TYPE_UPC_E'=>"UPCE",
                'TYPE_MSI'=>"MSI",
                'TYPE_MSI_CHECKSUM'=>"MSI+",
                'TYPE_POSTNET'=>"POSTNET",
                'TYPE_PLANET'=>"PLANET",
                'TYPE_RMS4CC'=>"RMS4CC",
                'TYPE_KIX'=>"KIX",
                'TYPE_IMB'=>"IMB",
                'TYPE_CODABAR'=>"CODABAR",
                'TYPE_CODE_11'=>"CODE11",
                'TYPE_PHARMA_CODE'=>"PHARMA",
                'TYPE_PHARMA_CODE_TWO_TRACKS'=>"PHARMA2T",



            ];
        }
        return $this->_options;
    }
}
