<?php
/**
 * Moodle / PEAR Quickform Wrapper for the LogicWaveform Editor  
 */

require_once('HTML/QuickForm/element.php');


class MoodleQuickForm_schematic extends HTML_QuickForm_element 
{
    
    /**
     *  Default options.
     */
    private $_options = array('advgates' => false, 'value' => null);
    
    
    /**
     * QuickForms constructor for the given waveform.
     * 
     * @param string $elementName
     * @param string $elementLabel
     * @param string $attributes
     * @param unknown_type $options
     */
    public function MoodleQuickForm_schematic($elementName=null, $elementLabel=null, $attributes=null, $options=null)
    {
        //ensure we have an array of options
        $options = (array)$options;
        
        //and copy each relevant option into the QuickForm element
        foreach ($options as $name=>$value)
            if (array_key_exists($name, $this->_options))
                $this->_options[$name] = $value;
        

        parent::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
    }
    

    function setName($name) 
    {
        $this->updateAttributes(array('name'=>$name));
    }

    function getName() 
    {
        return $this->getAttribute('name');
    }

    function setValue($value) 
    {
        $this->updateAttributes(array('value'=>$value));
    }

    function getValue() 
    {
        return $this->getAttribute('value');
    }
    
    function toHtml()
    {
        //get the fields for the included object
        $target = $this->getName();
        $advanced_gates = $this->_options['advgates'] ? 1 : 0;
        $value = $this->getValue();
        
        //render it using the provided HTML template
        ob_start();
        include $CFG->dirroot.'/question/type/boolean/scheditor.html';
        return ob_get_flush();
    }
}

HTML_QuickForm::registerElementType('waveform', "$CFG->dirroot/question/type/boolean/Schematic.QuickForm.php", 'MoodleQuickForm_schematic');
