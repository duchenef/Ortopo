<!DOCTYPE html>
<html>
<head>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
    <title>ORTOPO</title>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
  <style>
    version {
      position:absolute;
      left:646px;
      top:26px;
    }
    body {
      background-color:#ddeeff;
      font-family:"Verdana";
    }
    small {
      font-family:"Verdana";
      font-size:12px;
    }
    verysmalli {
      font-family:"Verdana";
      font-size:10px;
      font-style:italic;
    }
    table, td, th
    {
      border-collapse:collapse;
      border:1px solid #002277;
    }
    th
    {
      align
      background-color:#bbddff;
      //background-image:url('resources/ice.png');
      color:#002255;
    }
  </style>
</head>
    
<body onload="document.forms.main_form.isbn.focus()">
  <version><verysmalli>ORTOPO v0.94 20180516fd</verysmalli></version>

<?php

// library de creation de fichier excel xlsx
   include_once("xlsx_function.php");

// fonctions externes
   include_once("proquest_function.php");
   include_once("decitre_function.php");
   include_once("payot_function.php");
   include_once("template_function.php");
   $finalArray = template_function();

// nettoyage du cache upload 
    //Repertoire images
    $dir = "uploads/";
    // Pour chaque fichier du repertoire
    foreach(glob($dir.'*.*') as $file){
        unlink($file);
    }

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadname = basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;

// Check if file is csv
$csvFileType = pathinfo($target_file,PATHINFO_EXTENSION);
 
// Check if file already exists
if (file_exists($target_file)) {
    $upload_status = "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    $upload_status = "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow csv format only
if($csvFileType == "csv") {
    $upload_status = "File is csv.";
    $uploadOk = 1;
}
else {
    $upload_status = "Sorry, only csv files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0
if ($uploadOk == 0) {
    //$upload_status = "Sorry, your file was not uploaded."; // lets detailed upload status appear in the output log

// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $upload_status = "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        $url = "http://libraries.ecolint.ch/ortopo/uploads/$uploadname";
        $upload_status= "File was uploaded to ".$url;
    } else {
        $upload_status= "Sorry, there was an error uploading your file.";
    }
}

?>

<table width = '800'>
  <TR height='38px'>
        <TH align='left' colspan='4'>ORder TO P.O. (ORTOPO)</TH>
  </TR>
  <TR>
    <TD align='left' colspan='3' width='600'>
      <form enctype="multipart/form-data" method="post" action="index.php">
      Image upload : 
      <input type="file" name="fileToUpload" id="fileToUpload" />
    </TD>
    <TD align='center' colspan='1' width='200'>
      <input type="submit" value="Upload" />
    </TD>
  </TR>
  <TR>
    <TD colspan='4'>
      <verysmalli>
         <input type="radio" name="supplier" value="Proquest" checked>Proquest</input>
         <input type="radio" name="supplier" value="Decitre" >Decitre</input>
	 <input type="radio" name="supplier" value="Payot" >Payot</input>
      </verysmalli>
    </TD>
  </TR>
      </form>
</table>

</body>
</html>

<?php

$row = 1;
$csvarray = [];

// verification du supplier
if (isset($_POST["supplier"]))
{
$selected_supplier = $_POST["supplier"];
$supplier_status = $selected_supplier;
}   
else
{
$selected_supplier = "";
$supplier_status="no supplier selected";
}

$account = $finalArray[7][2];
$costCenter = $finalArray[7][3];

// debut boucle proquest

if ($selected_supplier == "Proquest") {
   if(($file = fopen("uploads/$uploadname","r")) !== FALSE) {
       while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
           $csvarray[] = $data;
       }
       fclose($file);
   }
   $finalArray[3][10] = "Coutts information services";
   $finalArray[2][10] = "6077";
   $finalArray[0][10] = "GBP";
   $finalArray[1][10] = "1.30";

// parametres du PO et application au row en cours
$vat = "0";
$rate = $finalArray[1][10];
$t = 9; //position de la premiere ligne de data dans le template

foreach ($csvarray as $key => $item) {
   if ($key < 1) continue;
   $row_results = proquest_function($csvarray, $key);
   $title = $row_results[0];
   $price = $row_results[1];
   $qty = $row_results[2];
   $netAmount = $qty*$price;
   $amountInCurrency = $netAmount+($netAmount*$vat)/100;
   $estimatedCHFAmount = $netAmount*$rate;
   $receipt = "Yes";
   
   $row = array("", $title, $account, $costCenter, "", $qty, $price, $vat, $netAmount, $amountInCurrency, $estimatedCHFAmount, $receipt);

   // insertion de row tiré de proquest dans template
   array_push($finalArray, $row);
   // incrementation de la position d'insertion de la prochaine ligne dans le template
   $t = $t+1;
}
// fin boucle proquest
}

// boucle DECITRE
else if ($selected_supplier == "Decitre") {
    if(($file = fopen("uploads/$uploadname","r")) !== FALSE) {
       while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
           $csvarray[] = $data;
       }
       fclose($file);
   }
   $finalArray[3][10] = "Decitre";
   $finalArray[2][10] = "114298";
   $finalArray[0][10] = "EUR";
   $finalArray[1][10] = "1.10";

// parametres du PO et application au row en cours
$vat = "0";
$rate = $finalArray[1][10];
$t = 9; //position de la premiere ligne de data dans le template

foreach ($csvarray as $key => $item) {
   if ($key < 1) continue;
   $row_results = decitre_function($csvarray, $key);
   $title = $row_results[0];
   $price = $row_results[1];
   $qty = $row_results[2];
   $netAmount = $qty*$price;
   $amountInCurrency = $netAmount+($netAmount*$vat)/100;
   $estimatedCHFAmount = $netAmount*$rate;
   $receipt = "Yes";
   
   if (substr($title, 0, 3) ==" - ") continue;
   $row = array("", $title, $account, $costCenter, "", $qty, $price, $vat, $netAmount, $amountInCurrency, $estimatedCHFAmount, $receipt);

   // insertion de row tiré de proquest dans template
   array_push($finalArray, $row);
   // incrementation de la position d'insertion de la prochaine ligne dans le template
   $t = $t+1;
}
// fin boucle decitre
}

// Boucle PAYOT
else if ($selected_supplier == "Payot") {
    if(($file = fopen("uploads/$uploadname","r")) !== FALSE) {
       while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
           $csvarray[] = $data;
       }
       fclose($file);
   }
   $finalArray[3][10] = "Payot";
   $finalArray[2][10] = "8056";
   $finalArray[0][10] = "CHF";
   $finalArray[1][10] = "1";

// parametres du PO et application au row en cours
$vat = "0";
$rate = $finalArray[1][10];
$t = 9; //position de la premiere ligne de data dans le template

foreach ($csvarray as $key => $item) {
   if ($key < 1) continue;
   $row_results = payot_function($csvarray, $key);
   $title = $row_results[0];
   $price = $row_results[1];
   $qty = $row_results[2];
   $netAmount = $qty*$price;
   $amountInCurrency = $netAmount+($netAmount*$vat)/100;
   $estimatedCHFAmount = $netAmount*$rate;
   $receipt = "Yes";
   
   if (substr($title, 0, 3) ==" - ") continue;
   $row = array("", $title, $account, $costCenter, "", $qty, $price, $vat, $netAmount, $amountInCurrency, $estimatedCHFAmount, $receipt);

   // insertion de row tiré de proquest dans template
   array_push($finalArray, $row);
   // incrementation de la position d'insertion de la prochaine ligne dans le template
   $t = $t+1;
}
// fin boucle Payot
}

// Affichage des resultats;
echo "<BR>";
echo "<table width='800'>";
// Output log

echo "<table width = '800'><tr><th align='left'>Output</th></tr><td>";
echo "<small>";
echo $upload_status."<BR>";
echo "Selected supplier: ".$supplier_status."<BR>";
echo "Download link: <a href='http://libraries.ecolint.ch/ortopo/output.xlsx'>output.xlsx</a><BR>";
echo "Date: ".$finalArray[0][1]." -- Request Type: ".$finalArray[0][7]." -- Currency: ".$finalArray[0][10].", Rate: ".$finalArray[1][10]."<BR>";
echo "Requestor ID: ".$finalArray[1][1].", Validator ID: ".$finalArray[2][1]." -- Campus: ".$finalArray[3][4].", Location: ".$finalArray[4][4]."<BR>";
echo "Account: ".$finalArray[7][2]."-".$finalArray[7][3]." -- Year: ".$finalArray[1][7]." -- Payment type: ".$finalArray[2][7]."<BR>";
echo "Supplier ID: ".$finalArray[2][10]."; name: ".$finalArray[3][10]." -- Year: ".$finalArray[1][7]." -- Payment type: ".$finalArray[2][7]."<BR>";
echo "Partial Delivery: ".$finalArray[5][7]." -- VAT: ".$finalArray[5][12]."<BR>";
echo "<u>Content</u>: <BR>";

foreach ($finalArray as $k => $item) {
   if ($k < 9) continue;
   echo $finalArray[$k][1]." -- Price: ".$finalArray[$k][6]."<BR>";

}

//var_dump($finalArray);
echo "<BR>";
//echo "data: <BR>";
//var_dump($csvarray);
echo "</small>";

//creation fichier excel
xlsx_function($finalArray);

?>

</table>
</body>
</html>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-12520299-1', 'auto');
  ga('send', 'pageview');

</script>