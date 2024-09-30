<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace bbbext_bnurl\bigbluebuttonbn;

use bbbext_bnurl\utils;
use stdClass;

/**
 * A class for the main mod form extension
 *
 * @package   bbbext_bnurl
 * @copyright 2023 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Laurent David (laurent@call-learning.fr)
 */
class mod_form_addons extends \mod_bigbluebuttonbn\local\extension\mod_form_addons {

    /**
     * Constructor
     *
     * @param \MoodleQuickForm $mform
     * @param stdClass|null $bigbluebuttonbndata
     * @param string|null $suffix
     */
    public function __construct(\MoodleQuickForm &$mform, ?stdClass $bigbluebuttonbndata = null, ?string $suffix = null) {
        parent::__construct($mform, $bigbluebuttonbndata, $suffix);
        // Supplement BBB data with additional information.
        if (!empty($bigbluebuttonbndata->id)) {
            $data = $this->retrieve_additional_data($bigbluebuttonbndata->id);
            $this->bigbluebuttonbndata = (object) array_merge((array) $this->bigbluebuttonbndata, $data);
            $this->bigbluebuttonbndata->bnurl_paramcount = count($data["bnurl_".array_key_first(utils::PARAM_TYPES)] ?? []);
        }
    }

    /**
     * Retrieve data from the database if any.
     *
     * @param int $id
     * @return array
     */
    private function retrieve_additional_data(int $id): array {
        global $DB;
        $data = [];
        $bnurlrecords = $DB->get_records(mod_instance_helper::SUBPLUGIN_TABLE, [
            'bigbluebuttonbnid' => $id,
        ]);
        if ($bnurlrecords) {
            $bnurlrecords = array_values($bnurlrecords);
            foreach ($bnurlrecords as $bnurlrecord) {
                foreach (utils::PARAM_TYPES as $paramtype => $paramtypevalue) {
                    if (!isset($data["bnurl_{$paramtype}"])) {
                        $data["bnurl_{$paramtype}"] = [];
                    }
                    $data["bnurl_{$paramtype}"][] = $bnurlrecord->{$paramtype} ?? '';
                }
            }
        }
        return $data;
    }

    /**
     * Allows modules to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data passed by reference
     */
    public function data_postprocessing(\stdClass &$data): void {
        // Nothing for now.
    }

    /**
     * Allow module to modify the data at the pre-processing stage.
     *
     * This method is also called in the bulk activity completion form.
     *
     * @param array|null $defaultvalues
     */
    public function data_preprocessing(?array &$defaultvalues): void {
        // This is where we can add the data from the bnurl table to the data provided.
        if (!empty($defaultvalues['id'])) {
            $data = $this->retrieve_additional_data(intval($defaultvalues['id']));
            $defaultvalues = (object) array_merge($defaultvalues, $data);
        }
    }

    /**
     * Can be overridden to add custom completion rules if the module wishes
     * them. If overriding this, you should also override completion_rule_enabled.
     * <p>
     * Just add elements to the form as needed and return the list of IDs. The
     * system will call disabledIf and handle other behaviour for each returned
     * ID.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules(): array {
        return [];
    }

    /**
     * Called during validation. Override to indicate, based on the data, whether
     * a custom completion rule is enabled (selected).
     *
     * @param array $data Input data (not yet validated)
     * @return bool True if one or more rules is enabled, false if none are;
     *   default returns false
     */
    public function completion_rule_enabled(array $data): bool {
        return false;
    }

    /**
     * Form adjustments after setting data
     *
     * @return void
     */
    public function definition_after_data() {
        // After data.
        $isdeleting = optional_param_array('bnurl_paramdelete', [], PARAM_RAW);
        // Get the index of the delete button that was pressed.
        if (!empty($isdeleting)) {
            $firstindex = array_key_first($isdeleting);
            // Then reassign values from the deleted group to the previous group.
            $paramcount = optional_param('bnurl_paramcount', 0, PARAM_INT);
            for ($index = $firstindex; $index < $paramcount; $index++) {
                $nextindex = $index + 1;
                if ($this->mform->elementExists("bnurl_paramgroup[{$nextindex}]")) {
                    $nextgroupelement = $this->mform->getElement("bnurl_paramgroup[{$nextindex}]");
                    if (!empty($nextgroupelement)) {
                        $nextgroupvalue = $nextgroupelement->getValue();
                        $currentgroupelement = $this->mform->getElement("bnurl_paramgroup[{$index}]");
                        $value = [
                            "bnurl_paramname[{$index}]" => $nextgroupvalue["bnurl_paramname[{$nextindex}]"],
                            "bnurl_paramvalue[{$index}]" => $nextgroupvalue["bnurl_paramvalue[{$nextindex}]"],
                        ];
                        $currentgroupelement->setValue($value);
                    }
                }
            }
            $newparamcount = $paramcount - 1;
            $this->mform->removeElement("bnurl_paramgroup[{$newparamcount}]");
            $this->mform->getElement('bnurl_paramcount')->setValue($newparamcount);
        }
    }

    /**
     * Add new form field definition
     */
    public function add_fields(): void {
        $this->mform->addElement('header', 'bnurl', get_string('formname', 'bbbext_bnurl'));
        $this->mform->addHelpButton('bnurl', 'formname', 'bbbext_bnurl');
        $paramcount = optional_param('bnurl_paramcount', $this->bigbluebuttonbndata->bnurl_paramcount ?? 0, PARAM_RAW);
        $paramcount += optional_param('bnurl_addparamgroup', 0, PARAM_RAW) ? 1 : 0;
        $isdeleting = optional_param_array('bnurl_paramdelete', [], PARAM_RAW);
        foreach ($isdeleting as $index => $value) {
            // This prevents the last delete button from submitting the form.
            $this->mform->registerNoSubmitButton("bnurl_paramdelete[$index]");
        }
        for ($index = 0; $index < $paramcount; $index++) {
            $paramname = $this->mform->createElement(
                'text',
                "bnurl_paramname[$index]",
                get_string('param_name', 'bbbext_bnurl'),
                ['size' => '6']
            );
            $paramvalue = $this->mform->createElement(
                'autocomplete',
                "bnurl_paramvalue[$index]",
                get_string('param_value', 'bbbext_bnurl'),
                utils::get_options_for_parameters(),
                [
                    'tags' => true,
                ]
            );
            $paramvalue->setValue('');
            $paramtype = $this->mform->createElement(
                'select',
                "bnurl_eventtype[$index]",
                get_string('param_eventtype', 'bbbext_bnurl'),
                utils::get_option_for_eventtype(),
            );
            $paramdelete = $this->mform->createElement(
                'submit',
                "bnurl_paramdelete[$index]",
                get_string('delete'),
                [],
                false,
                ['customclassoverride' => 'btn-sm btn-secondary float-left']
            );

            $this->mform->addGroup(
                [
                    $paramname, $paramvalue, $paramtype, $paramdelete,
                ],
                "bnurl_paramgroup[$index]",
                get_string('paramgroup', 'bbbext_bnurl'),
                [' '],
                false
            );
            $this->mform->setType("bnurl_paramname[$index]", utils::PARAM_TYPES['paramname']);
            $this->mform->setType("bnurl_paramvalue[$index]", utils::PARAM_TYPES['paramvalue']);
            $this->mform->setType("bnurl_eventtype[$index]", utils::PARAM_TYPES['eventtype']);
            $this->mform->setType("bnurl_paramdelete[$index]", PARAM_RAW);

            $this->mform->registerNoSubmitButton("bnurl_paramdelete[$index]");

        }
        // Add a button to add new param groups.
        $this->mform->addElement('submit', 'bnurl_addparamgroup', get_string('addparamgroup', 'bbbext_bnurl'));
        $this->mform->setType('bnurl_addparamgroup', PARAM_TEXT);
        $this->mform->registerNoSubmitButton('bnurl_addparamgroup');
        $this->mform->addElement('hidden', 'bnurl_paramcount');
        $this->mform->setType('bnurl_paramcount', PARAM_INT);
        $this->mform->setConstants(['bnurl_paramcount' => $paramcount]);
    }

    /**
     * Validate form and returns an array of errors indexed by field name
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation(array $data, array $files): array {
        $errors = [];
        foreach (utils::PARAM_TYPES as $paramtype => $paramtypevalue) {
            if (!empty($data['bnurl_' . $paramtype])
                && clean_param_array($data['bnurl_' . $paramtype], $paramtypevalue, true) === false) {
                $errors["bnurl_{$paramtype}"] = get_string('invalidvalue', 'bbbext_bnurl');
            }
        }
        return $errors;
    }
}
