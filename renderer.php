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
 * Short answer question renderer class.
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/question/type/boolean/question.php');



/**
 * Generates the output for short answer questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_boolean_renderer extends qtype_renderer 
{
    private function path()
    {
        global $CFG;
        return '/question/type/boolean';
    }

    /**
     * Specifies the HTML/CSS/JS which should appear in the page's header:
     * If required, set up the WireIt schematic editor in the page's header.  
     */
    public function head_code(question_attempt $qa)
    {
        //get a reference to the current page object
        global $PAGE;

        //if we're using schematic mode, load the requried CSS
        if($qa->get_question()->is_schematic())
        {
            //load in the CSS files required for WireIt
            $PAGE->requires->css($this->path().'/scripts/inputex/css/inputEx.css');
            $PAGE->requires->css($this->path().'/scripts/accordionview/assets/skins/sam/accordionview.css');
            $PAGE->requires->css($this->path().'/scripts/css/WireIt.css');
            $PAGE->requires->css($this->path().'/scripts/css/WireItEditor.css');
        }

        //return an empty string, as we don't need to add any other HTML
        return '';
    }


    /**
     * Print the actual question entry form for this question.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) 
    {
        //if the input method for this question is schmatic, use the schematic entry mode 
        if($qa->get_question()->is_schematic())
            return $this->formulation_and_controls_schematic_entry($qa, $options, $qa->get_question()->inputmethod == qtype_boolean_input_method::METHOD_ADVANCED_SCHEMATIC);
        //otherwise, use the normal expression entry mode
        else
            return $this->formulation_and_controls_expression_entry($qa, $options);
    }



    /**
     * Print the actual question entry form for this question.
     */
     private function formulation_and_controls_schematic_entry(question_attempt $qa, question_display_options $options, $adv_gates = false) 
     {
        //get a reference to the current page object, which is used to load javascripts
        global $PAGE;

        //extract the name and previous answer from the question's state
        $name = $qa->get_qt_field_name('answer'); 
        $innervalue = $qa->get_last_qt_var('answer');

        //require YUI modules
        $PAGE->requires->yui2_lib('utilities');
        $PAGE->requires->yui2_lib('resize');
        $PAGE->requires->yui2_lib('layout');
        $PAGE->requires->yui2_lib('container');
        $PAGE->requires->yui2_lib('button');
        $PAGE->requires->yui2_lib('tabview');
        $PAGE->requires->yui2_lib('yuiloader');
        $PAGE->requires->yui2_lib('json');

        //WireIt and dependencies
        $PAGE->requires->js($this->path().'/scripts/accordionview/accordionview-min.js');
        $PAGE->requires->js($this->path().'/scripts/sch/excanvas.js');
        $PAGE->requires->js($this->path().'/scripts/inputex/build/inputex-min.js');
        $PAGE->requires->js($this->path().'/scripts/sch/wireit.js');
        $PAGE->requires->js($this->path().'/scripts/sch/logicGates.js.php?target='.$name.'&advgates='.$adv_gates);


        //code to restore the previous schematic
        $PAGE->requires->js_init_code('window.restore_value = \''.$innervalue.'\';');

        //initialization code
        $PAGE->requires->js_init_code('wiring_init()', true);

        //question text
        $result = html_writer::tag('div', $qa->get_question()->format_questiontext($qa), array('class' => 'qtext'));

        //core editor canvas
        //FIXME: what happens if there are multiple? e.g. replace schema with something unique
        $result .= html_writer::start_tag('div', array('id' => 'schema'));
            
        //TODO: uniquefiy ids
        $result .= html_writer::tag('div', '', array('id' => 'top'));
        $result .= html_writer::tag('div', '', array('id' => 'left'));

        //right hand panels
        $result .= html_writer::start_tag('div', array('id' => 'right'));
        $result .= html_writer::tag('ul', '<li></li><li></li><li></li>', array('id' => 'accordionView'));
        $result .= html_writer::end_tag('div'); 

        //main canvas
        $result .= html_writer::tag('div', '', array('id' => 'center'));

        //end the canvas
        $result .= html_writer::end_tag('div');

        //and create the hidden input which will return the result
        $result .= html_writer::empty_tag('input', array('id' => $name, 'type' => 'hidden', 'name' => $name,  'value' => $innervalue));

        //if the user's last response was invalid, specify why:
        if ($qa->get_state() == question_state::$invalid) 
            $result .= html_writer::nonempty_tag('div', $qa->get_question()->get_validation_error(array('answer' => $innervalue)), array('class' => 'validationerror'));

        return $result;

     }
 

    /**
    * Print the actual question entry form for this question. (Expression mode.)
     */
    private function formulation_and_controls_expression_entry(question_attempt $qa, question_display_options $options) 
    {
        //get a reference to the question being asked
        $question = $qa->get_question();

        //and get the user's most recent response
        $currentanswer = $qa->get_last_qt_var('answer');

        //get the field name for the answer input
        $inputname = $qa->get_qt_field_name('answer');

        //start specifying the attributes of the input box
        $inputattributes = array
            (
                'type' => 'text',
                'name' => $inputname,
                'value' => $currentanswer,
                'id' => $inputname,
                'size' => 80,
                'class' => 'codeinput ',
            );

        //if this question is in a read-only mode, do not allow modifications 
        if ($options->readonly) 
        {
            $inputattributes['readonly'] = 'readonly';
        }

        //assume an empty feedback image, unless otherwise is specified
        $feedbackimg = '';
        
        //if the question's correctness should be displayed, display it 
        if ($options->correctness) 
        {
            //find the answer object that matches the user's response
            $answer = $question->get_matching_answer(array('answer' => $currentanswer));

            //if the user matched an answer, use its fraction as the correctness value
            if ($answer) 
                $fraction = $answer->fraction;
            //otherwise, the user has the question wrong
            else
                $fraction = 0;
            
            //add the appropriate class, which allows CSS decoration of the form field according to the its correctness
            $inputattributes['class'] = $this->feedback_class($fraction);

            //and add a feedback image afterwards
            $feedbackimg = $this->feedback_image($fraction);
        }

        //get the formatted value of the question's text
        $questiontext = $question->format_questiontext($qa);


        //assume the question is _not _ in placeholder mode
        //(which inlines the input field)
        $placeholder = false;

        //if the question contains a large set of underscores, use them as a blank for the fill in the blank
        if (preg_match('/_____+/', $questiontext, $matches)) 
        {
            //set the blank as the placeholder
            $placeholder = $matches[0];

            //and automatically adjust the size of the input box to match the placeholder's size
            $inputattributes['size'] = round(strlen($placeholder) * 1.1);
        }

        //create the actual input box
        $input = html_writer::empty_tag('input', $inputattributes) . $feedbackimg;

        //and, if we're in placeholder mode, inline it
        if ($placeholder) 
            $questiontext = substr_replace($questiontext, $input, strpos($questiontext, $placeholder), strlen($placeholder));

        //render the question text
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        //if we're _not_ in placeholder mode, append the input box to the question
        if (!$placeholder) 
        {
            $result .= html_writer::start_tag('div', array('class' => 'ablock'));
            $result .= get_string('function', 'qtype_boolean', html_writer::tag('div', $input, array('class' => 'answer')));
            $result .= html_writer::end_tag('div');
        }

        //if the user's last response was invalid, specify why:
        if ($qa->get_state() == question_state::$invalid) 
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' => $currentanswer)), array('class' => 'validationerror'));

        //and return the resultant HTML
        return $result;
    }

    public function specific_feedback(question_attempt $qa) {
        $question = $qa->get_question();

        $answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var('answer')));
        if (!$answer || !$answer->feedback) {
            return '';
        }

        return $question->format_text($answer->feedback, $answer->feedbackformat,
                $qa, 'question', 'answerfeedback', $answer->id);
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        $answer = $question->get_matching_answer($question->get_correct_response());
        if (!$answer) {
            return '';
        }

        return get_string('correctansweris', 'qtype_shortanswer', s($answer->answer));
    }
}
