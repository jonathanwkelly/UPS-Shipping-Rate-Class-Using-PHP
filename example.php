<?php

include_once 'ups.rate.class.php';

$objUpsRate = new UpsShippingQuote();

$strDestinationZip	= '38401';
$strMethodShortName 	= 'GND';
$strPackageLength 	= '24';
$strPackageWidth	= '18';
$strPackageHeight	= '6';
$strPackageWeight	= '2';
$boolReturnPriceOnly 	= true;

$result = $objUpsRate->GetShippingRate(
	$strDestinationZip, 
	$strMethodShortName, 
	$strPackageLength, 
	$strPackageWidth,
	$strPackageHeight, 
	$strPackageWeight, 
	$boolReturnPriceOnly
);

print_r($result);

?>
