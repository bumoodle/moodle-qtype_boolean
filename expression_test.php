<?php
/*
 *      Binghamton University
 *      Project: Concept Model
 *      
 *      File: LogicExpression.tests.php
 *      Description: Tester for the LogicExpression class.
 *      
 *      Author: Kyle Temkin <kyle@ktemkin.com>
 *      
 *      Created: 2010-05-16
 */

require_once 'LogicExpression.class.php';

// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

//get a quick input blurb from the user
//echo "<center>";

if(isset($_POST['input']))
	$input = stripslashes($_POST['input']);
else
	$input = '';
	
if(isset($_POST['input2']))
	$input2 = stripslashes($_POST['input2']);
else 
	$input2 ='';
	
?>


<form method="POST"> 
<table><tr><td>
Expression 1:&nbsp;&nbsp;&nbsp;</td><td>
<textarea name="input" rows="2" cols="40">
<?

//textarea = instant mess

if(isset($_POST['input']))
{
	echo stripslashes($_POST['input']);
}



?></textarea></td></tr><tr><td>
Expression 2:&nbsp;&nbsp;&nbsp;</td><td>
<textarea name="input2" rows="2" cols="40">
<?

//textarea = instant mess

if(isset($_POST['input2']))
{
	echo stripslashes($_POST['input2']);
}
?>
</textarea>

</td></tr></table>
	<br />

	<input type="submit" value="Submit" />
</form>

<br/><br />

<?php

$input = strip_whitespace($input);

function strip_whitespace($str)
{
	$str = str_replace("\t", '', $str);
	$str = str_replace("\n", '', $str);
	return str_replace(' ', '', $str);
}

if(!empty($input))
{
	try
	{
		echo '<table cellpadding="5"><tr><td>Equivalent?</td><td><strong>';
		
		$a = new LogicExpression($input);
		$b = new LogicExpression($input2);

		if($a->equivalent_to($b))
			echo '<font color="green">YES</font>';
		else
			echo '<font color="red">NO</font>';
	
		echo '</strong></td></tr>';	
			
		$a = new ShapedLogicExpression($input);
		$b = new ShapedLogicExpression($input2);
	
		echo '<tr><td>Same form?</td><td><strong>';
		
		if($a->equivalent_to($b))
			echo '<font color="green">YES</font>';
		else
			echo '<font color="red">NO</font>';
			
		echo '<tr><td>SOP?</td><td><strong>';
		
		if($a->is_sum_of_products())
			echo '<font color="green">YES</font>';
		else
			echo '<font color="red">NO</font>';
			
		echo ', ';

		if($b->is_sum_of_products())
			echo '<font color="green">YES</font>';
		else
			echo '<font color="red">NO</font>';
		
		echo '<tr><td>POS?</td><td><strong>';
		
			if($a->is_product_of_sums())
			echo '<font color="green">YES</font>';
		else
			echo '<font color="red">NO</font>';
			
		echo ', ';

		if($b->is_product_of_sums())
			echo '<font color="green">YES</font>';
		else
			echo '<font color="red">NO</font>';
		
		
		echo '</strong></td></tr><tr><td>Gates (with inverters):</td><td>';
		
		echo $a->gate_count(true).", ".$b->gate_count(true);
		
		echo '</td></tr><tr><td>Gates (without inverters):</td><td>';
		
		echo $a->gate_count(false).", ".$b->gate_count(false);
	
		
		echo '</td></tr></table><br/><br/>';

        echo '<div style="float:left">';

		echo "<b>Truth tables:</b><table cellpadding=\"10\"><tr><td><pre>";
		
		$a->print_truth_table($b->all_vars());
		
		echo '</pre></td><td><pre>';
		
		$b->print_truth_table($a->all_vars());
		
        echo '</pre></td></tr></table>';

		echo '</td></tr></table><br/><br/>';

        echo '</div><div style="float: left; padding-left: 30px;">';

        echo "<b>ESPRESSO-style PLAs:</b><table cellpadding=\"10\"><tr><td><pre>";
		
		$a->print_pla($b->all_vars());
		
		echo '</pre></td><td><pre>';
		
		$b->print_pla($a->all_vars());
		
        echo '</pre></td></tr></table>';

        echo '</div>';

	}
    catch(Exception $e)
	{
		echo "Sorry, couldn't understand your input.";
        echo $e->getMessage();
	}
}

//echo "</center>";

?>


