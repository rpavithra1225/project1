<?php

//turn on debugging messages
ini_set('display_errors', 'On');
error_reporting(E_ALL);


//instantiate the program object

//Class to load classes it finds the file when the progrm starts to fail for calling a missing class
class Manage {
    public static function autoload($class) {
        //you can put any file name or directory here
        include $class . '.php';
    }
}

spl_autoload_register(array('Manage', 'autoload'));

//instantiate the program object
$obj = new main();


class main {

    public function __construct()
    {
        $pageRequest = 'fileUploadForm';
        if(isset($_REQUEST['page'])) {
            $pageRequest = $_REQUEST['page'];
        }
        
         $page = new $pageRequest;

        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $page->get();
        } else {
            $page->post();
        }
    }

}

abstract class page {
    protected $html;

    public function __construct()
    {
        $this->html .= htmlTagsHelper::htmlStart();
        $this->html .= htmlTagsHelper::getcss("styles.css");
        $this->html .= htmlTagsHelper::bodyStart();
    }
    public function __destruct()
    {
        $this->html .= htmlTagsHelper::bodyEnd();
        $this->html .= htmlTagsHelper::htmlEnd();
        displayHelper::printString($this->html);
    }

    public function get() {
        echo htmlTagsHelper::headingThree("Hi! Welcome This is default get message");
    }

    public function post() {
        print_r($_POST);
    }
}

class fileUploadForm extends page
{

    public function get()
    {
        $form = htmlTagsHelper::divStart("container");
        $form .= htmlTagsHelper::paragraph('Upload the .csv file:');
        $form .= htmlTagsHelper::getlinebreak();
        $form .= htmlTagsHelper::formStart("index.php?fileUploadForm", "post", "multipart/form-data");
        $form .= htmlTagsHelper::inputStart("file", "file", "file", "");
        $form .= htmlTagsHelper::getlinebreak();
        $form .= htmlTagsHelper::paragraph('Does the file contain headers?');
        $form .= htmlTagsHelper::inputStart("radio", "header", "1", "yes");
        $form .= htmlTagsHelper::getLabel("1", "Yes");
        $form .= htmlTagsHelper::inputStart("radio", "header", "2", "no");
        $form .= htmlTagsHelper::getLabel("2", "No");
        $form .= htmlTagsHelper::getlinebreak();
        $form .= htmlTagsHelper::getlinebreak();
        $form .= htmlTagsHelper::inputStart("submit", "upload", "", "Upload");
        $form .= htmlTagsHelper::formEnd();
        $form .= htmlTagsHelper::divEnd();
        $this->html .= htmlTagsHelper::headingOne('Welcome to the application!', 'center');
        $this->html .= htmlTagsHelper::getlinebreak();
        $this->html .= $form;

    }

    public function post() 
    {
        $target_dir = "uploads/";

        //Remove the whitespaces before uploading the file
        $fileTrimmed = displayHelper::strReplace(" ", "", $_FILES["file"]["name"]);

        $target_file = $target_dir.basename($fileTrimmed);
        $fileType = pathinfo($target_file,PATHINFO_EXTENSION);
        $isHeaderSet = $_POST["header"];

        if ( isset($_POST["upload"]) ) {

            if ( isset($_FILES["file"])) {

                //if there was an error uploading the file
                if ($_FILES["file"]["error"] > 0) {
                    $msg = htmlTagsHelper::headingThree("Return Code: " . $_FILES["file"]["error"]);

                }
                else {
                                         
                     //if file already exists
                     if (file_exists($target_dir.$fileTrimmed)) {
                            $msg = htmlTagsHelper::headingThree($fileTrimmed. " already exists");
                     } else if ($fileType != "csv" ) {
                            $msg = htmlTagsHelper::headingThree("Sorry, only .csv files are allowed");
                     } else {
                            //Store file in directory "uploads" 
                            move_uploaded_file($_FILES['file']['tmp_name'], $target_dir.$fileTrimmed);
                            
                            //Redirect to tableDisplay page once successfully stored
                            fileUploadHelper::redirectpage("index.php","tableDisplay",$fileTrimmed, $isHeaderSet);
                    
                    }
                }
            } else {
                 $msg = htmlTagsHelper::headingThree('No file selected');
            }
        } else {
            $msg = htmlTagsHelper::headingThree('Not a valid submit request');
        }
        $this->html .= $msg;
    }
}



class tableDisplay extends page 
{
    public function get()
    {
        // Reads the file name from the request to display the content
        $fileName = $_GET["file"];
        $isHeaderSet = $_GET["isHeader"];
        $target_dir = "uploads/";

        //Search for the target file in 'uploads' folder
        $targetFile = $target_dir.$fileName;

        // Displays the contents of the csv file in a Table format
        $file = fopen($targetFile,"r");
        if($file === false) {
           die(displayHelper::printString("Error opening $file"));
        }

        //Start of the table
        $table = htmlTagsHelper::tableStart();

        // Displays the Table Header
        if ($isHeaderSet) {
            $tableHeader = fgetcsv($file);
            $displayData = displayHelper::displayData($tableHeader,TRUE);
            $table .= $displayData;
        }

        //Dispalys the contents of the csv file
        while($csvbody=fgetcsv($file))
        {
            $displayData = displayHelper::displayData($csvbody,FALSE);
            $table .= $displayData;
            
        }
        $table.= htmlTagsHelper::tableEnd();

        fclose($file);
        
        $this->html .= htmlTagsHelper::headingOne("Contents of '$fileName.' in Table format", " ");
        $this->html .= $table;
    }
}

class fileUploadHelper {

    static public function redirectpage($url,$page,$file, $isHeaderSet) {
        header("location:".$url."?"."page=".$page."&"."file=".$file."&"."isHeader=".$isHeaderSet);
    }
}

class displayHelper 
{

    // Prints the string passed as an argument
    static public function printString($string) {
       return print($string);
     }

    static public function displayData($tableRow,$isTableHeader) {

        $table = htmlTagsHelper::tableRowStart();
        foreach ($tableRow as $val) {
            if ($isTableHeader) {
                    $table.= htmlTagsHelper::tableHeadStart();
                    $table.= $val;
                    $table.= htmlTagsHelper::tableHeadEnd();
            } else {
                    $table .= htmlTagsHelper::tableDataStart();
                    $table .= $val;
                    $table .= htmlTagsHelper::tableDataEnd();
            }
        }
        $table .= htmlTagsHelper::tableRowEnd();
        return $table;
    }

    static public function strReplace($source, $dest, $str) {
        return str_replace($source, $dest, $str);
    }

    static public function getEmptyStr() {
        return " ";
    }
}

class htmlTagsHelper {

    static public function headingOne ($str, $center) {
        if(!isset($center)) {
            $center = 'left';
        }
        return "<h1 align='$center'> $str </h1>";
    }

    static public function headingThree ($str) {
        return "<h3> $str </h3>";
    }

    static public function paragraph ($str) {
        return "<p> $str </p>";
    }

    static public function tableRowStart () {
        return '<tr>';
    }


    static public function tableRowEnd () {
        return '</tr>';
    }

    static public function tableDataStart () {
        return '<td>';
    }

    static public function tableDataEnd () {
        return '</td>';
    }

    static public function tableHeadStart () {
        return '<th>';
    }

    static public function tableHeadEnd () {
        return '</th>';
    }

    static public function tableStart () {
            return '<table>';
    }


    static public function tableEnd () {
        return '</table>';
    }

    static public function divStart ($id) {
        if (isset($id)) {
            return "<div class='$id'>";
        } else {
            return '<div>';    
        }
        
    }

    static public function divEnd () {
        return '</div>';
    }

    static public function htmlStart () {
        return '<html>';
    }

    static public function htmlEnd () {
        return '</html>';
    }

    static public function bodyStart () {
        return '<body>';
    }

    static public function bodyEnd () {
        return '</body>';

    }

    static public function inputStart ($type, $name, $id, $val) {
        return "<input type='$type' name='$name' id='$id' value='$val'>";
    }


    static public function formStart ($action, $method, $enctype) {
        return "<form action='$action' method='$method' enctype='$enctype'>";
    }


    static public function formEnd () {
        return '</form>';
    }

    static public function getcss($cssFile) {
     return "<link rel='stylesheet' href='$cssFile'>";
    }

    static public function getlinebreak() {
     return '</br>';
    }

    static public function getLabel($id,$str) {
        return "<label for='$id'>".$str."</label>";
    }
}

?>