<?php

function autoloader($class) {
    include 'classes/' . $class . '.php';
}

spl_autoload_register('autoloader');

$t = new AddressBook(new DB());

$http_verb = $_SERVER['REQUEST_METHOD'];

switch($http_verb){
    case("GET"):{
        if(empty($_GET)){
            header('Content-type: application/json');
            echo $t->get();
        }
        elseif(isset($_GET['search']) AND !empty($_GET['search'])){ 
            header('Content-type: application/json');
            echo $t->search();
        }
        elseif(isset($_GET['dl']) AND !empty($_GET['dl'])){
            $t->exportAdressBook();
        }
        break;
    }   

    /*  create new Addressbook entry    */
    case("POST"):{
        /*  if succesfully enetered return data from DB with newly entry    */
        if(($res = $t->create()) === "succesfull"){
            header('Content-type: application/json');
            echo json_encode($t->get());
        }
        /* or return json with error report, whatever it is(false json,problem with db, false data) */
        else 
            return $res;
        break;
    }
    
    /*  pdate addresses table entry */
    case("PUT"):{
        header('Content-type: application/json');
        echo $t->update();
        break;
    }

    /*  remove entry from table addresses   */
    case("DELETE"):{
        header('Content-type: application/json');
        echo $t->delete();
        break;
    }
} 

?>