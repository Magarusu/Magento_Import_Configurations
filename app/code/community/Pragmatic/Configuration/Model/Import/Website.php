<?php

/**
 * Website Import Model
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Model_Import_Website extends Mage_Core_Model_Website
{
    const WEBSITE_FILE_NAME         = 'websites';
    const WEBSITE_IDENTIFIER_ERROR  =
        'Website code may only contain letters (a-z), numbers (0-9) or underscore(_), the first character must be a letter.';

    /**
     * Return an array with cleaned website data ready to be saved.
     * $increment is used when an error occurs to show the data set with the problem.
     *
     * @param $website
     * @param $increment
     *
     * @return mixed
     *
     * @throws Mage_Core_Exception
     */
    protected function _cleanWebsiteData($website, $increment)
    {
        $code = $website['code'];
        if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $code)) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS . '</br>' . self::WEBSITE_IDENTIFIER_ERROR,
                self::WEBSITE_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        if (!isset($website['name']) || !isset($website['code'])) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS,
                self::WEBSITE_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        if ($id = $this->load($code)->getId()) {
            $website['website_id'] = $id;
        }

        return $website;
    }

    /**
     * Import Websites from file.
     *
     * @param bool $throwFileError
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function importWebsites($throwFileError = true)
    {
        $helper = Mage::helper('pragmatic_configuration');
        $websites = $helper->getConfigurationFileContent(self::WEBSITE_FILE_NAME, true);

        if ($websites) {
            /** @var Mage_Cms_Model_Resource_Page $resource */
            $resource = $this->getResource();
            $resource->beginTransaction();
            foreach ($websites['website'] as $key => $website) {
                $websiteData = $this->_cleanWebsiteData($website, $key);
                $this->setData($websiteData);
                $this->save();
                // Unset data for a clean new load.
                $this->unsetData();
            }
            $resource->commit();
        } elseif ($throwFileError) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::FILE_ERROR,
                self::WEBSITE_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION
            ));
        }
    }
}
