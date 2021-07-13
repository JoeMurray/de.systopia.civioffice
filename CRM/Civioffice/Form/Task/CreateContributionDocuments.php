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
class CRM_Civioffice_Form_Task_CreateContributionDocuments extends CRM_Contribute_Form_Task {
    public $contribution_ids;

    public function buildQuickForm() {

      $this->contribution_ids = implode(",", $this->_componentIds);

    // export form elements
    $this->assign('contribution_ids', $this->contribution_ids);
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    parent::postProcess();
  }


}
