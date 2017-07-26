<?php

/**
 * Store Group Import Model.
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Model_Import_Store_Groups extends Mage_Core_Model_Store_Group
{
    const STORE_GROUPS_FILE_NAME        = 'store_groups';
    const STORE_GROUPS_INVALID_WEBSITE  = 'Invalid website code for data set number %s.';
    const STORE_GROUPS_INVALID_CATEGORY = 'Invalid root category name for data set number %s.';

    /**
     * Return an array with cleaned store group data ready to be saved.
     * $increment is used when an error occurs to show the data set with the problem.
     *
     * @param $storeGroup
     * @param $increment
     *
     * @return mixed
     *
     * @throws Mage_Core_Exception
     */
    protected function _cleanStoreGroupData($storeGroup, $increment)
    {
        // Validate website code.
        $website = Mage::getModel('core/website')->load($storeGroup['website_code'], 'code');
        if ($websiteId = $website->getId()) {
            $storeGroup['website_id'] = $websiteId;
        } else {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                self::STORE_GROUPS_INVALID_WEBSITE, ++$increment
            ));
        }

        // Validate root category.
        $rootCategory = Mage::getModel('catalog/category')->loadByAttribute('name', $storeGroup['root_category']);
        if ((int) $rootCategory->getLevel() === Mage_Catalog_Model_Category::TREE_ROOT_ID) {
            $storeGroup['root_category_id'] = $rootCategory->getId();
        } else {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                self::STORE_GROUPS_INVALID_CATEGORY, ++$increment
            ));
        }

        if (!isset($storeGroup['name'])) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS,
                self::STORE_GROUPS_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        if (isset($storeGroup['id']) && $this->load($storeGroup['id'])) {
            $storeGroup['group_id'] = $storeGroup['id'];
        } elseif ($id = $this->load($storeGroup['name'], 'name')->getId()) {
            $storeGroup['group_id'] = $id;
        }

        return $storeGroup;
    }

    /**
     * Import Store Groups from file.
     *
     * @param bool $throwFileError
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function importStoreGroups($throwFileError = true)
    {
        $helper = Mage::helper('pragmatic_configuration');
        $storeGroups = $helper->getConfigurationFileContent(self::STORE_GROUPS_FILE_NAME, true);

        if ($storeGroups) {
            /** @var Mage_Cms_Model_Resource_Page $resource */
            $resource = $this->getResource();
            $resource->beginTransaction();
            foreach ($storeGroups['store_group'] as $key => $storeGroup) {
                $storeGroupData = $this->_cleanStoreGroupData($storeGroup, $key);
                $this->setData($storeGroupData);
                $this->save();
                // Unset data for a clean new load.
                $this->unsetData();
            }
            $resource->commit();
        } elseif ($throwFileError) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::FILE_ERROR,
                self::STORE_GROUPS_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION
            ));
        }
    }
}
