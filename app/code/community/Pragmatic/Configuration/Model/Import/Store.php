<?php

/**
 * Store Import Model.
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Model_Import_Store extends Mage_Core_Model_Store
{
    const STORE_FILE_NAME           = 'stores';
    const STORE_INVALID_GROUP       = 'Invalid store group name provided for data set number %s.';
    const STORE_IDENTIFIER_ERROR    =
        'The store code may contain only letters (a-z), numbers (0-9) or underscore(_), the first character must be a letter.';

    protected $_storeGroupIds = array();

    /**
     * Return an array with cleaned store data ready to be saved.
     * $increment is used when an error occurs to show the data set with the problem.
     *
     * @param array $store
     * @param int $increment
     *
     * @return array
     *
     * @throws Mage_Core_Exception
     */
    protected function _cleanStoreData($store, $increment)
    {
        // Validate store code.
        $code = $store['code'];
        if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $code)) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS . '</br>' . self::STORE_IDENTIFIER_ERROR,
                self::STORE_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        // Get Store group id by group name.
        if (!$this->_storeGroupIds[$store['group_name']]) {
            $storeGroup = Mage::getResourceModel('core/store_group_collection')
                ->addFieldToFilter('name', $store['group_name'])
                ->getFirstItem();
            $this->_storeGroupIds[$store['group_name']] = $storeGroup->getId();
        }

        // validate store group.
        if ($storeGroupId = $this->_storeGroupIds[$store['group_name']]) {
            $store['group_id'] = $storeGroupId;
        } else {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(self::STORE_INVALID_GROUP, ++$increment));
        }

        if (!isset($store['name'])) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS,
                self::STORE_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        $store['is_active'] =
            isset($store['status']) && ($store['status'] == 'Enabled' || $store['status'] === 1) ? true : false;

        if ($id = $this->load($code)->getId()) {
            $store['store_id'] = $id;
        }

        return $store;
    }

    /**
     * Import Stores from file.
     *
     * @param bool $throwFileError
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function importStores($throwFileError = true)
    {
        $helper = Mage::helper('pragmatic_configuration');
        $stores = $helper->getConfigurationFileContent(self::STORE_FILE_NAME, true);

        if ($stores) {
            /** @var Mage_Cms_Model_Resource_Page $resource */
            $resource = $this->getResource();
            $resource->beginTransaction();
            foreach ($stores['store'] as $key => $store) {
                $storeData = $this->_cleanStoreData($store, $key);
                $this->setData($storeData);
                $this->save();
                // Unset data for a clean new load.
                $this->unsetData();
            }
            $resource->commit();
        } elseif ($throwFileError) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::FILE_ERROR,
                self::STORE_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION
            ));
        }
    }
}
