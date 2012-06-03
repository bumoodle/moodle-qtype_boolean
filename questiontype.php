<?php

// Boolean Question Type
// (C) Binghamton University 2011
//
// author: Kyle Temkin <ktemkin@binghamton.edu>
//
// Modified from Moodle Core Code

///////////////////
/// BOOLEAN ///
///////////////////

/// QUESTION TYPE CLASS //////////////////

///
/// This class contains some special features in order to make the
/// question type embeddable within a multianswer (cloze) question
///
/**
 * @package   qtype_boolean
 * @copyright 2011 Binghamton University
 * @author    Kyle Temkin <ktemkin@binghamton.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#require_once("$CFG->dirroot/question/type/questiontype.php");
require_once($CFG->dirroot.'/question/type/shortanswer/questiontype.php');
require_once("$CFG->dirroot/question/type/boolean/LogicExpression.class.php");
require_once("$CFG->dirroot/question/type/boolean/LogicSchematic.class.php");

/**
 * Defines the Boolean question type, 
 */
class qtype_boolean extends qtype_shortanswer
{
    /**
	 * Returns a list of the class fields which should be stored to the database.
	 */
    function extra_question_fields() 
    {
        return array('question_boolean', 'answers', 'gate_limit', 'limitgates', 'freeinverters', 'answerform', 'inputmethod');
    }

    /**
     * Returns the name of the database column which stores the question's unique ID number.
     */
    function questionid_column_name() 
    {   
        return 'question';
    }
}


