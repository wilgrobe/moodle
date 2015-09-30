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
 * The mod_scorm sco scored event.
 *
 * @package    mod_scorm
 * @author     Will Groberg
 * @copyright  2015 Walt Disney Company
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_scorm sco scored event class.
 *
 * @property-read array $other {
 *      Extra information about event properties.
 *
 *      - int instanceid: (optional) Instance id of the scorm activity.
 * }
 *
 * @package    mod_scorm
 * @since      Moodle 2.9
 * @author     Will Groberg
 * @copyright  2015 Walt Disney Company
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class sco_scored extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'scorm_scoes_track';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' scored the sco with id '$this->objectid' for the scorm with " .
            "course module id '$this->contextinstanceid'.";
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventscoscored', 'mod_scorm');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/scorm/player.php', array('id' => $this->contextinstanceid, 'scoid' => $this->objectid));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
    }
}
