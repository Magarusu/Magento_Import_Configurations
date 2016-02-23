<?php

/**
 * CMS Block Import Model
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Model_Import_Block extends Mage_Cms_Model_Block
{
    const CMS_BLOCKS_FILE_NAME          = 'cms_blocks';
    const CMS_BLOCKS_IDENTIFIER_ERROR   = 'Please enter a valid XML-identifier. For example something_1, block5, id-4.';

    /**
     * Return an array with cleaned cms block data ready to be saved.
     * $increment is used when an error occurs to show the data set with the problem.
     *
     * @param array $block
     * @param int $increment
     *
     * @return array
     *
     * @throws Mage_Core_Exception
     */
    protected function _cleanBlockData($block, $increment)
    {
        $id = $block['identifier'];
        if (!preg_match("/^[a-zA-Z0-9-_]+$/", $id)) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS . '</br>' . self::CMS_BLOCKS_IDENTIFIER_ERROR,
                self::CMS_BLOCKS_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }
        
        if (!isset($block['identifier']) || !isset($block['title']) || !isset($block['content'])) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS,
                self::CMS_BLOCKS_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        $block['status'] =
            isset($block['status']) && ($block['status'] == 'Enabled' || $block['status'] === 1) ? true : false;

        if ($id = $this->load($block['identifier'])->getId()) {
            $block['block_id'] = $id;
        }

        return $block;
    }

    /**
     * Import CMS blocks from file.
     *
     * @param bool $throwFileError
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function importCmsBlocks($throwFileError = true)
    {
        $helper = Mage::helper('pragmatic_configuration');
        $blocks = $helper->getConfigurationFileContent(self::CMS_BLOCKS_FILE_NAME, true);

        if ($blocks) {
            /** @var Mage_Cms_Model_Resource_Page $resource */
            $resource = $this->getResource();
            $resource->beginTransaction();
            foreach ($blocks['block'] as $key => $block) {
                $blockData = $this->_cleanBlockData($block, $key);
                $this->setData($blockData);
                $this->save();
                // Unset data for a clean new load.
                $this->unsetData();
            }
            $resource->commit();
        } elseif ($throwFileError) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::FILE_ERROR,
                self::CMS_BLOCKS_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION
            ));
        }
    }
}
