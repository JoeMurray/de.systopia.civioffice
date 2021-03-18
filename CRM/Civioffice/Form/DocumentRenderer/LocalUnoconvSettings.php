<?php
/*-------------------------------------------------------+
| SYSTOPIA CiviOffice Integration                        |
| Copyright (C) 2021 SYSTOPIA                            |
| Author: J. Franz (franz@systopia.de)                   |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*/

use CRM_Civioffice_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Civioffice_Form_DocumentRenderer_LocalUnoconvSettings extends CRM_Core_Form {
  public function buildQuickForm() {

      // add form elements
      $this->add(
          'text',
          'unoconv_binary_path',
          E::ts("path to the unoconv binary"),
          [],
          true
      );

      $this->add(
          'text',
          'temp_folder_path',
          E::ts("path to the working temp folder"),
          [],
          true
      );

      $this->setDefaults(
          [
              'unoconv_binary_path' => Civi::settings()->get(CRM_Civioffice_DocumentRenderer_LocalUnoconv::UNOCONV_BINARY_PATH_SETTINGS_KEY),
              'temp_folder_path' => Civi::settings()->get(CRM_Civioffice_DocumentRenderer_LocalUnoconv::TEMP_FOLDER_PATH_SETTINGS_KEY)
          ]
      );

      $this->addButtons(
          [
              [
                  'type' => 'submit',
                  'name' => E::ts('Save'),
                  'isDefault' => true,
              ],
          ]
      );

      parent::buildQuickForm();
  }

    /**
     * Validate input data
     * This method is executed before postProcess()
     * @return bool
     */
    public function validate(): bool
    {
        parent::validate();

        $folder_to_check = $this->_submitValues['temp_folder_path'];

        if (!empty($folder_to_check)) { // needed?
            $this->_errors['temp_folder_path'] = E::ts("Input is empty");
        }

        // check if temp folder exists, try to create one if not
        if (!file_exists($folder_to_check)) {
            if (!mkdir($folder_to_check)) {
                $this->_errors['temp_folder_path'] = E::ts("Unable to create temp folder");
            }
        }

        if (!is_writable($folder_to_check)) {
            $this->_errors['temp_folder_path'] = E::ts("Unable to write temp folder");
        }

        if (!is_dir($folder_to_check)) {
            $this->_errors['temp_folder_path'] = E::ts("This is not a folder");
        }

        return (count($this->_errors) == 0);
    }

  public function postProcess() {
      $values = $this->exportValues();

      // save to settings
      Civi::settings()->set(CRM_Civioffice_DocumentRenderer_LocalUnoconv::UNOCONV_BINARY_PATH_SETTINGS_KEY, $values['unoconv_binary_path']);

      $values['temp_folder_path'] = rtrim($values['temp_folder_path'], '\/');
      Civi::settings()->set(CRM_Civioffice_DocumentRenderer_LocalUnoconv::TEMP_FOLDER_PATH_SETTINGS_KEY, $values['temp_folder_path']);
  }
}
