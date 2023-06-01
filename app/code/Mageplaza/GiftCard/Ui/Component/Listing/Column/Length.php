<?php
namespace Mageplaza\GiftCard\Ui\Component\Listing\Column;
use Mageplaza\GiftCard\Helper\Data;
class Length extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $helperData;
    public function __construct(
    Data $helperData,
    \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
    \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
    array $components = [],
    array $data = []
    )
    
    {
        $this->helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['giftcard_id'])) {
                    $item[$this->getData('name')] = $this->helperData->getCodeConfig('length');
                }
            }
        }
        return $dataSource;
    }
}
