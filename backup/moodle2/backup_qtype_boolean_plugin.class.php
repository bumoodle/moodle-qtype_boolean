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
 * @package    moodlecore
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



/**
 * Provides the information to backup boolean questions.
 * 
 * This is a genericized class that really should be abstracted and the inherited from.
 * This will probably occur when BU branches off their own fork of Moodle.
 * 
 */
class backup_qtype_boolean_plugin extends backup_qtype_plugin
 {
    
    
    //~ktemkin
    //ease of use fields
    const base_name = 'boolean';
    const base_db_name =  'question_boolean';
    const base_extra_fields = 'answers|gate_limit|limitgates|freeinverters|answerform|inputmethod';
    const base_questionid_field = 'question';
    
    

    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() 
    {

        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, '../../qtype', self::base_name);

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // This qtype uses standard question_answers, add them here
        // to the tree before any other information that will use them
        $this->add_question_question_answers($pluginwrapper);

        //~ktemkin
        //get the extra fields from the ease of use field
        $extra_fields = explode('|', self::base_extra_fields);
        
        // Now create the qtype own structures [sic]
        $qtype = new backup_nested_element(self::base_name, array('id'), $extra_fields);

        // Now the own qtype tree
        $pluginwrapper->add_child($qtype);

        // set source to populate the data
        $qtype->set_source_table(self::base_db_name, array(self::base_questionid_field => backup::VAR_PARENTID));

        // don't need to annotate ids nor files [sic]

        return $plugin;
    }
}
