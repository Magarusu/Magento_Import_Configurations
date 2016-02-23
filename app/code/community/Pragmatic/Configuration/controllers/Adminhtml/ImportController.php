<?php

/**
 * Configuration Import Controller
 *
 * @category    Pragmatic
 * @package     Pragmatic_Configuration
 */
class Pragmatic_Configuration_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action
{
    const PRAGMATIC_CONFIGURATION_REDIRECT_URL  = "adminhtml/system_config/edit/section/pragmatic_configuration/";
    const MESSAGE_IMPORT_SUCCESSFUL             = "have been imported successfully.";

    /**
     * Import Everything except Categories.
     */
    public function indexAction()
    {
        try {
            Mage::getSingleton('pragmatic_configuration/import_website')->importWebsites(false);
            Mage::getSingleton('core/session')->addSuccess('Websites '  . self::MESSAGE_IMPORT_SUCCESSFUL);

            Mage::getSingleton('pragmatic_configuration/import_store_groups')->importStoreGroups(false);
            Mage::getSingleton('core/session')->addSuccess('Store Groups '  . self::MESSAGE_IMPORT_SUCCESSFUL);

            Mage::getSingleton('pragmatic_configuration/import_store')->importStores(false);
            Mage::getSingleton('core/session')->addSuccess('Stores '  . self::MESSAGE_IMPORT_SUCCESSFUL);

            Mage::getSingleton('pragmatic_configuration/import_config')->importConfigurations(false);
            Mage::getSingleton('core/session')->addSuccess('Configurations '  . self::MESSAGE_IMPORT_SUCCESSFUL);

            Mage::getSingleton('pragmatic_configuration/import_block')->importCmsBlocks();
            Mage::getSingleton('core/session')->addSuccess('Blocks '  . self::MESSAGE_IMPORT_SUCCESSFUL);

            Mage::getSingleton('pragmatic_configuration/import_page')->importCmsPages();
            Mage::getSingleton('core/session')->addSuccess('Pages ' . self::MESSAGE_IMPORT_SUCCESSFUL);
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->getUrl(self::PRAGMATIC_CONFIGURATION_REDIRECT_URL));
    }

    /**
     * @todo do importCategoriesAction
     */
    public function importCategoriesAction()
    {
        Mage::getSingleton('core/session')->addSuccess('Categories import not yet implemented.');

        $this->getResponse()->setRedirect($this->getUrl(self::PRAGMATIC_CONFIGURATION_REDIRECT_URL));
    }

    /**
     * Import stores.
     */
    public function importStoresAction()
    {
        try {
            Mage::getSingleton('pragmatic_configuration/import_website')->importWebsites(false);
            Mage::getSingleton('core/session')->addSuccess('Websites '  . self::MESSAGE_IMPORT_SUCCESSFUL);

            Mage::getSingleton('pragmatic_configuration/import_store_groups')->importStoreGroups(false);
            Mage::getSingleton('core/session')->addSuccess('Store Groups '  . self::MESSAGE_IMPORT_SUCCESSFUL);

            Mage::getSingleton('pragmatic_configuration/import_store')->importStores(false);
            Mage::getSingleton('core/session')->addSuccess('Stores '  . self::MESSAGE_IMPORT_SUCCESSFUL);
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->getUrl(self::PRAGMATIC_CONFIGURATION_REDIRECT_URL));
    }

    /**
     * Import system configurations.
     */
    public function importConfigurationsAction()
    {
        try {
            Mage::getSingleton('pragmatic_configuration/import_config')->importConfigurations();

            Mage::getSingleton('core/session')->addSuccess('Configurations '  . self::MESSAGE_IMPORT_SUCCESSFUL);
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->getUrl(self::PRAGMATIC_CONFIGURATION_REDIRECT_URL));
    }

    /**
     * Import cms blocks.
     */
    public function importBlocksAction()
    {
        try {
            Mage::getSingleton('pragmatic_configuration/import_block')->importCmsBlocks();

            Mage::getSingleton('core/session')->addSuccess('Blocks '  . self::MESSAGE_IMPORT_SUCCESSFUL);
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->getUrl(self::PRAGMATIC_CONFIGURATION_REDIRECT_URL));
    }

    /**
     * Import cms pages.
     */
    public function importPagesAction()
    {
        try {
            Mage::getSingleton('pragmatic_configuration/import_page')->importCmsPages();

            Mage::getSingleton('core/session')->addSuccess('Pages ' . self::MESSAGE_IMPORT_SUCCESSFUL);
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->getUrl(self::PRAGMATIC_CONFIGURATION_REDIRECT_URL));
    }
}
