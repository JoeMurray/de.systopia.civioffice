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
    public function buildQuickForm() {
        $this->setTitle(E::ts("CiviOffice - Generate Documents based on contributions"));

        $contribution_ids_string = implode(",", $this->_contributionIds);
        $contact_id_string = '';

        $contributions = \Civi\Api4\Contribution::get()
            ->addWhere('id', 'IN', $this->_contributionIds)
            ->execute();
        foreach ($contributions as $contribution) {
            $contact_id_string .= $contribution['contact_id'] . ', ';
        }

        // export form elements
        $this->assign('contribution_ids', $contribution_ids_string);
        $this->assign('contact_ids', $contact_id_string);

        parent::buildQuickForm();
  }

    public function postProcess() {
    $values = $this->exportValues();

    parent::postProcess();
  }


}
