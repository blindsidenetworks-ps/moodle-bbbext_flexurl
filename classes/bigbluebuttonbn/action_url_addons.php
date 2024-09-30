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
use core_form\util;
use mod_bigbluebuttonbn\instance;

/**
 * A single action class to mutate the action URL.
 *
 * @package   bbbext_bnurl
 * @copyright 2023 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Laurent David (laurent@call-learning.fr)
 */
class action_url_addons extends \mod_bigbluebuttonbn\local\extension\action_url_addons {
    /**
     * Sample mutate the action URL.
     *
     *
     * @param string $action
     * @param array $data
     * @param array $metadata
     * @param int|null $instanceid
     * @return array associative array with the additional data and metadata (indexed by 'data' and
     * 'metadata' keys)
     */
    public function execute(string $action = '', array $data = [], array $metadata = [], ?int $instanceid = null): array {
        global $DB;
        if ($instanceid) {
            $instance = instance::get_from_instanceid($instanceid);
            $bnurlrecords = $DB->get_records(mod_instance_helper::SUBPLUGIN_TABLE, [
                'bigbluebuttonbnid' => $instanceid,
            ]);
            $eventtypes = array_flip(utils::ACTION_CODES);
            foreach ($bnurlrecords as $bnurlrecord) {
                if ($bnurlrecord->eventtype != utils::ACTION_CODES['all'] &&
                    $eventtypes[$bnurlrecord->eventtype] != $action) {
                    continue;
                }
                $data[$bnurlrecord->paramname] = utils::get_real_value($bnurlrecord->paramvalue, $instance);

            }
        }
        return ['data' => $data, 'metadata' => $metadata];
    }
}
