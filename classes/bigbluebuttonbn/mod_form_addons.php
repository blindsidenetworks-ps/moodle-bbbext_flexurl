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

namespace bbbext_flexurl\bigbluebuttonbn;

use bbbext_flexurl\utils;
use stdClass;

/**
 * A class for the main mod form extension
 *
 * @package   bbbext_flexurl
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
            $this->bigbluebuttonbndata->flexurl_paramcount = count($data["flexurl_".array_key_first(utils::PARAM_TYPES)] ?? []);
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
        $flexurlrecords = $DB->get_records(mod_instance_helper::SUBPLUGIN_TABLE, [
            'bigbluebuttonbnid' => $id,
        ]);
        if ($flexurlrecords) {
            $flexurlrecords = array_values($flexurlrecords);
            foreach ($flexurlrecords as $flexurlrecord) {
                foreach (utils::PARAM_TYPES as $paramtype => $paramtypevalue) {
                    if (!isset($data["flexurl_{$paramtype}"])) {
                        $data["flexurl_{$paramtype}"] = [];
                    }
                    $data["flexurl_{$paramtype}"][] = $flexurlrecord->{$paramtype} ?? '';
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
        // This is where we can add the data from the flexurl table to the data provided.
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
        $isdeleting = optional_param_array('flexurl_paramdelete', [], PARAM_RAW);
        // Get the index of the delete button that was pressed.
        if (!empty($isdeleting)) {
            $firstindex = array_key_first($isdeleting);
            // Then reassign values from the deleted group to the previous group.
            $paramcount = optional_param('flexurl_paramcount', 0, PARAM_INT);
            for ($index = $firstindex; $index < $paramcount; $index++) {
                $nextindex = $index + 1;
                if ($this->mform->elementExists("flexurl_paramgroup[{$nextindex}]")) {
                    $nextgroupelement = $this->mform->getElement("flexurl_paramgroup[{$nextindex}]");
                    if (!empty($nextgroupelement)) {
                        $nextgroupvalue = $nextgroupelement->getValue();
                        $currentgroupelement = $this->mform->getElement("flexurl_paramgroup[{$index}]");
                        $value = [
                            "flexurl_paramname[{$index}]" => $nextgroupvalue["flexurl_paramname[{$nextindex}]"],
                            "flexurl_paramvalue[{$index}]" => $nextgroupvalue["flexurl_paramvalue[{$nextindex}]"],
                        ];
                        $currentgroupelement->setValue($value);
                    }
                }
            }
            $newparamcount = $paramcount - 1;
            $this->mform->removeElement("flexurl_paramgroup[{$newparamcount}]");
            $this->mform->getElement('flexurl_paramcount')->setValue($newparamcount);
        }
    }

    /**
     * Add new form field definition
     */
    public function add_fields(): void {
        $this->mform->addElement('header', 'flexurl', get_string('formname', 'bbbext_flexurl'));
        $this->mform->addHelpButton('flexurl', 'formname', 'bbbext_flexurl');
        $paramcount = optional_param('flexurl_paramcount', $this->bigbluebuttonbndata->flexurl_paramcount ?? 0, PARAM_RAW);
        $paramcount += optional_param('flexurl_addparamgroup', 0, PARAM_RAW) ? 1 : 0;
        $isdeleting = optional_param_array('flexurl_paramdelete', [], PARAM_RAW);
        foreach ($isdeleting as $index => $value) {
            // This prevents the last delete button from submitting the form.
            $this->mform->registerNoSubmitButton("flexurl_paramdelete[$index]");
        }
        for ($index = 0; $index < $paramcount; $index++) {
            $paramname = $this->mform->createElement(
                'text',
                "flexurl_paramname[$index]",
                get_string('param_name', 'bbbext_flexurl'),
                ['size' => '6']
            );
            $paramvalue = $this->mform->createElement(
                'autocomplete',
                "flexurl_paramvalue[$index]",
                get_string('param_value', 'bbbext_flexurl'),
                utils::get_options_for_parameters(),
                [
                    'tags' => true,
                ]
            );
            $paramvalue->setValue('');
            $paramtype = $this->mform->createElement(
                'select',
                "flexurl_eventtype[$index]",
                get_string('param_eventtype', 'bbbext_flexurl'),
                utils::get_option_for_eventtype(),
            );
            $paramdelete = $this->mform->createElement(
                'submit',
                "flexurl_paramdelete[$index]",
                get_string('delete'),
                [],
                false,
                ['customclassoverride' => 'btn-sm btn-secondary float-left']
            );

            $this->mform->addGroup(
                [
                    $paramname, $paramvalue, $paramtype, $paramdelete,
                ],
                "flexurl_paramgroup[$index]",
                get_string('paramgroup', 'bbbext_flexurl'),
                [' '],
                false
            );
            $this->mform->setType("flexurl_paramname[$index]", utils::PARAM_TYPES['paramname']);
            $this->mform->setType("flexurl_paramvalue[$index]", utils::PARAM_TYPES['paramvalue']);
            $this->mform->setType("flexurl_eventtype[$index]", utils::PARAM_TYPES['eventtype']);
            $this->mform->setType("flexurl_paramdelete[$index]", PARAM_RAW);

            $this->mform->registerNoSubmitButton("flexurl_paramdelete[$index]");

        }
        // Add a button to add new param groups.
        $this->mform->addElement('submit', 'flexurl_addparamgroup', get_string('addparamgroup', 'bbbext_flexurl'));
        $this->mform->setType('flexurl_addparamgroup', PARAM_TEXT);
        $this->mform->registerNoSubmitButton('flexurl_addparamgroup');
        $this->mform->addElement('hidden', 'flexurl_paramcount');
        $this->mform->setType('flexurl_paramcount', PARAM_INT);
        $this->mform->setConstants(['flexurl_paramcount' => $paramcount]);
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
            if (!empty($data['flexurl_' . $paramtype])
                && clean_param_array($data['flexurl_' . $paramtype], $paramtypevalue, true) === false) {
                $errors["flexurl_{$paramtype}"] = get_string('invalidvalue', 'bbbext_flexurl');
            }
        }
        return $errors;
    }
}
