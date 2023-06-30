<?php
namespace AHT\GiftCard\Ui\Component\Form\Field;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use AHT\GiftCard\Helper\Data;

class CodeLength extends \Magento\Ui\Component\Form\Field
{

    private $_helperData;
   /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $helperData,
        array $components = [],
        array $data = []
    ){
        $this->_helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepare()
    {
        parent::prepare();

        if ($this->_helperData->getCodeConfig('length')) {
            $currentConfig = $this->getData('config');
            $currentConfig['default'] =$this->_helperData->getCodeConfig('length');
            $this->setData('config', $currentConfig);
        }
    }

}
