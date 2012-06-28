<?php
/*
 *      Binghamton University
 *      Project: Moodle Question Types for Digital Logic
 *
 *      File: LogicExpression.class.php
 *      Description: Class (and subclasses) which describe boolean logic circuit using the Abstract Syntax Tree model.
 *
 *      Author: Kyle Temkin <ktemkin@binghamton.edu>
 *
 *      Created: 2010-05-16
 *      Modified: 2011-06-09
 *      
 *      Production Changelog:
 *      
 *       6/27/11 - Fixed an issue in which expressions of the form (a^b)^(c^d) were incorrectly identified as malformed.
 *               - Added XOR gates to the order of precedence, between AND and OR, allowing a more convenient psuedocannonical form. 
 *       6/15/11 - Fixed an issue in which XOR gates were not counted in total gate counts.
 *       6/09/11 - Itinitial Moodle commit.
 *       5/16/11 - Created for the IQ Questioning System.
 *              
 */


if(!class_exists("InvalidArugumentException"))
{
    class InvalidArugumentException extends Exception {}    
}


/**
 * Evaluates, stores, and processes logical expressions.
 */
class LogicExpression
{
    //store/handle logic as a RPN (unambiguous, and thus non-parenthesized) queue
    protected $rpn;

    /**
     * Create a new LogicExpression.
     */
    public function __construct($input)
    {
        $input = strtolower($input);
        
        $this->fromInfix($input);
        $this->infix = $input;
    }

    /**
     * Returns the RPN value of this element.
     */
    public function getRPN()
    {
        return $this->rpn;
    }

    /**
     *  Modifies an infix logic expression string, converting postfix-negations (') to prefix-negations. (~).
     * 
     * @param string $infix The infix logic expression string.
     */
    private static function handle_tick($infix)
    {
        
        //three cases:

        //case 1: simple double tick
        $infix = str_replace("''", '', $infix);
        
        //case 2: simple operand and tick
        $infix = preg_replace("|([A-Za-z])'|", "~$1", $infix);

        //case 3: parenthetical operand and tick

        //find any right-paren which is followed by a tick
        $pos = strrpos($infix, ")'");

        //if we found one, try and replace it with the equivalent ~
        if($pos !== false)
        {
            //first, remove the tick
            $infix = substr($infix, 0, $pos) . ')' . substr($infix, $pos+2);

            $r_paren_count = 0;

            //iterate backwards through the string, looking for a matching l-paren
            for($i = $pos - 1; $i >= 0; --$i)
            {
                //if we run into an r-paren, count it; as it has to be closed before
                //we can find the match
                if($infix[$i]==')')
                ++$r_paren_count;

                if($infix[$i]=='(')
                {
                        

                    //if we've hit an L-paren without any R-parens,
                    //we've found the appropriate mate
                    if($r_paren_count===0)
                    {
                        //insert our standard not (~) before it
                        $infix = substr($infix, 0, $i) . '~' . substr($infix, $i);
                            
                        //and recurse to adjust any further instances
                        return self::handle_tick($infix);
                    }
                        
                    //otherwise, we've found the match to one of the R-parens we've hit
                    //decrease the count of unmatched right-parenthesis
                    else
                    {
                        --$r_paren_count;
                    }
                        
                }
                    
            }

        }
        
        return $infix;
    }

    /**
     * Populates the internal RPN que from an infix expression string.
     * 
     * $infix   string  The logic expression to parse.
     */
    public function fromInfix($infix)
    {
        $infix = self::remove_whitespace($infix);
        

        //handle postfix ticks
        $infix = self::handle_tick($infix);

        //$infix = preg_replace("|(\(.+?\))'|", "~$1", $infix);


        //clear the current expression and operator stack
        $this->rpn = array();
        $ops = array();

        //iterate through the array
        $chars = preg_split('//', $infix, -1);
        foreach($chars as $i => $char)
        {
            $orig_char = $char;
                
            //push bools/variables directly into the output
            if(self::isBoolean($char) || !self::isOperator($char))
            {
                if(self::isBoolean($char))
                {
                    array_push($this->rpn, $char);
                }
                else
                {

                    //if it's not a boolean or an operator, it is automatically
                    //considered a variable and is pushed to the output queue
                        
                    //skip invalid characters
                    if(!ctype_alpha($char))
                        continue;
                        
                    array_push($this->rpn, $char);
                }

                //handle the case that we just completed the value for the unary NOT
                if(!empty($ops))
                    if($ops[count($ops)-1] == "~")
                        array_push($this->rpn, array_pop($ops));

                
                //handle implied AND (i.e. AB)
                if($i + 1 >= count($chars) - 1)
                    continue;
                
                
                //if the next character is another identifier, a literal, the unary not, or 
                //an open paren '(', then we have an implied AND
                if($chars[$i+1]=="(" || $chars[$i + 1] == '~' || $chars[$i + 1] == '!' || self::isBoolean($chars[$i+1]) || !self::isOperator($chars[$i+1]))
                    $char = '*';
                else 
                    continue;
            }

            //we now know what we have is an operator
            assert(self::isOperator($char));

            //highest priority goes to the logical NOT
            if($char=='~' || $char=='!')
            {
                array_push($ops, "~");
                continue;
            }           
                
            //next highest priority goes to left parentheses
            if($char=='(')
            {
                array_push($ops, $char);
                continue;
            }
                
            //then, right (close) parentheses
            if($char==')')
            {

                //process all operators until the next close-paren
                while(($x = array_pop($ops)) != '(')
                {
                    array_push($this->rpn,  $x);
                        
                    //if we've run out of operations on the stack, the
                    //expression is malformed
                    if(empty($ops))
                        throw new InvalidArgumentExpression("Malformed expression!");
                }

                //if the last object on the stack is a Not operator,
                //output it immediately, as, in this case, it is a function
                if(!empty($ops))
                    if($ops[count($ops)-1] == "~")
                        array_push($this->rpn, array_pop($ops));


                //if our close-paren is immediately followed by an open-paren
                //non-operator, or unary not, this is a special case of the implied and
                if($i < (count($chars) - 1))
                {
                    if($chars[$i+1]=='(' || $chars[$i+1]=='~' || $chars[$i+1]=='!' || (!self::isOperator($chars[$i+1]) && ctype_alnum($chars[$i+1])))
                        $char = '*';
                    else
                        continue;
                }
                else
                {
                    continue;
                }
            }
            
            //we now know we have a non-parenthesis operator
            assert($char != ')' && $char != ')');

            //if there's nothing on the stack, our operator takes
            //automatic precedence
            if(empty($ops))
            {
                array_push($ops, $char);
                continue;
            }
                
            //if the operator doesn't have precedence
            //place it on the stack and take the operator with more precendence
            if(!self::operatorPreceeds($char, $ops[count($ops)-1]) && $ops[count($ops)-1]!='(')
            array_push($this->rpn, array_pop($ops));
                
            //push the operator onto  the stack
            array_push($ops, $char);
                
            //(continue)
        }


        //we're finished with the input string, so output all remaining
        //operators to finish the process
        while(!empty($ops))
            array_push($this->rpn, array_pop($ops));
    }

    /**
     * Generates an associative array which represents the truth table for this boolean expression.
     *  
     * @param array $other_vars Any other variables which should be included in the truth table; useful for comparison. 
     * @return array Keys represent the boolean input string (in alphabetical order), values the corresponding output.
     */
    public function truth_table(array $other_vars=array())
    {
        
        $table = array();
        
        //create a new array composed of all variables used in this expression,
        //plus an optional set of other variables (useful in comparing two expressions truth tables)
        $target_vars = array_unique(array_merge($this->all_vars(), $other_vars));
        sort($target_vars);
        
        //get every possible combination of inputs
        $all_mappings = self::enumerate_all_mappings($target_vars);

        //for every possible combination, evaluate the expression
        foreach($all_mappings as $mapping)
        {
            //sort the mapping, so the binary key is meaningful
            //(would be nice if PHP let us use mappings _as_ keys)
            ksort($mapping);
            
            //add the expression to the table
            $table[self::mapping_to_binary($mapping)] = $this->evaluate($mapping);
        }
        
        //return the truth table
        return $table;
    }
    
    /**
     * Prints a given truth-table, in a human-readable format. Should be used with <pre> on a web page.
     *
     * @param array $other_vars Any other variables which should be included in the truth table; useful for comparison.
     */
    public function print_truth_table($other_vars = array())
    {
        $keys = array_unique(array_merge($this->all_vars(), $other_vars));
        sort($keys);
        
        //print each of the input variables, for user reference
        foreach($keys as $key)
        {
            echo $key;
        }
        echo ' | ?<br/>';
        
        //get the truth table
        $table = $this->truth_table($other_vars);
        
        //print each row in the table
        foreach($table as $row => $value)
            echo $row." | ".($value ? '1' : '0').'<br/>';
                
    }
    
    /**
     * Converts a relation between identifiers and boolean values to a bitstring,
     * the order of the bits corresponds to the alphabetical order of the identifiers.
     */
    public static function mapping_to_binary($mapping)
    {
        //return buffer
        $ret = "";
        
        //ensure the variables are in alphabetical order
        ksort($mapping);
        
        //add a binary representation of each substitution item to the return buffer
        foreach($mapping as $item)
            if($item===true)
                $ret .= '1';
            else
                $ret .= '0';
        
        //return
        return $ret;
    }

    /**
     * @return An array of all identifiers included in the expression.   
     */
    public function all_vars()
    {
        $vars = array();

        foreach($this->rpn as $item)
        {
            //if the item isn't an operator or a boolean, it must be a variable
            if(!self::isOperator($item) && !self::isBoolean($item))
                $vars["$item"] = $item;
        }
        
        return array_values($vars);
    }

    /**
     * Evaluates the expression, replacing each identifier with a truth value
     * provided by the relation. 
     * 
     * @param array $mapping An associative array representitive of a relation which maps
     *                       identifiers to truth values.
     */
    public function evaluate($mapping)
    {
        //create a shallow copy of the RPN output
        $rpn = $this->rpn;

        //list of pending operations
        $stack = array();

        //while there are still elements in the RPN expression
        while(count($rpn))
        {
            //remove the first element from the RPN queue
            $bottom = array_shift($rpn);
                
            //if we have a token
            if(!self::isOperator($bottom))
            {
                
                //evaluate it, push it onto the stack, and continue
                if(!self::isBoolean($bottom))
                    array_push($stack, $mapping["$bottom"]);
                else
                    array_push($stack, self::eval_bool($bottom));
                continue;
                
            }
            //otherwise, we have an operator
            else
            {
                //if the operator is _not_, then it's unary
                if($bottom=='!' || $bottom=='~')
                {
                    //if we don't have enough arguments on the stack,
                    //the expression was malformed
                    if(count($stack) < 1)
                        throw new InvalidArugumentException("Malformed expression!", 0);
                    
                    //get an argument from the stack
                    $top = array_pop($stack);
                        
                    //compute the value, then push it onto the stack
                    array_push($stack, self::soft_eval($bottom, $top));
                }
                else
                //otherwise, it's a binary operator
                {
                    //if we don't have enough arguments on the stack,
                    //the expression was malformed
                    if(count($stack) < 2)
                        throw new InvalidArugumentException("Malformed expression!", 0);
                        
                    //get the arguments from the stack
                    $a = array_pop($stack);
                    $b = array_pop($stack);
                        
                    //compute the expression, the push the result onto the stack
                    array_push($stack, self::soft_eval($bottom, $a, $b));
                }
            }
        }

        //if we didn't get the correct amount of values on the stack
        if(count($stack)!==1)
            throw new InvalidArugumentException("Malformed expression!", 0);

        //return the computation's result
        return $stack[0];
    }

    /**
     * Generates all possible truth value sets for a given set of variables, in the form
     * of several relation which map identifier names to truth values.
     * 
     *  For example:  (a,b) will return ((a=> False, b=> False), (a=> False, b=> True), (a=> True, b=> False), (a=> True, b=>True)),
     *  where () represents an array.
     * 
     */
    public static function enumerate_all_mappings($vars)
    {
        $all_mappings = array();
        rsort($vars);
            
        // enumerate each possible input value, encoded as a binary number
        // for example, the number 16 = 10000, which determines that the first
        // four input variables are False, and the fifth is True
      
        for($i = 0; $i < 1 << count($vars); ++$i)
        {
            $mapping = array();

            //enumerate each variable by determining if the binary bit in question is one

            for($j=0; $j < count($vars); ++$j)
                $mapping["$vars[$j]"] = ($i >> $j) % 2 == 1;

            ksort($mapping);
            
            //and add the completed mapping to the list
            $all_mappings[] = $mapping;
        }

        //return the completed list of mappings
        return $all_mappings;
    }

    /**
     * Determines if the string is a valid boolean.
     * Valid identifiers are: T, F, 0, 1
     */
    private static function isBoolean($char)
    {
        switch($char)
        {
            case '0':
            case '1':
            case 'T':
            case 'F':
                return true;
            default:
                return false;
        }
    }
    
    
    /**
     * Converts a boolean literal to a PHP truth value.
     */
    private static function eval_bool($char)
    {
        switch($char)
        {
        
            case '1':
            case 'T':
                return true;
            default:
                return false;
        }
    }

    /**
     * Evaluates a given truth expression.  
     */
    private static function soft_eval($op, $a, $b=False)
    {
        switch($op)
        {
            case '*': //and
                return $a and $b;
            case '+': //or
                return $a or $b;
            case '~': //not
                return !$a;
            case '!': //not, alternate
                return !$a;
            case '^': //xor
                return $a xor $b;
        }
    }

    /**
     * Determines if an operator has precedence over any other.
     */
    public static function operatorPreceeds($x, $y = "+")
    {
        //The only operator with precedence over another is AND:
        return ($x=='*' && $y!='*') || ($x=='^' && $y!='*' && $y!='^');
        
        
    }


    /**
     * Determines if a single character is an operator. (Parens are
     * considered operators.)
     */
    public static function isOperator($char)
    {
        //note we are testing for direct equivalence
        //(and thus this is better than a regex)
        switch($char)
        {
            case '*': //and
            case '+': //or
            case '~': //not
            case '!': //not, alternate
            case '^': //xor
            case '(': //left paren
            case ')': //right paren
                return true;
            default:
                return false;
        }
    }

    /**
     * Outputs a string representation of the class.
     */
    public function __toString()
    {
        return join('', $this->rpn);
    }

    /**
     * Determines if this expression is logically equivalent to another given expression.
     * 
     * @param LogicExpression The expression to be checked for equivalence.
     */
    public function equivalent_to(LogicExpression $other)
    {
        return $this->logically_equivalent($other);     
    }
    
    /**
     * Determines if this expression is logically equivalent to another given expression.
     * 
     * @param LogicExpression The expression to be checked for equivalence.
     */
    public function logically_equivalent(LogicExpression $other)
    {       
        $this_vars = $this->all_vars();
        $other_vars = $other->all_vars();
        
        return $this->truth_table($other_vars) == $other->truth_table($this_vars);
    }


    /**
     * Returns the total binary gate count necessary to implement the given circuit.
     *
     * @param bool $count_inverters True iff inverters should be included in the count.
     */
    public function gate_count($count_inverters = false)
    {       
        $gate_counts = array_count_values($this->rpn);
        $gates = array('+', '*', '&', '|', '^');
        $count = 0;
        
        //count each of the gate operations
        foreach($gate_counts as $gate => $subcount)
            if(in_array($gate, $gates))
                $count += $subcount;
            
        //and, if requested, count inverters
        if($count_inverters)
        {
            $count += (array_key_exists('!', $gate_counts) ? $gate_counts['!'] : 0) + (array_key_exists('~', $gate_counts) ? $gate_counts['~'] : 0);
            $count += substr_count($this->infix, "''")*2;
        }


        return $count;
    }

    /**
     * Simple helper function to remove all whitespace.
     */
    public static function remove_whitespace($string)
    {
        return trim(str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $string));
    }
    
}

/**
 * 
 * Special subcase of LogicExpression in which the form of the expression matters;
 * two ShapedLogicExpressions are only equivalent if they share the same form as well
 * as being logically equivalent.
 *  
 * @author ktemkin
 *
 */
class ShapedLogicExpression extends LogicExpression
{
    protected $tree;
    
    /**
     * Simple constructor. 
     */
    function __construct($infix)
    {
        //create the base LogicExpression
        parent::__construct($infix);
        
        //and also form an Abstract Syntax Tree
        $this->create_tree();
        
    }
    
    /**
     * Returns the canonical form of a given operator. 
     */
    protected static function base_operator($item)
    {
        switch($item)
        {
            case '*':
            case '&':
                return '*';
            case '+':
            case '|':
                return '+';
            case '^':
                return '^';
        }
        
    }
    
    /**
     * Returns true iff the arguments each represent the same logical function.
     */
    public static function same_function($a, $b, $c=null)
    {
        //simple recursive simplification
        
        if($c!==null)
            return self::same_function($a, $b) && self::same_function($b, $c);
            
        return (self::base_operator($a)==self::base_operator($b));
    }
    
    /**
     * Returns the Abstract Syntax Tree which represents the given logic expression.
     */
    public function get_tree()
    {
        return $this->tree;     
    }
    
    /**
     * Creates an Abstract Syntax Tree from the internal postfix representation of the expression. 
     */
    public function create_tree()
    {
        //create a shallow copy of the RPN output
        $rpn = $this->rpn;

        //list of pending operations
        $stack = array();
        
        //create an empty array, which represents an Abstract Syntax Tree
        $this->tree = array();

        //while there are still elements in the RPN expression
        while(count($rpn))
        {
            $bottom = array_shift($rpn);
                
                
            //if we have a token
            if(!self::isOperator($bottom))
            {
                //push it onto the stack, and continue
                array_push($stack, $bottom);
                continue;
            }
            //otherwise, we have an operator
            else
            {
                //if the operator is _not_, then it's unary
                if($bottom=='!' || $bottom=='~')
                {
                    //if we don't have enough arguments on the stack,
                    //the expression was malformed
                    if(count($stack) < 1)
                        throw new InvalidArugumentException("Malformed expression!", 0);
                    
                    //get an argument from the stack
                    $top = array_pop($stack);
                    
                    //compute the value, then push it onto the stack
                    array_push($stack, array('~', array($top)));
                }
                else
                //otherwise, it's a binary operator
                {
                    //if we don't have enough arguments on the stack,
                    //the expression was malformed
                    if(count($stack) < 2)
                        throw new InvalidArugumentException("Malformed expression!", 0);
                        
                    //get the arguments from the stack
                    $a = array_pop($stack);
                    $b = array_pop($stack);
                    
                    //three cases:
                    //case 1: both of the previous funcitons were of the same type as the current operator
                    if(is_array($a) && is_array($b) && self::same_function($a[0], $b[0], $bottom) )
                    {
                        //form a single group of coalesced operands, including all
                        //operands of $a and $b
                        $coalesced_operands = array_merge($a[1], $b[1]); 
                        
                        //and push those onto the stack
                        array_push($stack, array($bottom, $coalesced_operands));        
                        
                        //and merge them all into one n-ary operation
                        //array_push($stack, array_merge(array($bottom), $a, $b));
                    }
                    //case 2: one of the previous functions was of the same type as the current operator
                    else if(is_array($a) && self::same_function($a[0], $bottom))
                    {
                        //form a single group of coalesced operands, including all
                        //operands of $a (the gate of the same type), and $b, the gate of differing type
                        $coalesced_operands = $a[1]; 
                        $coalesced_operands[] = $b; 
                        
                        //and push those onto the stack
                        array_push($stack, array($bottom, $coalesced_operands));
                    }
                    else if(is_array($b) && self::same_function($b[0], $bottom))
                    {
                        //form a single group of coalesced operands, including all
                        //operands of $b (the gate of the same type), and $a, the gate of differing type
                        $coalesced_operands = $b[1]; 
                        $coalesced_operands[] = $a; 
                        
                        //and push those onto the stack
                        array_push($stack, array($bottom, $coalesced_operands));
                    }
                    //case 2: all of the operations are different
                    else
                    {
                        //otherwise, don't merge anything
                        array_push($stack, array($bottom, array($a, $b)));
                    }
                }
            }
        }

        
        //if we didn't get the correct amount of values on the stack
        if(count($stack)!==1)
        {   
            throw new InvalidArugumentException("Malformed expression! [STLF]", 0);
        }

        //store the created tree
        $this->tree =  $stack[0];
    }
    
    /**
     * Override for equivalence checking:
     * Shaped logic expressions are only equivalent if the two expressions have
     * isomorphic Abstract Syntax Trees.
     * 
     * (non-PHPdoc)
     * @see LogicExpression::equivalent_to()
     */
    public function equivalent_to(LogicExpression $other)
    {       
        //return true iff the two trees are isomorphic over a simple 
        //logic equivalence relation (defined in subtrees match)
        return self::subtrees_match($this->tree, $other->tree);
    }
    
    /**
     * Returns true iff two given subtrees are isomorphic.
     */
    protected static function subtrees_match($a, $b)
    {

        //our equivalence relation dictates that two subtrees
        //are equivalent if they have all of the same leaves
        //and the same "label" (boolean operator/gate)
        
        
        //base case: we have two identifiers
        //in this case, the degenerate subtrees match only if they contain
        //the same identifier
        if(!is_array($a) && !is_array($b))
            return $a == $b;
        
        //base case: if only one of the two subtrees is an array (i.e. not a leaf)
        //they can't possibly match
        elseif(!(is_array($a) && is_array($b)))
            return false;
        
        //base case: if the two subtrees feature a different logic operator,
        //they do not match
        if(!self::same_function($a[0], $b[0]))
            return false;
            
        //base case: if the two subtress are of different order / gate arity
        //they do not match
        if(count($a[1])!==count($b[1]))
            return;
            
        //recursive case:
        //check to see if there's an isomorphism which maps each of the subtrees
        //of $a to the subtrees of $b

        //keep track of the elements already matched, to ensure we have a 1:1 relationship
        //(an isomorphism must be a bijection)
        $already_matched = array();
        
        //for each value in the subtree $a,
        //try to find a match from $b
        foreach($a[1] as $akey => $aval)
        {
            //assume we'll fail, until we make a match
            $matched = false;
            
            //compare the given subtree to each of the opposing subtrees
            foreach($b[1] as $bkey => $bval)
            {
                //if the given key has already been matched, continue
                if(in_array($bkey, $already_matched))
                    continue;
                
                //otherwise, compare this two values
                if(self::subtrees_match($aval, $bval))
                {
                    //indicate success
                    $matched = true;
                    
                    //and mark the given subkey as already matched, to ensure 1:1 output
                    array_push($already_matched, $bkey);                
                    break;
                }       
            }
            
            //if we didn't make a match, these subtrees are not isomorphic
            if(!$matched)
                return false;
        }
        
        //if we matched every element, the subtrees are isomorphic
        return true;
    }
    
    protected function is_simple_tiered($first_tier, $second_tier, $third_tier='~')
    {
        //create an easy reference handle for the AST
        $tree =& $this->tree;
        
        //special case: we have a constant
        if(!is_array($tree))
            return true;
        
        //special case: just second-tier items
        //we allow these, as cannonically, ab is considered SOP and a+b is considered POS 
        if(self::base_operator($tree[0]) == $second_tier)
        {
            //ensure there isn't a second tier by ensuring that all elements are either
            //literals or complimented literals 
            foreach($tree[1] as $element)
                if(!self::is_literal_construct($element))
                    return false;
            
            //if they do match this condition, accept the item
            return true;
        }
        
        //AST array depth must be 4: SUMS (x2 due to structure) + PRODUCTS (x2 due to structure)
        //if(self::array_depth($tree) != 4)
            //return false;         
            
        //top level must match the first_tier
        if(self::base_operator($tree[0])!=$first_tier)
            return false;
            
        //all second level entities must be either the second tier, or literal constructs (see definition below) 
        foreach($tree[1] as $element)
        {
            //if the element is a literal construct, it matches; continue
            if(self::is_literal_construct($element))
                continue;
            
            //if the element is not a literal, and does't match our second tier, return false
            if(is_array($element) && self::base_operator($element[0]) != $second_tier)
                return false;
            
            //now that we know it's second-tier, we need to make sure it only has literal/literal construct children;
            //otherwise, this element is a more complex tier; return false 
            foreach($element[1] as $subelement)
                if(!self::is_literal_construct($subelement))
                    return false;
        }
        
        //if these three conditions are met, the expression is SOP
        return true;
    }
    
    function is_sum_of_products()
    {
        return $this->is_simple_tiered('+', '*');
    }
    
    function is_product_of_sums()
    {
        return $this->is_simple_tiered('*', '+');
    }
    
    /**
     * Returns true iff the item is either a literal or an inverted literal.
     */
    protected static function is_literal_construct($construct)
    {
        //if the item isn't an array, it's a literal
        if(!is_array($construct))
            return true;
            
        //if the item is a literal wrapped in an inverter, return true
        if($construct[0]=='~' && count($construct[1])==1 && !is_array($construct[1][0]))
            return true;
            
        //otherwise, the item isn't a literal construct             
        return false;
    }
    
    protected static function array_depth($array) 
    {
        $max_depth = 1;
    
        //for each element in the array
        foreach ($array as $value) 
        {
            //if the element is itself an array,
            if (is_array($value)) 
            {
                //recurse
                $depth = self::array_depth($value) + 1;

                //if we've found a deeper element, keep it
                if ($depth > $max_depth) 
                    $max_depth = $depth;
            }
        }

        return $max_depth;
    }
}
