<?php

// Boolean Question Type
// (C) Binghamton University 2011
//
// author: Kyle Temkin <ktemkin@binghamton.edu>
//
// Modified from Moodle Core Code

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the editing form for the shortanswer question type.
 *
 * @package   qtype_boolean
 * @copyright 2011 Binghamton University
 * @author    Kyle Temkin <ktemkin@binghamton.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * shortanswer editing form definition.
 */
class qtype_boolean_edit_form extends question_edit_form 
{
    
    

    function get_per_answer_fields(&$mform, $label, $gradeoptions, &$repeatedoptions, &$answersoption) 
    {
        
        
        $repeated = array();
        
        //set up the per-option answers
        $repeated[] =& $mform->createElement('header', 'answerhdr', $label);
        $repeated[] =& $mform->createElement('text', 'answer', get_string('defaultfunction', 'qtype_boolean'), array('size' => 80));
        $repeated[] =& $mform->createElement('select', 'fraction', get_string('grade'), $gradeoptions);
        $repeated[] =& $mform->createElement('editor', 'feedback', get_string('feedback', 'quiz'),array('rows' => 5), $this->editoroptions);
        
        
        
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;   
    }
    
    
    
    
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    function definition_inner(&$mform) 
    {
        global $CFG;
        
        $creategrades = get_grade_options();
        

        $mform->addElement('header', 'gradeoptions', get_string('booleanoptions', 'qtype_boolean'));
        
        //allow a limit to be placed on the amount of gates the user can use
        $grouparray[] =& $mform->createElement('advcheckbox', 'limitgates', get_string('limitgates', 'qtype_boolean'), '&nbsp;&nbsp;'.get_string('limitgatesstart', 'qtype_boolean'), array("group" => ""), array('0', '1'));
        $grouparray[] =& $mform->createElement('text', 'gate_limit', '10', array('size' => '3'));
        $grouparray[] =& $mform->createElement('static', 'limitgatesend', '', get_string('limitgatesend', 'qtype_boolean'));
        $mform->addGroup($grouparray, 'limitgatesgroup', get_string('limitgates', 'qtype_boolean'), array(''), false);
        
        //allow the option for inverters to be absorbed into gates ("free inverters")
        $mform->addElement('advcheckbox', 'freeinverters', '', '&nbsp;&nbsp;'.get_string('freeinverters_edit', 'qtype_boolean'), array("group" => ""), array('0', '1'));
        
        //input method
        $formoptions = array('expr' => get_string('exprinput', 'qtype_boolean'), 'schema' => get_string('schemainput', 'qtype_boolean'), 'advschema' => get_string('advschemainput', 'qtype_boolean'));
        $mform->addElement('select', 'inputmethod', get_string('inputmethod', 'qtype_boolean'), $formoptions);
        
        //input form
        $formoptions = array('loose' => get_string('looseform', 'qtype_boolean'), 'strict' => get_string('strictform', 'qtype_boolean'), 'sop' => get_string('sopform', 'qtype_boolean'), 'pos' => get_string('posform', 'qtype_boolean'));
        $mform->addElement('select', 'answerform', get_string('answerform', 'qtype_boolean'), $formoptions);
        
                
        $evaluator = $CFG->wwwroot .'/question/type/boolean/expression_test.php';
        $mform->addElement('html', '<div class="fitem"><div class="fitemtitle">'.get_string('instrassist', 'qtype_boolean').'</div>');
        $mform->addElement('html', '<div class="felement"><span><a href="'.$evaluator.'" target="_blank">'.get_string('exprchecker', 'qtype_boolean').'</a>&nbsp; &nbsp; <small>('.get_string('newwindow', 'qtype_boolean').')</small></span></div></div>');
        
        //allow more than one boolean expression as an answer
        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_boolean', '{no}'), $creategrades->gradeoptions, 2, 2);

        //add settings for multiple-try modes
        $this->add_interactive_settings();
    }

    function data_preprocessing($question) 
    {
        if (isset($question->options))
        {
            $answers = $question->options->answers;
            $answers_ids = array();
            
            if (count($answers)) 
            {
                $key = 0;
                foreach ($answers as $answer){
                    $answers_ids[] = $answer->id;
                    $default_values['answer['.$key.']'] = $answer->answer;
                    $default_values['fraction['.$key.']'] = $answer->fraction;
                    $default_values['feedback['.$key.']'] = array();

                    // prepare feedback editor to display files in draft area
                    $draftid_editor = file_get_submitted_draft_itemid('feedback['.$key.']');
                    $default_values['feedback['.$key.']']['text'] = file_prepare_draft_area(
                        $draftid_editor,       // draftid
                        $this->context->id,    // context
                        'question',   // component
                        'answerfeedback',             // filarea
                        !empty($answer->id)?(int)$answer->id:null, // itemid
                        $this->fileoptions,    // options
                        $answer->feedback      // text
                    );
                    $default_values['feedback['.$key.']']['itemid'] = $draftid_editor;
                    // prepare files code block ends

                    $default_values['feedback['.$key.']']['format'] = $answer->feedbackformat;
                    $key++;
                }
                
                
            }
            //$default_values['usecase'] = $question->options->usecase;
            $question = (object)((array)$question + $default_values);
        }
        return $question;
    }
    
    function validation($data, $files) 
    {
        
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $answercount = 0;
        $maxgrade = false;
        
        
        if($data['limitgates']=='1' && !is_numeric($data['gate_limit']))
            $errors['limitgatesgroup'] = get_string('gatelimitnumeric', 'qtype_boolean');
        else if ($data['limitgates']=='1' && intval($data['gate_limit'] <= 0))
            $errors['limitgatesgroup'] = get_string('gatelimitpositive', 'qtype_boolean');
            
        
        //for each of the supplied answer
        foreach ($answers as $key => $answer) 
        {
            //trim post/preceeding whitespcae
            $trimmedanswer = trim($answer);
            
            //if an answer was provided
            if ($trimmedanswer !== '')
            {
                //count it
                $answercount++;

                //if this grade counts as 100%
                if ($data['fraction'][$key] == 1)
                {
                    //set the maxgrade variable, which indicates that there's at least
                    //one full-credit answer  
                    $maxgrade = true;
                }
                    
            } 
            //otherwise, 
            else if ($data['fraction'][$key] != 0 || !html_is_blank($data['feedback'][$key]['text'])) 
            {
                $errors["answer[$key]"] = get_string('answermustbegiven', 'qtype_shortanswer');
                $answercount++;
            }
        }
        
        if ($answercount==0)
            $errors['answer[0]'] = get_string('notenoughanswers', 'quiz', 1);
        
        if ($maxgrade == false) 
            $errors['fraction[0]'] = get_string('fractionsnomax', 'question');

        return $errors;
    }
    function qtype() 
    {
        return 'boolean';
    }
}
