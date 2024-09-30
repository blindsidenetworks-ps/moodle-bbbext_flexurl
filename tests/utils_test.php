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

namespace bbbext_bnurl;

use mod_bigbluebuttonbn\instance;

/**
 * BBB Utils tests class.
 *
 * @package   bbbext_bnurl
 * @copyright 2023 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Laurent David (laurent@call-learning.fr)
 * @coversDefaultClass  \bbbext_bnurl\utils
 */
final class utils_test extends \advanced_testcase {
    /**
     * @var \stdClass $bbb
     */
    protected $bbb;
    /**
     * @var \stdClass $course
     */
    protected $course;
    /**
     * @var \stdClass $user
     */
    protected $user;

    /**
     * Provider for get_fields_for_parameter
     *
     * @return array[]
     */
    public static function provider_get_fields_for_parameter(): array {
        return [
            'actitivityinfo' => [
                'activityinfo',
                ["activityinfo.id", "activityinfo.name", "activityinfo.url", "activityinfo.iconurl"], ],
            'courseinfo' => ['courseinfo',
                ["courseinfo.id", "courseinfo.fullname", "courseinfo.shortname", "courseinfo.idnumber", "courseinfo.summary",
                    "courseinfo.summaryformat", "courseinfo.startdate", "courseinfo.enddate", "courseinfo.visible",
                    "courseinfo.showactivitydates", "courseinfo.showcompletionconditions", "courseinfo.pdfexportfont",
                    "courseinfo.fullnamedisplay", "courseinfo.viewurl", "courseinfo.courseimage", "courseinfo.progress",
                    "courseinfo.hasprogress", "courseinfo.isfavourite", "courseinfo.hidden", "courseinfo.timeaccess",
                    "courseinfo.showshortname", "courseinfo.coursecategory", ], ],
            'user' => ['user', ['user.alternatename', 'user.email', 'user.firstname', 'user.firstnamephonetic', 'user.lastname',
                'user.lastnamephonetic', 'user.middlename', ], ],
        ];
    }

    /**
     * Provider for get_fields_for_parameter
     *
     * @return array[]
     */
    public static function provider_get_value_for_fields(): array {
        return [
            'actitivityinfo' => [
                ["activityinfo.name" => "BBB Activity",
                    "activityinfo.url" => "https://www.example.com/moodle/mod/bigbluebuttonbn/view.php",
                    "activityinfo.iconurl" => "https://www.example.com/moodle/theme/image.php/boost/bigbluebuttonbn",
                ], ],
            'courseinfo' => [
                [
                    "courseinfo.fullname" => "BBBCourse FULL",
                    "courseinfo.shortname" => "BBBCourse",
                    "courseinfo.idnumber" => "",
                    "courseinfo.summary" => "Test course 1",
                ], ],
            'user' => [[
                'user.email' => "",
                'user.firstname' => "BBB User FN",
                'user.lastname' => "BBB LN",
            ], ],
        ];
    }

    /**
     * Setup
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $datagenerator = $this->getDataGenerator();
        $this->course = $datagenerator->create_course(['fullname' => 'BBBCourse FULL', 'shortname' => 'BBBCourse']);
        $this->user = $datagenerator->create_user(['firstname' => 'BBB User FN', 'lastname' => 'BBB LN',
            'email' => 'bbb@blindsidenetworks.com', ]);
        $bbbgenerator = $datagenerator->get_plugin_generator('mod_bigbluebuttonbn');
        $this->bbb = $bbbgenerator->create_instance(['name' => 'BBB Activity', 'course' => $this->course->id]);
        set_config('available_info', 'user, courseinfo, activityinfo', 'bbbext_bnurl');
        $datagenerator->enrol_user($this->user->id, $this->course->id);
    }

    /**
     * Test get_options_for_parameters
     *
     * @covers \bbbext_bnurl\utils::get_options_for_parameters
     */
    public function test_get_options_for_parameters(): void {
        $this->setAdminUser(); // To get the email.
        $options = utils::get_options_for_parameters();
        $this->assertNotEmpty($options);
        $this->assertEquals(
            json_decode('{
    "%activityinfo.id%": "activityinfo.id",
    "%activityinfo.name%": "activityinfo.name",
    "%activityinfo.url%": "activityinfo.url",
    "%activityinfo.iconurl%": "activityinfo.iconurl",
    "%courseinfo.id%": "courseinfo.id",
    "%courseinfo.fullname%": "courseinfo.fullname",
    "%courseinfo.shortname%": "courseinfo.shortname",
    "%courseinfo.idnumber%": "courseinfo.idnumber",
    "%courseinfo.summary%": "courseinfo.summary",
    "%courseinfo.summaryformat%": "courseinfo.summaryformat",
    "%courseinfo.startdate%": "courseinfo.startdate",
    "%courseinfo.enddate%": "courseinfo.enddate",
    "%courseinfo.visible%": "courseinfo.visible",
    "%courseinfo.showactivitydates%": "courseinfo.showactivitydates",
    "%courseinfo.showcompletionconditions%": "courseinfo.showcompletionconditions",
    "%courseinfo.pdfexportfont%": "courseinfo.pdfexportfont",
    "%courseinfo.fullnamedisplay%": "courseinfo.fullnamedisplay",
    "%courseinfo.viewurl%": "courseinfo.viewurl",
    "%courseinfo.courseimage%": "courseinfo.courseimage",
    "%courseinfo.progress%": "courseinfo.progress",
    "%courseinfo.hasprogress%": "courseinfo.hasprogress",
    "%courseinfo.isfavourite%": "courseinfo.isfavourite",
    "%courseinfo.hidden%": "courseinfo.hidden",
    "%courseinfo.timeaccess%": "courseinfo.timeaccess",
    "%courseinfo.showshortname%": "courseinfo.showshortname",
    "%courseinfo.coursecategory%": "courseinfo.coursecategory",
    "%user.alternatename%": "user.alternatename",
    "%user.email%": "user.email",
    "%user.firstname%": "user.firstname",
    "%user.firstnamephonetic%": "user.firstnamephonetic",
    "%user.lastname%": "user.lastname",
    "%user.lastnamephonetic%": "user.lastnamephonetic",
    "%user.middlename%": "user.middlename"
}', true)
            , $options);
    }

    /**
     * Test get_fields_for_parameter
     *
     * @param string $paramtype
     * @param array $expected
     *
     * @covers       \bbbext_bnurl\utils::get_fields_for_parameter
     * @dataProvider provider_get_fields_for_parameter
     */
    public function test_get_fields_for_parameter(string $paramtype, array $expected): void {
        $this->setAdminUser(); // To get the email.
        $fields = utils::get_fields_for_parameter($paramtype);
        $this->assertNotEmpty($fields);
        $this->assertEquals($expected, array_keys($fields));
    }

    /**
     * Test get_value_for_field
     *
     * @param array $expected
     * @covers       \bbbext_bnurl\utils::get_value_for_field
     * @dataProvider provider_get_value_for_fields
     */
    public function test_get_value_for_field(array $expected): void {
        $instance = instance::get_from_instanceid($this->bbb->id);

        $this->setUser($this->user);
        foreach ($expected as $key => $expectedvalue) {
            $fieldvalue = utils::get_value_for_field($key, $instance);
            $this->assertStringContainsString($expectedvalue, $fieldvalue, "Field $key does not contain $expectedvalue");
        }
    }
}
