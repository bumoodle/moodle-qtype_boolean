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
 * Short answer question definition class.
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/question/type/shortanswer/questiontype.php');

/**
 * Enumeration which describes how Boolean logic function should be entered by the user.
 */
abstract class qtype_boolean_input_method
{
    /**
     * Allows the user to enter a Boolean expression via a JavaScript schematic editor.
     */
    const METHOD_SCHEMATIC = 'schema';

    /**
     * Allows the user to enter a Boolean expression via a JavaScript schematic edtior, using advanced gates (XOR, XNOR, NAND/NOR, etc.)
     */
    const METHOD_ADVANCED_SCHEMATIC = 'advschema';

    /**
     * Allows the user to enter a Boolean expression using Boolean Algebra syntax.
     */
    const METHOD_BOOLEAN_ALGEBRA = 'classic';

}

/**
 * Object which represents an instance (not an attempt) at a Boolean question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_boolean_question extends qtype_shortanswer_question implements question_response_answer_comparer 
{

    public $inputmethod;
    public $answerform;
    public $limitgates;
    public $gate_limit;
    public $freeinverters;

    /**
     * Returns true iff the active question is a schematic question (e.g. has a schematic entry mode selected.)
     */
    function is_schematic()
    {
        return ($this->inputmethod == qtype_boolean_input_method::METHOD_SCHEMATIC || $this-> inputmethod == qtype_boolean_input_method::METHOD_ADVANCED_SCHEMATIC);
    }

    /**
     * Returns true iff the given response is gradeable.
     * 
     * @param array $response   The HTML response data.
     * @return bool             True iff the response is gradeable.
     */
    function is_complete_response(array $response)
    {
        try
        {
            //if this is a schematic, attempt to parse the schematic using JSON
            if($this->is_schematic())
            {
                $schema = LogicSchematic::from_JSON($response['answer']);
                return (bool)$schema->to_expression(false);                
            }
            else
            {
                //attempt to convert the logic into a LogicExpression object
                $logic = new LogicExpression($response['answer']);
            }

            //if creation succeeds, then we have a valid expression
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }

    }

    function summarise_response(array $response)
    {
        try
        {
            //if this is a schematic, attempt to parse the schematic using JSON
            if($this->is_schematic())
            {
                $schema = LogicSchematic::from_JSON($response['answer']);
                return $schema->to_expression(false);                
            }
            else
            {
                return $response['answer'];
            }

        }
        catch(Exception $e)
        {
            return $response['answer'];
        }


    }

    function get_validation_error(array $response)
    {
        //if we have an invalid format, let the user know
        if(!$this->is_complete_response($response) && !empty($response['answer']))
        {
            //vary the message according to the input method
            if($this->is_schematic())
                return get_string('invalidschematic', 'qtype_boolean');
            else
                return get_string('invalidexpression', 'qtype_boolean');
        }
        //otherwise, delegate to the base class
        else
        {
            parent::get_validation_error($response);
        }
    }

    function compare_response_with_answer(array $response, question_answer $answer)
    {
        try
        {

            //strip away the container stores the response and answer
            $response = $response['answer'];
            $answer = $answer->answer;

            //if our question is of the schematic type, convert it to an expression first
            if($this->inputmethod == 'schema' || $this->inputmethod == 'advschema')
            {
                //convert the JSON-encoded schematic to a string expression
                $schema = LogicSchematic::from_JSON($response);
                $response = $schema->to_expression(false);
            }
        
        
            //convert the response and answer to logic expressions,
            //based on the question's answer form settings
            switch($this->answerform)
            {
                case 'strict':
                    
                    //create shaped 'strict' logic expressions
                    $answer_le = new ShapedLogicExpression($answer);
                    $response_le = new ShapedLogicExpression($response);
                    break;
                    
                case 'sop':
                    
                    //if the given response isn't in sum of products form, immediately disqualify
                    $response_le = new ShapedLogicExpression($response);
                    if(!$response_le->is_sum_of_products())
                        return false;
                    
                    //and, otherwise, allow any valid expression
                    $answer_le = new LogicExpression($answer);
                    $response_le = new LogicExpression($response);
                    break;
                
                
                case 'pos':
                    
                    //if the given response isn't in sum of products form, immediately disqualify
                    $response_le = new ShapedLogicExpression($response);
                    if(!$response_le->is_product_of_sums())
                        return false;
                    
                    //and, otherwise, allow any valid expression
                    $answer_le = new LogicExpression($answer);
                    $response_le = new LogicExpression($response);
                    break;
                    
                    
                //'loose'
                default:
                    $answer_le = new LogicExpression($answer);
                    $response_le = new LogicExpression($response);
                    break;
            
            }         
            
            //if the option is selected, check to see that the response is within the gate limit
            if($this->limitgates)
            {
                    //if the user has used too many gates, their answer is wrong
                    if($response_le->gate_count(!$this->freeinverters) > $this->gate_limit)
                        return false;
            }
                        
            //and test their equivalence
            return $answer_le->equivalent_to($response_le);
        }
        catch(Exception $e)
        {
            return false;
        }
        
    }

    /**
     * Override the cleanup behavior of the shortanswer question type.
     * TODO: eventually remove double-parenthesis and etc?
     */
    public function clean_response($answer)
    {
        return $answer;
    }

}


