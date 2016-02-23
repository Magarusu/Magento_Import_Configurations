<?php

/**
 * Configuration Import Button Block
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Block_Adminhtml_Import_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Create config button with action.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $data = $element->getOriginalData();
        $action = isset($data['action']) ? $data['action'] : false;

        $url = $this->getUrl('pragmatic_configuration/adminhtml_import/' . $action);

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('import-cms')
            ->setLabel('Import')
            ->setOnClick("setLocation('$url')")
            ->toHtml();

        return $html;
    }
}
