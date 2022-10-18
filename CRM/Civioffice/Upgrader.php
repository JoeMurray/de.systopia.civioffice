<?php

use CRM_Civioffice_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Civioffice_Upgrader extends CRM_Civioffice_Upgrader_Base
{
    /**
     * Run installation tasks.
     */
    public function install()
    {
        // Create/synchronise the Live Snippets option group.
        $customData = new CRM_Civioffice_CustomData(E::LONG_NAME);
        $customData->syncOptionGroup(E::path('resources/live_snippets_option_group.json'));
    }

    /**
     * Example: Run an external SQL script when the module is uninstalled.
     */
    public function uninstall()
    {
        // Remove civioffice_live_snippets option group.
        \Civi\Api4\OptionGroup::delete(false)
            ->addWhere('name', '=', 'civioffice_live_snippets')
            ->execute();
        // TODO: Revert contact settings for each live snippet.
//        Civi::contactSettings()->revert('civioffice.live_snippets.' . $name);

        // Remove settings created by this extension.
        Civi::settings()->revert(CRM_Civioffice_DocumentStore_Local::LOCAL_TEMP_PATH_SETTINGS_KEY);
        Civi::settings()->revert(CRM_Civioffice_DocumentStore_Local::LOCAL_STATIC_PATH_SETTINGS_KEY);
        Civi::settings()->revert(CRM_Civioffice_DocumentStore_Upload::UPLOAD_PRIVATE_ENABLED_SETTINGS_KEY);
        Civi::settings()->revert(CRM_Civioffice_DocumentStore_Upload::UPLOAD_PUBLIC_ENABLED_SETTINGS_KEY);
        // TODO: Revert contact settings.
//        Civi::contactSettings()->revert(CRM_Civioffice_Form_DocumentFromSingleContact::UNOCONV_CREATE_SINGLE_ACTIVIY_ATTACHMENT);
//        Civi::contactSettings()->revert(CRM_Civioffice_Form_DocumentFromSingleContact::UNOCONV_CREATE_SINGLE_ACTIVIY_TYPE);

        // Revert renderer settings.
        foreach (Civi::settings()->get('civioffice_renderers') as $renderer_uri => $renderer_name) {
            Civi::settings()->revert('civioffice_renderer_' . $renderer_uri);
        }
        Civi::settings()->revert('civioffice_renderers');

        // TODO: Clean-up file cache (rendered files), using a cleanup interface.
    }

    /**
     * Support Live Snippets.
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_0006(): bool
    {
        // Create/synchronise the Live Snippets option group.
        $this->ctx->log->info('Create/synchronise Live Snippets option group.');
        $customData = new CRM_Civioffice_CustomData(E::LONG_NAME);
        $customData->syncOptionGroup(E::path('resources/live_snippets_option_group.json'));
        return true;
    }
}
