<?php
// globalni promena pro uloyeni xml
$xml;

// funkce vypise help pri parametru --help
function retHelp(){
    echo "parse.php je skript ktery preklada vstupni kod IPPcode23 na vystupni XML \n";
    echo "vstupni soubor se presmeruje pomoci znaku < nasledovne: \n";
    echo "parse.php < vstupni_soubor \n";
    exit(0);
}

// funcke vytvori xml kostru 
function xmlStart(){
    global $xml;
    $xml = xmlwriter_open_memory();
    xmlwriter_set_indent($xml, 1);
    xmlwriter_set_indent_string($xml, "  ");
    xmlwriter_start_document($xml, '1.0', 'UTF-8');

    xmlwriter_start_element($xml, 'program');
    xmlwriter_start_attribute($xml, 'language');
    xmlwriter_text($xml, 'IPPcode23');
    xmlwriter_end_attribute($xml);
}

// funkce vytvori xml kostru a vlozi parametry pro sestaveni xml vystupu
function createInstruction($order,  $opcode){
    global $xml;
    xmlwriter_start_element($xml, 'instruction');
    xmlwriter_start_attribute($xml, 'order');
    xmlwriter_text($xml, $order);
    xmlwriter_end_attribute($xml);
    xmlwriter_start_attribute($xml, 'opcode');
    xmlwriter_text($xml, strtoupper($opcode));
    xmlwriter_end_attribute($xml);
}
// vytvoreni argumentu uvnitr instrukce
function createArgument($type, $value, $argCounter = 1){
    global $xml;
    xmlwriter_start_element($xml, 'arg'.$argCounter);
    xmlwriter_start_attribute($xml, 'type');
    xmlwriter_text($xml, $type);
    xmlwriter_end_attribute($xml);
    if($value != null){
        xmlwriter_text($xml, $value);
    }
    xmlwriter_end_element($xml);
}

//ukonceni instrukce *potreba zavolat po vytvoreni vsech argumentu v dane instrukci
function endInstruction(){
    global $xml;
    xmlwriter_end_element($xml);
}

//ukonci xml
function xmlEnd(){
    global $xml;
    xmlwriter_end_element($xml);
    echo xmlwriter_output_memory($xml);
}

// funkce testuje zda je danny string label podle zadani
function isLabel($label){
    // regex pro otestovani zda je to validni label
    if(!preg_match("/^[a-zA-Z_%&*\-$!?]*$/", $label) ||
        preg_match("/(string|nil|int|bool)/", $label) ||
        preg_match("/(LF@|TF@|GF@)/", $label)){
        global $err;
        $err = 23;
    }
}

// funkce testuje zda je danny string promenna podle zadani
function isVar($var){
    if(!preg_match("/^(?!.*@.*@)(LF|GF|TF)@[a-zA-Z_%&?$*%!][a-zA-Z0-9&*$%?!]*$/", $var)){
        global $err;
        $err = 23;
    }  
}
// funkce testuje zda je danny string symbol podle zadani
function isSymb($symb){
    global $err;
    if(preg_match('/^(nil)@[a-zA-Z&*$0-9]*$/', $symb)){
        $parsed = explode('@', $symb);
        if($parsed[1] != 'nil'){
            $err = 23;
        }
    }
    checkBool($symb);   
    if(preg_match('/^(int)@[a-zA-Z&*$0-9]*$/', $symb)){
        $parsed = explode('@', $symb);
        if(!preg_match('/^[\-\+]*[0-9]+$/', $parsed[1])){
            $err = 23;
        }
    }
    if(!preg_match("/^(LF|GF|TF|string|bool|int|nil)@[a-zA-Z0-9&*$%?!<>,.]*/", $symb) || preg_match('/\\\\$/', $symb) || preg_match('/\\\\03s/', $symb)){
        $err = 23;
    }
}

// funkce testuje zda je danny string typ podle zadani
function isType($type){
    if(!preg_match("/^(string|bool|int)$/", $type)){
        global $err;
        $err = 23;
    }
}
// funkce testuje zda danna instrukce ma validni pocet instrukci
function numOfOperands($operands, $num){
    if(count($operands) != $num ){
        global $err;
        $err = 23; 
    }
}
// overeni zda danny string je validni bool dle zadani
function checkBool($bool){
    global $err;
    if(preg_match("/bool@/", $bool)){
        if(preg_match("/bool@(false|true)/", $bool)){
            return;
        }else{
            $err = 23;
        }
    }
}
// funkce vytvori xml pro instrukci bez promenych
function noAttrInstruction($parsed, $stateCounter){
    numOfOperands($parsed, 1);
    createInstruction($stateCounter, $parsed[0]);
    endInstruction();
}
// rozebrani danneho stringu a zjisteni jeho typu
function symbolParser($symb){
    $symParsed = explode('@', $symb);
    if(preg_match("/(string|bool|int|nil)/", $symParsed[0])){
        checkBool($symb);
        $ret[0] = $symParsed[0];
        array_shift($symParsed);
        $ret[1] = implode('@',$symParsed);
        return $ret;
    }else if(preg_match("/(LF|GF|TF)/", $symParsed[0])){
        $ret[0] = "var";
        $ret[1] = implode('@',$symParsed);
        return $ret;
    }
}
// vypis a validace aritmetickych instrukci
function fourParamOps($parsed, $stateCounter){
    numOfOperands($parsed, 4);
    isVar($parsed[1]);
    isSymb($parsed[2]);
    isSymb($parsed[3]);
    createInstruction($stateCounter, $parsed[0]);
    for ($i=1; $i < count($parsed) ; $i++) { 
        $argument = symbolParser($parsed[$i]);
        createArgument($argument[0],$argument[1], $i);
    }
    endInstruction();
}
// vypis a validace jump(eq|neq) instrukci
function jumpsOps($parsed, $stateCounter){
    numOfOperands($parsed, 4);
    isLabel($parsed[1]);
    isSymb($parsed[2]);
    isSymb($parsed[3]);
    createInstruction($stateCounter, $parsed[0]);
    createArgument("label", $parsed[1]);
    for ($i=2; $i < count($parsed) ; $i++) { 
        $argument = symbolParser($parsed[$i]);
        createArgument($argument[0],$argument[1], $i);
    }
    endInstruction();
}
// funkce vytvori instrukci bez jinych overeni
function createJustInstruction($parsed, $stateCounter){
    createInstruction($stateCounter, $parsed[0]);
        for ($i=1; $i < count($parsed) ; $i++) { 
            $argument = symbolParser($parsed[$i]);
        createArgument($argument[0],$argument[1], $i);
    }
    endInstruction();
}
