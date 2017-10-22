<?php
  //this is a group of string functions
  class stringFunctions {
  //add static to make it global so you can call it without instantiation
     static public function printThis($string) {
      // this function prints the string passed as an argument
       return print($string);
     }
     static public function strLength($string) {
     // this function returns the length of the string passed as an argument
        return strlen($srting); 
     }	
  }