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
 * PHPUnit data generator tests.
 *
 * @package    mod_data
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * PHPUnit data generator testcase.
 *
 * @package    mod_data
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_data_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('data'));

        $course = $this->getDataGenerator()->create_course();

        /** @var mod_data_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');
        $this->assertInstanceOf('mod_data_generator', $generator);
        $this->assertEquals('data', $generator->get_modulename());

        $generator->create_instance(array('course' => $course->id));
        $generator->create_instance(array('course' => $course->id));
        $data = $generator->create_instance(array('course' => $course->id));
        $this->assertEquals(3, $DB->count_records('data'));

        $cm = get_coursemodule_from_instance('data', $data->id);
        $this->assertEquals($data->id, $cm->instance);
        $this->assertEquals('data', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($data->cmid, $context->instanceid);

        // test gradebook integration using low level DB access - DO NOT USE IN PLUGIN CODE!
        $data = $generator->create_instance(array('course' => $course->id, 'assessed' => 1, 'scale' => 100));
        $gitem = $DB->get_record('grade_items', array('courseid' => $course->id, 'itemtype' => 'mod',
                'itemmodule' => 'data', 'iteminstance' => $data->id));
        $this->assertNotEmpty($gitem);
        $this->assertEquals(100, $gitem->grademax);
        $this->assertEquals(0, $gitem->grademin);
        $this->assertEquals(GRADE_TYPE_VALUE, $gitem->gradetype);
    }

    public function test_create_field() {
        global $DB;

        $this->resetAfterTest(true);

        $this->setAdminUser();
        $this->assertEquals(0, $DB->count_records('data'));

        $course = $this->getDataGenerator()->create_course();

        /** @var mod_data_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');
        $this->assertInstanceOf('mod_data_generator', $generator);
        $this->assertEquals('data', $generator->get_modulename());

        $data = $generator->create_instance(array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('data'));

        $cm = get_coursemodule_from_instance('data', $data->id);
        $this->assertEquals($data->id, $cm->instance);
        $this->assertEquals('data', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($data->cmid, $context->instanceid);

        $fieldtypes = array('checkbox', 'date', 'menu', 'multimenu', 'number', 'radiobutton', 'text', 'textarea', 'url');

        $count = 1;

        // Creating test Fields with default parameter values.
        foreach ($fieldtypes as $fieldtype) {

            // Creating variables dynamically.
            $fieldname = 'field-' . $count;
            $record = new StdClass();
            $record->name = $fieldname;
            $record->type = $fieldtype;

            ${$fieldname} = $this->getDataGenerator()->get_plugin_generator('mod_data')->create_field($record, $data);

            $this->assertInstanceOf('data_field_' . $fieldtype, ${$fieldname});
            $count++;
        }

        $this->assertEquals(count($fieldtypes), $DB->count_records('data_fields', array('dataid' => $data->id)));

        $addtemplate = $DB->get_record('data', array('id' => $data->id), 'addtemplate');
        $addtemplate = $addtemplate->addtemplate;

        for ($i = 1; $i < $count; $i++) {
            $fieldname = 'field-' . $i;
            $this->assertTrue(strpos($addtemplate, '[[' . $fieldname . ']]') >= 0);
        }
    }

    public function test_create_entry() {
        global $DB;

        $this->resetAfterTest(true);

        $this->setAdminUser();
        $this->assertEquals(0, $DB->count_records('data'));

        $course = $this->getDataGenerator()->create_course();

        /** @var mod_data_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');
        $this->assertInstanceOf('mod_data_generator', $generator);
        $this->assertEquals('data', $generator->get_modulename());

        $data = $generator->create_instance(array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('data'));

        $cm = get_coursemodule_from_instance('data', $data->id);
        $this->assertEquals($data->id, $cm->instance);
        $this->assertEquals('data', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($data->cmid, $context->instanceid);

        $fieldtypes = array('checkbox', 'date', 'menu', 'multimenu', 'number', 'radiobutton', 'text', 'textarea', 'url');

        $count = 1;

        // Creating test Fields with default parameter values.
        foreach ($fieldtypes as $fieldtype) {

            // Creating variables dynamically.
            $fieldname = 'field-' . $count;
            $record = new StdClass();
            $record->name = $fieldname;
            $record->type = $fieldtype;

            ${$fieldname} = $this->getDataGenerator()->get_plugin_generator('mod_data')->create_field($record, $data);
            $this->assertInstanceOf('data_field_' . $fieldtype, ${$fieldname});
            $count++;
        }

        $this->assertEquals(count($fieldtypes), $DB->count_records('data_fields', array('dataid' => $data->id)));

        $fields = $DB->get_records('data_fields', array('dataid' => $data->id), 'id');

        $contents = array();
        $contents[] = array('one', 'two', 'three', 'four');
        $contents[] = '01-01-2037'; // It should be lower than 2038, to avoid failing on 32-bit windows.
        $contents[] = 'one';
        $contents[] = array('one', 'two', 'three', 'four');
        $contents[] = '12345';
        $contents[] = 'one';
        $contents[] = 'text for testing';
        $contents[] = '<p>text area testing<br /></p>';
        $contents[] = array('example.url', 'sampleurl');
        $count = 0;
        $fieldcontents = array();
        foreach ($fields as $fieldrecord) {
            $fieldcontents[$fieldrecord->id] = $contents[$count++];
        }

        $datarecordid = $this->getDataGenerator()->get_plugin_generator('mod_data')->create_entry($data, $fieldcontents);

        $this->assertEquals(1, $DB->count_records('data_records', array('dataid' => $data->id)));
        $this->assertEquals(count($contents), $DB->count_records('data_content', array('recordid' => $datarecordid)));
    }
}
