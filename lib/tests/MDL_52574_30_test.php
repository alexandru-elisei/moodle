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

/**
 * File containing the tests for issue MDL-52574-30.
 *
 * @package    MDL-52574-30
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/accesslib.php');

/**
 * MDL-52574-30 test case.
 *
 * @package    MDL-52574-30
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @group      MDL_52574_30
 */
class MDL_52574_30_testcase extends advanced_testcase {

    /**
     * Tidy up open files that may be left open.
     */
    protected function tearDown() {
        gc_collect_cycles();
    }

    /**
     * Tests if downloading users succeeds when no $sort fields are specified
     * and the requested fields are not part of the default $sort fields.
     * The default $sort fields are lastname, firstname, id and are returned
     * by users_order_by_sql().
     */
    public function test_user_download_without_sort_fields() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $roleid);

        $fields = 'u.maildigest, u.email';
        $usersassigned = get_role_users($roleid, $context, false, $fields);

        // The array from get_role_users() is indexed by the first requested field.
        $this->assertArrayHasKey($user->maildigest, $usersassigned);
        $this->assertEquals($user->maildigest, $usersassigned[$user->maildigest]->maildigest);
    }
}
