<?php
    require '../../../../../config.php';

    header("Content-type: text/javascript");
    
    //TODO: replace
    $root = $CFG->wwwroot.'/question/type/boolean/scripts/sch/';
?>

var logicGatesLang = 
{

    // Set a unique name for the language
    languageName: "logicGates",

    parentEl: 'schema',
    
    //use the inline form method of saving (compatible with Moodle)
    adapter: WireIt.WiringEditor.adapters.InlineForm,
    
    layoutOptions:
    {
        height: 600,
        width: 'auto',

        units: 
        [
            { position: 'top', height: 0, body: 'top'},
            { position: 'left', width: 150, resize: false, body: 'left', gutter: '5px', collapse: true, collapseSize: 25, header: 'Logic Elements', scroll: true, animate: true },
            { position: 'center', body: 'center', gutter: '5px' },
            { position: 'right', width: 0, resize: false, body: 'right', gutter: '5px', collapse: false, collapseSize: 0, animate: true }
        ]
    },
    
    
    // List of node types definition
    modules: [
        {
            "name": "AND",

            "container" : 
            {
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/gate_and.png",
                "icon": "<? echo $root; ?>gates/gate_and.png",
                "width": 100,
                "height": 50,
                "terminals": [
                    {"name": "_INPUT1", "direction": [-1,0], "offsetPosition": {"left": -7, "top": -1 },"ddConfig": {"type": "input","allowedTypes": ["output"]}, "nMaxWires": 1 },
                    {"name": "_INPUT2", "direction": [-1,0], "offsetPosition": {"left": -7, "top": 20 },"ddConfig": {"type": "input","allowedTypes": ["output"]}},
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 85, "top": 10 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }

        },

        {
          "name": "OR",

            "container": {
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/gate_or.png",
                "icon": "<?php echo $root; ?>gates/gate_or.png",
                "width": 100,
                "height": 50,
                    "terminals": [
                        {"name": "_INPUT1", "direction": [-1,0], "offsetPosition": {"left": -7, "top": -1 },"ddConfig": {"type": "input","allowedTypes": ["output"]}, "nMaxWires": 1 },
                        {"name": "_INPUT2", "direction": [-1,0], "offsetPosition": {"left": -7, "top": 20 },"ddConfig": {"type": "input","allowedTypes": ["output"]}},
                        {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 85, "top": 10 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                    ]
            }
        },

        {
          "name": "NOT",
          "container": {
                "width": 100,
                "height": 50,
                "xtype":"WireIt.ImageContainer", 
               "image": "<?php echo $root; ?>gates/gate_not.png",
               "icon": "<?php echo $root; ?>gates/gate_not.png",
                "terminals": [
                    {"name": "_INPUT", "direction": [-1,0], "offsetPosition": {"left": -7, "top": 10 },"ddConfig": {"type": "input","allowedTypes": ["output"]}, "nMaxWires": 1 },
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 85, "top": 10 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },

        <?php
            if(isset($_GET['advgates']) && $_GET['advgates'])
            {
        ?>

        {
           "name": "NAND",
           "container": {
               "width": 100,
                "height": 50,
               "xtype":"WireIt.ImageContainer", 
               "image": "<?php echo $root; ?>gates/gate_nand.png",
               "icon": "<?php echo $root; ?>gates/gate_nand.png",
                "terminals": [
                        {"name": "_INPUT1", "direction": [-1,0], "offsetPosition": {"left": -7, "top": -1 },"ddConfig": {"type": "input","allowedTypes": ["output"]}, "nMaxWires": 1 },
                        {"name": "_INPUT2", "direction": [-1,0], "offsetPosition": {"left": -7, "top": 20 },"ddConfig": {"type": "input","allowedTypes": ["output"]}},
                        {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 85, "top": 10 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },
        
        {
           "name": "NOR",
           "container": {
               "width": 100,
                "height": 50,
               "xtype":"WireIt.ImageContainer", 
               "image": "<?php echo $root; ?>gates/gate_nor.png",
               "icon": "<?php echo $root; ?>gates/gate_nor.png",
                "terminals": [
                        {"name": "_INPUT1", "direction": [-1,0], "offsetPosition": {"left": -7, "top": -1 },"ddConfig": {"type": "input","allowedTypes": ["output"]}, "nMaxWires": 1 },
                        {"name": "_INPUT2", "direction": [-1,0], "offsetPosition": {"left": -7, "top": 20 },"ddConfig": {"type": "input","allowedTypes": ["output"]}},
                        {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 85, "top": 10 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },

        {
           "name": "XOR",
           "container": {
               "width": 100,
                "height": 50,
            "xtype":"WireIt.ImageContainer", 
            "image": "<?php echo $root; ?>gates/gate_xor.png",
            "icon": "<?php echo $root; ?>gates/gate_xor.png",
                "terminals": [
                        {"name": "_INPUT1", "direction": [-1,0], "offsetPosition": {"left": -7, "top": -1 },"ddConfig": {"type": "input","allowedTypes": ["output"]}, "nMaxWires": 1 },
                        {"name": "_INPUT2", "direction": [-1,0], "offsetPosition": {"left": -7, "top": 20 },"ddConfig": {"type": "input","allowedTypes": ["output"]}},
                        {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 85, "top": 10 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },
        
        
        
        {
           "name": "XNOR",
           "container": {
               "width": 100,
                "height": 50,
            "xtype":"WireIt.ImageContainer", 
            "image": "<?php echo $root; ?>gates/gate_xnor.png",
            "icon": "<?php echo $root; ?>gates/gate_xnor.png",
                "terminals": [
                        {"name": "_INPUT1", "direction": [-1,0], "offsetPosition": {"left": -7, "top": -1 },"ddConfig": {"type": "input","allowedTypes": ["output"]}, "nMaxWires": 1 },
                        {"name": "_INPUT2", "direction": [-1,0], "offsetPosition": {"left": -7, "top": 20 },"ddConfig": {"type": "input","allowedTypes": ["output"]}},
                        {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 85, "top": 10 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },
        
        <?php
            }
        ?>

        {
            "name": "IN_A",
            "container" : {
                "width": 53,
                "height": 33,
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/input_A.png",
                "icon": "<?php echo $root; ?>gates/input_A.png",
                "terminals": [
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 40, "top": 1 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },

        {
            "name": "IN_B",
            "container" : {
                "width": 53,
                "height": 33,
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/input_B.png",
                "icon": "<?php echo $root; ?>gates/input_B.png",
                "terminals": [
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 40, "top": 1 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },

        {
            "name": "IN_C",
            "container" : {
                "width": 53,
                "height": 33,
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/input_C.png",
                "icon": "<?php echo $root; ?>gates/input_C.png",
                "terminals": [
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 40, "top": 1 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },
        
        
                {
            "name": "IN_D",
            "container" : {
                "width": 53,
                "height": 33,
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/input_D.png",
                "icon": "<?php echo $root; ?>gates/input_D.png",
                "terminals": [
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 40, "top": 1 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },
        
                {
            "name": "IN_E",
            "container" : {
                "width": 53,
                "height": 33,
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/input_E.png",
                "icon": "<?php echo $root; ?>gates/input_E.png",
                "terminals": [
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 40, "top": 1 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },
        
        {
            "name": "IN_F",
            "container" : {
                "width": 53,
                "height": 33,
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/input_F.png",
                "icon": "<?php echo $root; ?>gates/input_F.png",
                "terminals": [
                    {"name": "_OUTPUT", "direction": [1,0], "offsetPosition": {"left": 40, "top": 1 },"ddConfig": {"type": "output","allowedTypes": ["input"]}}
                ]
            }
        },
        
        {
            "name": "OUT_Q",
            "container" : {
                "width": 53,
                "height": 33,                
                "xtype":"WireIt.ImageContainer", 
                "image": "<?php echo $root; ?>gates/output_Q.png",
                "icon": "<?php echo $root; ?>gates/output_Q.png",
                "terminals": [
                    {"name": "_INPUT", "direction": [-1,0], "offsetPosition": {"left": -20, "top": 1 },"ddConfig": {"type": "input","allowedTypes": ["output"]}}
                ]
            }
        }
    ]
};


function save_wiring()
{
    var last_value;
    
    //encode the circuit as a JSON string, and store it in a textarea
    YUI().use('json-stringify', function (Y) { last_value = Y.JSON.stringify(window.editor.getValue()); } );
    YUI().use('node', function(Y)  { Y.one(document.getElementById('<?php echo $_GET['target']; ?>')).set('value', last_value); });
}

function load_wiring()
{
    YUI().use
    (
        'json-parse', 
        'node', 
        function(Y)
        {
            //parse the JSON string, and populate the wiring editor
            new_val = Y.JSON.parse(window.restore_value);
            editor.loadPipe(new_val.working);
        }
    );
}

function wiring_init() 
{ 
    window.editor = new WireIt.WiringEditor(logicGatesLang);     
    load_wiring();

    
    //save every .5s    
    setInterval("save_wiring()", 500); //auto-save every .5 seconds
    
    
}

//when ready, initialize the editor
