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
 * Strings for component 'qtype_boolean', language 'en', branch 'EECE_AT_BU'
 *
 * @package   qtype_boolean
 * @copyright 2011 Binghamton University
 * @author    Kyle Temkin <ktemkin@binghamton.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

$string['addingboolean'] = 'Adding a Boolean Logic question';
$string['addmoreanswerblanks'] = 'Blanks for {no} More Answers';
$string['answermustbegiven'] = 'You must enter an answer if there is a grade or feedback.';
$string['answerno'] = 'Graded Expression {$a}';
$string['editingboolean'] = 'Editing a Boolean Logic question';
$string['filloutoneanswer'] = 'You must provide at least one possible expression. Expressions left blank will not be used. The first matching expression will be used to determine the score and feedback.';
$string['boolean'] = 'Boolean Expression';
$string['pluginname'] = 'Boolean Expression';
$string['boolean_help'] = 'In response to a question (that may include a image) the respondent constructs a boolean expression. There may be a several model expressions, each with a different grade. ';
$string['boolean_link'] = 'question/type/shortanswer';
$string['booleansummary'] = 'Allows a response of a simple boolean expression, which is graded by comparing form and function to several model expressions.';

$string['defaultfunction'] = 'Expression:';
$string['function'] = 'Expression: {$a}';
$string['limitgates'] = 'Gate usage';
$string['limitgatesstart'] = 'Limit the user to no more than&nbsp;&nbsp;';
$string['limitgatesend'] = '&nbsp;&nbsp;gates.';

$string['invalidexpression'] = '<strong>The system was unable to interpet your expression.</strong><br/> Please use one of the standard formats discussed in class.';
$string['invalidschematic'] = '<strong>The system was unable to interpet your schematic.</strong><br/> Check your schematic and try again.';

$string['booleanoptions'] = 'Limits and Interpretation';

$string['gatelimitnumeric'] = 'You must provide a numeric gate limit.';
$string['gatelimitpositive'] = 'You must provide a positive (greater than zero) gate limit.';
$string['freeinverters_edit'] = 'Don\'t count inverters in the above count.';

$string['gatelimited'] = 'Use no more than {$a->limit} gates';
$string['freeinverters_quiz'] = ', not counting inverters.';
$string['normalinverters_quiz'] = ', counting inverters.';
$string['toomanygates'] = '<strong>You used more gates than allowed!</strong> Check your expression and try again.';

$string['answerform'] = 'Answer Form';
$string['looseform'] = 'Accept any answer which is functionally equivalent.';
$string['strictform'] = 'Only accept answers which match the answer\'s form.';
$string['sopform'] = 'Only accept answers in Sum of Products form.';
$string['posform'] = 'Only accept answers in Product of Sums form.';

$string['instrassist'] = 'Instructor Assistance';
$string['exprchecker'] = 'Expression Checker';
$string['newwindow'] = 'Opens in new window.';

$string['inputmethod'] = 'Input Method';
$string['exprinput'] = 'Boolean Algebraic Expression (e.g. \'a+b\')';
$string['schemainput'] = 'Basic Gate Schematic';
$string['advschemainput'] = 'Advanced Gate Schematic';
