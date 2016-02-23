<?php

/**
 * CMS Page Import Model
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Model_Import_Page extends Mage_Cms_Model_Page
{
    const CMS_PAGES_FILE_NAME = 'cms_pages';

    /**
     * Return an array with cleaned cms page data ready to be saved.
     * $increment is used when an error occurs to show the data set with the problem.
     *
     * @param array $page
     * @param $increment
     *
     * @return array
     *
     * @throws Mage_Core_Exception
     */
    protected function _cleanPageData($page, $increment)
    {
        $data = array();
        $data['identifier'] = isset($page['url_key']) && !empty($page['url_key']) ? $page['url_key'] : null;
        $data['title'] = isset($page['title']) && !empty($page['title']) ? $page['title'] : null;
        $data['root_template'] = isset($page['layout']) && !empty($page['layout']) ? $page['layout'] : null;

        if (!isset($data['identifier']) || !isset($data['title']) || !isset($data['root_template'])) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::MISSING_ARGUMENTS,
                self::CMS_PAGES_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION,
                ++$increment
            ));
        }

        $data['is_active'] =
            isset($page['status']) && ($page['status'] == 'Enabled' || $page['status'] === 1) ? true : false;
        $data['content'] = isset($page['content']) && !empty($page['content']) ? $page['content'] : false;
        $data['content_heading'] =
            isset($page['content_heading']) && !empty($page['content_heading']) ? $page['content_heading'] : false;
        $data['layout_update_xml'] =
            isset($page['layout_update_xml']) && !empty($page['layout_update_xml']) ? $page['layout_update_xml'] : false;

        if ($id = $this->load($data['identifier'])->getId()) {
            $data['page_id'] = $id;
        }

        return $data;
    }

    /**
     * Import CMS pages from file.
     *
     * @param bool $throwFileError
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function importCmsPages($throwFileError = true)
    {
        $helper = Mage::helper('pragmatic_configuration');
        $pages = $helper->getConfigurationFileContent(self::CMS_PAGES_FILE_NAME, true);

        if ($pages) {
            foreach ($pages['page'] as $key => $page) {
                $pages['page'][$key] = $this->_cleanPageData($page, $key);
                $pages['page'][$key]['stores'] = $helper->getStoreIds($page);
            }

            /** @var Mage_Cms_Model_Resource_Page $resource */
            $resource = $this->getResource();
            $resource->beginTransaction();
            foreach ($pages['page'] as $page) {
                $this->setData($page);
                $this->save();
            }
            $resource->commit();
        } elseif ($throwFileError) {
            Mage::throwException(Mage::helper('pragmatic_configuration')->__(
                Pragmatic_Configuration_Helper_Data::FILE_ERROR,
                self::CMS_PAGES_FILE_NAME . '.' . Pragmatic_Configuration_Helper_Data::FILE_DEFAULT_EXTENSION
            ));
        }
    }
}
