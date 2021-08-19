<?php
/*-------------------------------------------------------+
| SYSTOPIA CiviOffice Integration                        |
| Copyright (C) 2020 SYSTOPIA                            |
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
 * CiviOffice Document Renderer
 */
abstract class CRM_Civioffice_DocumentRenderer extends CRM_Civioffice_OfficeComponent
{
    /**
     * Get the output/generated mime types for this document renderer
     *
     * @return array
     *   list of mime types
     */
    public abstract function getOutputMimeTypes(): array;

    /**
     * Render a document for a list of entities
     *
     * @param $document_with_placeholders
     * @param array $entity_ids
     *   entity ID, e.g. contact_id
     * @param CRM_Civioffice_DocumentStore_LocalTemp $temp_store
     * @param string $target_mime_type
     * @param string $entity_type
     *   entity type, e.g. 'contact' or 'contribution'
     *
     * @return array
     *   list of token_name => token value
     */
    public abstract function render(
        $document_with_placeholders,
        array $entity_ids,
        CRM_Civioffice_DocumentStore_LocalTemp $temp_store,
        string $target_mime_type,
        $entity_type
    ): array;

    /**
     * resolve all tokens
     *
     * @param array $token_names
     *   the list of all token names to be replaced
     *
     * @param integer $entity_id
     *   entity ID, e.g. contact_id
     *
     * @param string $entity_type
     *   entity type, e.g. 'contact' or 'contribution'
     *
     * @return array
     *   list of token_name => token value
     */
    public function resolveTokens($token_names, $entity_id, $entity_type = 'contact'): array
    {
        // TODO: implement
        // TODO: use additional token system
        throw new Exception('resolveTokens not implemented');
    }

    /**
     * Replace all tokens with {token_name} and {$smarty_var.attribute} format
     *
     * @param $string
     * @param integer $entity_id
     *   entity ID, e.g. contact_id
     *
     * @param string $entity_type
     *   entity type, e.g. 'contact' or 'contribution'
     *
     * @return string
     *   input string with the tokens replaced
     *
     * @throws \Exception
     */
    public function replaceAllTokens($string, $entity_id, $entity_type): string
    {
        // TODO: use additional token system

        if ($entity_type == 'contact') {
            $processor = new \Civi\Token\TokenProcessor(
                Civi::service('dispatcher'),
                [
                    'controller' => __CLASS__,
                    'smarty' => false,
                ]
            );

            $identifier = 'contact-token';

            $processor->addMessage($identifier, $string, 'text/plain');
            $processor->addRow()->context('contactId', $entity_id);
            $processor->evaluate();

            /*
             * FIXME: Unfortunately we get &lt; and &gt; from civi backend so we need to decode them back to < and > with htmlspecialchars_decode()
             * This is postponed as it might be risky as it either breaks xml or leads to further problems
             * https://github.com/systopia/de.systopia.civioffice/issues/3
             */

            return $processor->getRow(0)->render($identifier);
        } else if ($entity_type == 'contribution') {

            // debug code:
            $string = "start {contribution.total_amount} end";

            $categories = self::getTokenCategories();

            $messageToken = CRM_Utils_Token::getTokens($string);

            $domain = CRM_Core_BAO_Domain::getDomain();
            $string = CRM_Utils_Token::replaceDomainTokens($string, $domain, TRUE, $messageToken);
            $string = CRM_Utils_Token::replaceContactTokens($string, $contact, TRUE, $messageToken);

            // crash:

            $contribution = self::buildContributionArray("", [$entity_id], 1, false, false, $messageToken, self::class, "****~~~~", true);

            $string = CRM_Utils_Token::replaceContributionTokens($string, $contribution, TRUE, $messageToken);
            //$string = CRM_Utils_Token::replaceHookTokens($string, $contact, $categories, TRUE);

            /*
            if (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY) {
                $smarty = CRM_Core_Smarty::singleton();
                // also add the tokens to the template
                $smarty->assign_by_ref('contact', $contact);
                $string = $smarty->fetch("string:$string");
            }
            */
            return $string;
        }

        // todo: implement?
        throw new Exception('replaceAllTokens not implemented for entity ' . $entity_type);
    }

    /**
     * Generate the contribution array from the form, we fill in the contact details and determine any aggregation
     * around contact_id of contribution_recur_id
     *
     * @param string $groupBy
     * @param array $contributionIDs
     * @param array $returnProperties
     * @param bool $skipOnHold
     * @param bool $skipDeceased
     * @param array $messageToken
     * @param string $task
     * @param string $separator
     * @param bool $isIncludeSoftCredits
     *
     * @return array
     */
    public static function buildContributionArray($groupBy, $contributionIDs, $returnProperties, $skipOnHold, $skipDeceased, $messageToken, $task, $separator, $isIncludeSoftCredits) {
            $contributions = $contacts = [];

            $contributionIDs = [95]; //fixme!!
            $returnProperties = ['contribution.total_amount'];


            foreach ($contributionIDs as $item => $contributionId) {
                $contribution = CRM_Contribute_BAO_Contribution::getContributionTokenValues($contributionId, $messageToken)['values'][$contributionId];
                $contribution['campaign'] = $contribution['contribution_campaign_title'] ?? NULL;
                $contributions[$contributionId] = $contribution;

                if (false && $isIncludeSoftCredits) {
                    //@todo find out why this happens & add comments
                    [$contactID] = explode('-', $item);
                    $contactID = (int) $contactID;
                }
                else {
                    $contactID = $contribution['contact_id'];
                }
                if (!isset($contacts[$contactID])) {
                    $contacts[$contactID] = [];
                    $contacts[$contactID]['contact_aggregate'] = 0;
                    $contacts[$contactID]['combined'] = $contacts[$contactID]['contribution_ids'] = [];
                }

                $contacts[$contactID]['contact_aggregate'] += $contribution['total_amount'];
                $groupByID = empty($contribution[$groupBy]) ? 0 : $contribution[$groupBy];

                $contacts[$contactID]['contribution_ids'][$groupBy][$groupByID][$contributionId] = TRUE;
                if (!isset($contacts[$contactID]['combined'][$groupBy]) || !isset($contacts[$contactID]['combined'][$groupBy][$groupByID])) {
                    $contacts[$contactID]['combined'][$groupBy][$groupByID] = $contribution;
                    $contacts[$contactID]['aggregates'][$groupBy][$groupByID] = $contribution['total_amount'];
                }
                else {
                    $contacts[$contactID]['combined'][$groupBy][$groupByID] = self::combineContributions($contacts[$contactID]['combined'][$groupBy][$groupByID], $contribution, $separator);
                    $contacts[$contactID]['aggregates'][$groupBy][$groupByID] += $contribution['total_amount'];
                }
            }
            // Assign the available contributions before calling tokens so hooks parsing smarty can access it.
            // Note that in core code you can only use smarty here if enable if for the whole site, incl
            // CiviMail, with a big performance impact.
            // Hooks allow more nuanced smarty usage here.
            CRM_Core_Smarty::singleton()->assign('contributions', $contributions);
            foreach ($contacts as $contactID => $contact) {
                [$tokenResolvedContacts] = CRM_Utils_Token::getTokenDetails(['contact_id' => $contactID],
                                                                            $returnProperties,
                                                                            $skipOnHold,
                                                                            $skipDeceased,
                                                                            NULL,
                                                                            $messageToken,
                                                                            $task
                );
                $contacts[$contactID] = array_merge($tokenResolvedContacts[$contactID], $contact);
            }
            return [$contributions, $contacts];
        }

    /**
     * Get the categories required for rendering tokens.
     *
     * @return array
     */
    protected static function getTokenCategories() {
        if (!isset(Civi::$statics[__CLASS__]['token_categories'])) {
            $tokens = [];
            CRM_Utils_Hook::tokens($tokens);
            Civi::$statics[__CLASS__]['token_categories'] = array_keys($tokens);
        }
        return Civi::$statics[__CLASS__]['token_categories'];
    }

    /**
     * We combine the contributions by adding the contribution to each field with the separator in
     * between the existing value and the new one. We put the separator there even if empty so it is clear what the
     * value for previous contributions was
     *
     * @param array $existing
     * @param array $contribution
     * @param string $separator
     *
     * @return array
     */
    public static function combineContributions($existing, $contribution, $separator) {
        foreach ($contribution as $field => $value) {
            $existing[$field] = isset($existing[$field]) ? $existing[$field] . $separator : '';
            $existing[$field] .= $value;
        }
        return $existing;
    }

    /*
     * Could be used to convert larger batches of strings and/or contact ids
     */
    public function multipleReplaceAllTokens()
    {
    }
}
