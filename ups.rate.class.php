<?php

class UpsShippingQuote {

    var $strAccessLicenseNumber 	= 'ABC123ABC123ABC123';
    var $strUserId 					= 'UsernameHere';
    var $strPassword				= 'supersecret';
    var $strShipperNumber			= '445566';
	var $strShipperZip				= '12345';
	var $strDefaultServiceCode 		= '03'; // GND / General Ground method
	var $strRateWebServiceLocation	= 'https://www.ups.com/ups.app/xml/Rate'; // Production URL

	var $boolDebugMode				= false;
	
	function UpsShippingQuote() { }

	/**
	 * Gets passed a character string that represents
	 * the method. The service code that needs to be 
	 * passed to the web service is then returned.
	 * Defaults to Ground shipping.
	 * 
	 * @param strService string 
	 * 
	 * @return string The shipping code the web service wants
	 **/
	private function GetServiceCode($strService='GND') {

		switch($strService) { 

		     case '1DM':            
		       $strServiceCode = '14'; 
		       break; 

		     case '1DA':            
		       $strServiceCode = '01'; 
		       break;          

		      case '1DAPI':            
		       $strServiceCode = '01'; 
		       break; 

		     case '1DP':            
		       $strServiceCode = '13'; 
		       break; 

		     case '2DM':            
		       $strServiceCode = '59'; 
		       break; 

		     case '2DA':            
		       $strServiceCode = '02'; 
		       break; 

		     case '3DS':            
		       $strServiceCode = '12'; 
		       break; 

		     case 'GND':            
		       $strServiceCode = '03'; 
		       break; 

		     case 'GNDRES':            
		       $strServiceCode = '03'; 
		       break; 

		     case 'GNDCOM':            
		       $strServiceCode = '03'; 
		       break;           

		     case 'STD':            
		       $strServiceCode = '11'; 
		       break; 

		     case 'XPR':            
		       $strServiceCode = '07'; 
		       break; 

		     case 'XDM':            
		       $strServiceCode = '54'; 
		       break; 

		     case 'XPD':            
		       $strServiceCode = '08'; 
		       break; 

		     default:            
		       $strServiceCode = '03'; 
		       break; 

			}

		return $strServiceCode;

	} # end method GetServiceCode()

	/**
	 * Will hit the UPS web service and return some
	 * rate information.
	 * 
	 * @param strDestinationZip string
	 * 
	 * @param strServiceShortName string
	 * 
	 * @param decPackageLength decimal
	 * 
	 * @param decPackageWidth decimal
	 * 
	 * @param decPackageHeight decimal
	 * 
	 * @param decPackageWeight decimal
	 * 
	 * @param boolReturnPriceOnly boolean
	 * 
	 * @return decimal/object Depends on the 
	 * argument boolReturnPriceOnly
	 **/
    function GetShippingRate($strDestinationZip, $strServiceShortName='GND', $strPackageLength=18, $strPackageWidth=12, $strPackageHeight=4, $strPackageWeight=2, $boolReturnPriceOnly=true) {

		$strServiceCode = $this->GetServiceCode($strServiceShortName);
	
		$strXml ="<?xml version=\"1.0\"?>  
		<AccessRequest xml:lang=\"en-US\">  
		    <AccessLicenseNumber>{$this->strAccessLicenseNumber}</AccessLicenseNumber>  
		    <UserId>{$this->strUserId}</UserId>  
		    <Password>{$this->strPassword}</Password>  
		</AccessRequest>  
		<?xml version=\"1.0\"?>  
		<RatingServiceSelectionRequest xml:lang=\"en-US\">  
		    <Request>  
			<TransactionReference>  
			    <CustomerContext>Bare Bones Rate Request</CustomerContext>  
			    <XpciVersion>1.0001</XpciVersion>  
			</TransactionReference>  
			<RequestAction>Rate</RequestAction>  
			<RequestOption>Rate</RequestOption>  
		    </Request>  
		<PickupType>  
		    <Code>01</Code>  
		</PickupType>  
		<Shipment>  
		    <Shipper>  
			<Address>  
			    <PostalCode>{$this->strShipperZip}</PostalCode>  
			    <CountryCode>US</CountryCode>  
			</Address>  
		    <ShipperNumber>{$this->strShipperNumber}</ShipperNumber>  
		    </Shipper>  
		    <ShipTo>  
			<Address>  
			    <PostalCode>{$strDestinationZip}</PostalCode>  
			    <CountryCode>US</CountryCode>  
			<ResidentialAddressIndicator/>  
			</Address>  
		    </ShipTo>  
		    <ShipFrom>  
			<Address>  
			    <PostalCode>{$this->strShipperZip}</PostalCode>  
			    <CountryCode>US</CountryCode>  
			</Address>  
		    </ShipFrom>  
		    <Service>  
			<Code>{$strServiceCode}</Code>  
		    </Service>  
		    <Package>  
			<PackagingType>  
			    <Code>02</Code>  
			</PackagingType>  
			<Dimensions>  
			    <UnitOfMeasurement>  
				<Code>IN</Code>  
			    </UnitOfMeasurement>  
			    <Length>{$strPackageLength}</Length>  
			    <Width>{$strPackageWidth}</Width>  
			    <Height>{$strPackageHeight}</Height>  
			</Dimensions>  
			<PackageWeight>  
			    <UnitOfMeasurement>  
				<Code>LBS</Code>  
			    </UnitOfMeasurement>  
			    <Weight>{$strPackageWeight}</Weight>  
			</PackageWeight>  
		    </Package>  
		</Shipment>  
		</RatingServiceSelectionRequest>";  

		$rsrcCurl = curl_init($this->strRateWebServiceLocation);  

		curl_setopt($rsrcCurl, CURLOPT_HEADER, 0);
		curl_setopt($rsrcCurl, CURLOPT_POST, 1);
		curl_setopt($rsrcCurl, CURLOPT_TIMEOUT, 60);
		curl_setopt($rsrcCurl, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($rsrcCurl, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt($rsrcCurl, CURLOPT_SSL_VERIFYHOST, 0);  
		curl_setopt($rsrcCurl, CURLOPT_POSTFIELDS, $strXml);  

		$strResult = curl_exec($rsrcCurl);
		if($this->boolDebugMode) echo "<!--{$strResult}-->";		

		$objResult = new SimpleXMLElement($strResult);
		if($this->boolDebugMode) print_r($objResult);

		curl_close($rsrcCurl);
		
		// Return either the decimal string value that is the rate
		if($boolReturnPriceOnly) {

			return (string) $objResult->RatedShipment->TotalCharges->MonetaryValue;

		// Or return the full object and do with it what you want
		} else {

			return $objResult;
		}

	} # end method GetShippingRate()

} # end class UpsShippingQuote

?>
