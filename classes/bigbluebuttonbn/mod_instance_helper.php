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
 * Class defining a way to deal with instance save/update/delete in extension
 *
 * @package   bbbext_flexurl
 * @copyright 2023 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Laurent David (laurent@call-learning.fr)
 */
class mod_instance_helper extends \mod_bigbluebuttonbn\local\extension\mod_instance_helper {
    /**
     * This is the name of the table that will be used to store additional data for the instance.
     */
    const SUBPLUGIN_TABLE = 'bbbext_flexurl';

    /**
     * Runs any processes that must run before a bigbluebuttonbn insert/update.
     *
     * @param stdClass $bigbluebuttonbn BigBlueButtonBN form data
     **/
    public function add_instance(stdClass $bigbluebuttonbn) {
        $this->sync_additional_params($bigbluebuttonbn);
    }

    /**
     * Make sure that the bbbext_flexurl has the right parameters (and not more)
     *
     * @param stdClass $bigbluebuttonbn
     * @return void
     */
    private function sync_additional_params(stdClass $bigbluebuttonbn): void {
        global $DB;
        // Retrieve existing parameters from the database.
        $existingparams = $DB->get_records(self::SUBPLUGIN_TABLE, ['bigbluebuttonbnid' => $bigbluebuttonbn->id]);
        // Checks first.
        $count = $bigbluebuttonbn->flexurl_paramcount ?? 0;
        foreach (utils::PARAM_TYPES as $type => $paramtype) {
            if (!isset($bigbluebuttonbn->{'flexurl_' . $type})) {
                // If the parameters were deleted, the deletion must be synced in the DB.
                if (!empty($existingparams)) {
                    continue;
                }
                return;
            }
            if ($count != count($bigbluebuttonbn->{'flexurl_' . $type})) {
                debugging('FlexURL : The number of ' . $type . ' does not match the number of parameters.');
                return;
            }
            if (clean_param_array($bigbluebuttonbn->{'flexurl_' . $type}, $paramtype, true) !=
                $bigbluebuttonbn->{'flexurl_' . $type}) {
                debugging('FlexURL : The ' . $type . ' contains invalid value.');
                return;
            }
        }
        // Then sync.
        // First delete everything related to this module.
        $DB->delete_records(self::SUBPLUGIN_TABLE, ['bigbluebuttonbnid' => $bigbluebuttonbn->id]);

        for ($index = 0; $index < $count; $index++) {
            $queryfields = [];
            foreach (array_keys(utils::PARAM_TYPES) as $type) {
                $queryfields[$type] = $bigbluebuttonbn->{'flexurl_' . $type}[$index];
            }
            $queryfields['bigbluebuttonbnid'] = $bigbluebuttonbn->id;
            $DB->insert_record(self::SUBPLUGIN_TABLE, (object) $queryfields);
        }
    }

    /**
     * Runs any processes that must be run after a bigbluebuttonbn insert/update.
     *
     * @param stdClass $bigbluebuttonbn BigBlueButtonBN form data
     **/
    public function update_instance(stdClass $bigbluebuttonbn): void {
        $this->sync_additional_params($bigbluebuttonbn);
    }

    /**
     * Runs any processes that must be run after a bigbluebuttonbn delete.
     *
     * @param int $id
     */
    public function delete_instance(int $id): void {
        global $DB;
        $DB->delete_records(self::SUBPLUGIN_TABLE, [
            'bigbluebuttonbnid' => $id,
        ]);
    }

    /**
     * Get any join table name that is used to store additional data for the instance.
     *
     * @return array
     */
    public function get_join_tables(): array {
        return []; // We don't make the join here as we have several values per instance.
    }
}
