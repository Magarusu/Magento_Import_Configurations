<?php

/**
 * System Configurations Import Model.
 *
 * @category    Pragmatic_Configuration_Model_Import_Config
 * @package     Pragmatic_Configuration_Model_Import_Config
 */
class Pragmatic_Configuration_Model_Import_Config extends Mage_Core_Model_Config_Data
{
    const CONFIGURATIONS_FILE_NAME  = 'configurations';
    const CONFIGURATIONS_CODE_ERROR = 'Invalid %s value provided for data set number %s.';

    /**
     * Return an array with cleaned system configurations data ready to be saved.
     * $increment is used when an error occurs to show the data set with the problem.
     *
     * @param $config
     * @param $increment
     *
     * @return mixed
     *
     * @throws Mage_Core_Exception
     */
    protected function _cleanConfigData($config, $increment)
    {
        $scope      = Mage_Core_Model_Store::DEFAULT_CODE;
        $scopeId    = 0;
        $codeError  = null;

        if (!isset($config['path']) || !isset($config['value'])) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS,
                self::CONFIGURATIONS_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        // Validate store/website code.
        if ($config['store_code']) {
            $scope = 'stores';
            $scopeId = Mage::getModel('core/store')->load($config['store_code'], 'code')->getId();
            $codeError = $scopeId ? null : 'store_code';
        } elseif ($config['website_code']) {
            $scope = 'websites';
            $scopeId = Mage::getModel('core/website')->load($config['website_code'], 'code')->getId();
            $codeError = $scopeId ? null : 'website_code';
        }

        if ($codeError) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                self::CONFIGURATIONS_CODE_ERROR, $codeError, ++$increment
            ));
        }

        $config['scope'] = $scope;
        $config['scope_id'] = $scopeId;

        return $config;
    }

    /**
     * Import system configurations.
     *
     * @param bool $throwFileError
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function importConfigurations($throwFileError = true)
    {
        $helper = Mage::helper('pragmatic_configuration');
        $configurations = $helper->getConfigurationFileContent(self::CONFIGURATIONS_FILE_NAME, true);

        if ($configurations) {
            /** @var Mage_Cms_Model_Resource_Page $resource */
            $resource = $this->getResource();
            $resource->beginTransaction();
            foreach ($configurations['config'] as $key => $config) {
                $configData = $this->_cleanConfigData($config, $key);
                $this->setData($configData);
                $this->save();
                // Unset data for a clean new load.
                $this->unsetData();
            }
            $resource->commit();
        } elseif ($throwFileError) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::FILE_ERROR,
                self::CONFIGURATIONS_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION
            ));
        }
    }
}
