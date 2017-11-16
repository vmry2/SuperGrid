<!DOCTYPE html>
<html>
<head>
	<title>Tester</title>
	<link rel="stylesheet" type="text/css" href="Tester.css">
</head>
<body>
<?php
include "SuperGrid.class.php";

try
{
	$file_db = new PDO('sqlite:Northwind.sqlite3');
	$stmt = $file_db->query("SELECT * FROM Customers");
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
	$sg = new SuperGrid();
	$sg->setCssClass("SuperGridCss");
	$sg->SetData($result);
	$sg->MarkAlternateRowsCss = true;
	$sg->MarkColumnsCss = true;
	$sg->DisplayColumnNamesInFooter = true;
	$sg->hideColumn("Address");
	$sg->hideColumn("CustomerID");
	$sg->setColumnTitle("CompanyName", "Company Name");
	$sg->display();
	
	//$sg->dump(true);
}
catch(Exception $e)
{
	print($e->getMessage());
}
?>
</body>
</html>