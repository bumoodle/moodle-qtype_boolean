<?php

class InvalidSchematicException extends exception {}

class LogicSchematic
{
    /**
     * Stores a list of gate objects instatiated in the schematic.
     */
    private $gates;
    private $wires;
    
    
    
    
    /**
     * 
     */
    public function __construct($gates, $wires)
    {
        $this->gates = $gates;
        $this->wires = $wires;
    
        //TODO: abstract to class / static /whatever
        $gate_defaults = array('in' => 2, 'out' => 1, 'prefix' => '', 'infix'=>'', 'postfix' => '', 'input' => false, 'output' => false);
        $this->defs = array
        (
            //inputs
            'IN_A' => (object)array_merge($gate_defaults, array('infix'=>'a', 'input'=> true)),
            'IN_B' => (object)array_merge($gate_defaults, array('infix'=>'b', 'input'=> true)),
            'IN_C' => (object)array_merge($gate_defaults, array('infix'=>'c', 'input'=> true)),
            'IN_D' => (object)array_merge($gate_defaults, array('infix'=>'d', 'input'=> true)),
            'IN_E' => (object)array_merge($gate_defaults, array('infix'=>'e', 'input'=> true)),
            'IN_F' => (object)array_merge($gate_defaults, array('infix'=>'f', 'input'=> true)),
            
            //outputs
            'OUT_Q' => (object)array_merge($gate_defaults, array('output'=> true, 'in' => 1)),
        
            //basic gates
            'AND' => (object)array_merge($gate_defaults, array('infix' => '*')),
            'OR' => (object)array_merge($gate_defaults, array('infix' => '+')),
            'NOT' => (object)array_merge($gate_defaults, array('in' => 1, 'prefix' => '!')),
            
            //advanced gates
            'XOR' => (object)array_merge($gate_defaults, array('infix' => '^')),
            'XNOR' => (object)array_merge($gate_defaults, array('prefix' => '!', 'infix' => '^')),
            'NAND' => (object)array_merge($gate_defaults, array('prefix' => '!', 'infix' => '*')),
            'NOR' => (object)array_merge($gate_defaults, array('prefix' => '!', 'infix' => '+')),

        );
        
        //run some basic sanity checks
        $this->sanity_checks();
    }
    
    /**
     * 
     *  $as_object : If true, the result will be returned as a LogicExpression object; if not, it will be returned as a string.
     */
    public function to_expression($as_object = true)
    {
        //convert the object to an expression, recursively
        $expr = $this->eval_recurse($this->get_output());
        
        //convert the string into a LogicExpression object, and return it
        if($as_object)
            return new LogicExpression($expr);
        else
            return $expr;
    }
    
    private function sanity_checks()
    {
        $outputs = 0;
        
        //check 1: ensure the circuit only has one output
        
        foreach($this->gates as $id => $gate)
        {
            $def = $this->get_definition($id);
            
            if($def->output)
                ++$outputs;
        }
        
        if($outputs != 1)
            throw new InvalidSchematicException('Improper number of (identical) outputs!');
    }
    
    /**
     * Returns the gate ID for the gate which connects to the given schematic's output.
     */
    private function get_output()
    {
        //for each gate in the schematic
        foreach($this->gates as $id => $gate)
        {
            //get the gate's definition
            $def = $this->get_definition($id);
            
            //if this gate is /the/ output
            if($def->output)
            {
                //return the wire connected to it
                $inputs =  $this->get_input_gates($id);
                
                if(count($inputs)==0)
                    throw new InvalidSchematicException("Nothing connected to the output!");
                
                return $inputs[0];
            }
        }
        
        throw new InvalidSchematicException("Schematic lacks an output!");
    }
   
    /**
     * Returns a list of gate IDs which feed the given gate.
     */
    private function get_input_gates($gate_id)
    {
        $gates = array();
        
        //for each wire in the schematic
        foreach($this->wires as $wire)
        {
            //if the wire is attached to the input of the given gate, on either end, add it to our list of input wires
            //case 1: gate is attached to the wire's source
            if($wire->src->moduleId==$gate_id && (strpos($wire->src->terminal, '_INPUT')===0))
                $gates[] =  $wire->tgt->moduleId;
                
            //case 2: gate is attached to the wire's target
            if($wire->tgt->moduleId==$gate_id && (strpos($wire->tgt->terminal, '_INPUT')===0))
                $gates[] = $wire->src->moduleId;
        }
        
        //if we have the wrong number of inputs for the gate type, throw an exception
        if(count($gates) != $this->get_definition($gate_id)->in)
            throw new InvalidSchematicException('Inputs missing from one or more gates!');
        
        
        //return the matched wires
        return $gates;
    }
   
    /**
     * Performa a recursive descent down the abstract syntax (sub)tree rooted at target gate,
     * and returns the logical expression represented.
     * 
     * Should not be called with the output gate.
     */
    private function eval_recurse($target)
    {
        //base case: the object in an input
        if($this->get_definition($target)->input)
        {
            //return its name
            return $this->get_definition($target)->infix;
        }
        
        //recursive case: the object is a gate
        //(We know the gate isn't an input from above,
        //and we assume we're never going to be called with the output gate.)
        
        //get the defintion for the given gate
        $def = $this->get_definition($target);
        
        //and get a list of gates which feed this one
        $input_gates = $this->get_input_gates($target);
        
        //recurse for each of the input gates
        $subexprs = array_map(array($this, 'eval_recurse'), $input_gates);
        
        //and merge the given subexpressions into one expression
        return $def->prefix .'('. implode($def->infix, $subexprs) .')' . $def->postfix; 
    }
    
    private function get_definition($gate_id)
    {
        return $this->defs[$this->gates[$gate_id]->name];
    }
    
    public static function from_JSON($json_string)
    {
        //decode the given schematic
        $schema = json_decode($json_string);
        
        //test for bad schematic
        if($schema == false)
            throw new InvalidSchematicException('Did not receive a valid schematic response.');
            
        //create a new schematic from the list of modules (gates) and wires
        return new LogicSchematic((array)$schema->working->modules, (array)$schema->working->wires);
    }
}
