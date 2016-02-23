<?php

/**
 * Configuration Import Helper
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Helper_Data extends Mage_Core_Helper_Abstract
{
    const FILE_ERROR                = "File cannot be read or it contains errors. Please check file: %s.";
    const MISSING_ARGUMENTS         = "Missing arguments in file %s for data set number %s. Please check it for errors.";
    const FILE_DEFAULT_EXTENSION    = 'xml';

    /**
     * Get configuration content from file by specific content type.
     *
     * @param $type
     * @param bool $asArray
     *
     * @return null|array|SimpleXMLElement
     */
    public function getConfigurationFileContent($type, $asArray = false)
    {
        $xmlObject = null;

        $directory = Mage::getStoreConfig('pragmatic_configuration/import/directory');
        $xmlPath = Mage::getBaseDir() . DS . $directory . DS . $type . '.' . self::FILE_DEFAULT_EXTENSION;

        // Create XML object.
        // Varien_Simplexml_Config cannot be used because we need 'LIBXML_NOCDATA' flag for CDATA
        if (file_exists($xmlPath)) {
            $xmlObject = simplexml_load_file($xmlPath, 'SimpleXMLElement', LIBXML_NOCDATA);
        }

        // Convert XML object to array.
        if ($xmlObject && $asArray) {
            $xmlObject = json_decode(json_encode($xmlObject), true);
            // Return an array if there is only one $xmlObject.
            $xmlObjectKey = key($xmlObject);
            if (!$xmlObject[$xmlObjectKey][0]) {
                $xmlObject[$xmlObjectKey] = array($xmlObject[$xmlObjectKey]);
            }
        }

        return $xmlObject;
    }

    /**
     * Retrieve store ids based on store codes.
     *
     * @param $data
     *
     * @return array|Mage_Core_Model_Resource_Store_Collection|null
     */
    public function getStoreIds($data)
    {
        if (isset($data['stores']['store']) && !empty($data['stores']['store'])) {
            $storesCodes = $data['stores']['store'];
        } elseif (isset($data['store']) && !empty($data['store'])) {
            $storesCodes = $data['store'];
        } else {
            return null;
        }

        $storeIds = Mage::getModel('core/store')->getCollection()
            ->addFieldToFilter('code', array('in' => $storesCodes))
            ->addFieldToSelect('store_id');
        $storeIds = array_map(function ($ar) {return $ar['store_id'];}, $storeIds->getData());

        return $storeIds;
    }
}
