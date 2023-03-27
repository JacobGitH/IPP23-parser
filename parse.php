<?php
include_once("parser/help.php");
// autor: Jakub Kopecky xkopec58

// globalni promena pro ulozeni zda byla hlavicka IPPCode23 nastavena
$ippHeader = false;
// pocitadlo instrukci
$stateCounter = 1;
// kod chyby
$err = 0;

// volani funkce z help.php pro zahajeni

xmlStart();
// kontroluje pocet parametru
if($argc > 2 && $argv[1] == "--help"){
    exit(10);
}
if($argc > 1 && $argv[1] == "--help"){
    retHelp();
}

while($line = fgets(STDIN)){
    // odebere prazdne radky
    if(trim($line) == ''){
        continue;
    }
    // odstraneni komentaru
    $parts = explode('#', $line, 2);
    $parsed = preg_split('/ +/', trim($parts[0]));
    // zjisti zda je hlavicka ippcode23 pritomna
    if(strtoupper($parsed[0]) == ".IPPCODE23"){
        if($ippHeader == true){
            exit(22);
        }
        $ippHeader = true;   
    }
    // nastaveni err na 21 pri redeklaraci hlavicky
    if($parsed[0] != '' && $ippHeader != true){
        exit(21);
    }
    
    $upperCased = strtoupper($parsed[0]);
    // prepinac pro vsechny instrukce v ippcode23
    switch ($upperCased) {
        case 'DEFVAR':
                numOfOperands($parsed, 2);
                isVar($parsed[1]);
                createInstruction($stateCounter, $parsed[0]);
                createArgument("var", $parsed[1]);
                endInstruction();
                $stateCounter++;
            break;
        case 'MOVE':
                numOfOperands($parsed, 3);
                isVar($parsed[1]);
                isSymb($parsed[2]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'LABEL':
                numOfOperands($parsed, 2);
                isLabel($parsed[1]);
                createInstruction($stateCounter, $parsed[0]);
                createArgument("label", $parsed[1]);
                endInstruction();
                $stateCounter++;
            break;
        case 'CALL':
                numOfOperands($parsed, 2);
                isLabel($parsed[1]);
                createInstruction($stateCounter, $parsed[0]);
                createArgument("label", $parsed[1]);
                endInstruction();
                $stateCounter++;
            break;
        case 'WRITE':
                numOfOperands($parsed, 2);
                isSymb($parsed[1]);
                createInstruction($stateCounter, $parsed[0]);
                for ($i=1; $i < count($parsed) ; $i++) { 
                    $argument = symbolParser($parsed[$i]);
                    createArgument($argument[0],$argument[1], $i);
                }
                endInstruction();
                $stateCounter++;
            break;
        case 'READ':
                numOfOperands($parsed, 3);
                isVar($parsed[1]);
                isType($parsed[2]);
                createInstruction($stateCounter, $parsed[0]);
                $argument = symbolParser($parsed[1]);
                createArgument($argument[0],$argument[1]);
                createArgument("type", $parsed[2],2);
                endInstruction();
                $stateCounter++;
            break;
        case 'CREATEFRAME':
                noAttrInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'PUSHFRAME':
                noAttrInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'POPFRAME':
                noAttrInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'RETURN':
                noAttrInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'PUSHS':
                numOfOperands($parsed, 2);
                isSymb($parsed[1]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'POPS':
                numOfOperands($parsed, 2);
                isVar($parsed[1]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'ADD':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'SUB':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'MUL':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'IDIV':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'LT':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'GT':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'EQ':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'AND':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'OR':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'NOT':
                numOfOperands($parsed, 3);
                isVar($parsed[1]);
                isSymb($parsed[2]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'STRI2INT':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'INT2CHAR':
                numOfOperands($parsed, 3);
                isVar($parsed[1]);
                isSymb($parsed[2]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'CONCAT':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'STRLEN':
                numOfOperands($parsed, 3);
                isVar($parsed[1]);
                isSymb($parsed[2]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'GETCHAR':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'SETCHAR':
                fourParamOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'TYPE':
                numOfOperands($parsed, 3);
                isVar($parsed[1]);
                isSymb($parsed[2]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'JUMP':
                numOfOperands($parsed, 2);
                isLabel($parsed[1]);
                createInstruction($stateCounter, $parsed[0]);
                createArgument("label", $parsed[1]);
                endInstruction();
                $stateCounter++;
            break;
        case 'JUMPIFEQ':
                jumpsOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'JUMPIFNEQ':
                jumpsOps($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'EXIT':
                numOfOperands($parsed, 2);
                isSymb($parsed[1]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'DPRINT':
                numOfOperands($parsed, 2);
                isSymb($parsed[1]);
                createJustInstruction($parsed, $stateCounter);
                $stateCounter++;
            break;
        case 'BREAK':
                numOfOperands($parsed, 1);
                createInstruction($stateCounter, $parsed[0]);
                endInstruction();
                $stateCounter++;
            break;
        case '.IPPCODE23':
            break;    
        default:    
            if($upperCased != ''){
                $err = 22;
            }
        break;
    }
}

if($ippHeader == false){
    $err = 21;
}
if($err != 0){
    exit($err);
}
// ukonci vypis xml souboru
xmlEnd();
