<?php
namespace Edu\Cnm\AbqVast;

require_once("autoload.php");
require_once("/etc/apache2/capstone-mysql/encrypted-config.php");

/**
 * this Class will download data from the City of Albuquerque City Database.
 *
 * @author Valente Meza vmeza3@cnm.edu
 **/
class DataDownloader {
    /**
     * Vendor checkbook: http://data.cabq.gov/government/vendorcheckbook/VendorCheckBookCABQ-en-us.xml
     */
    /**
     * Gets the metadata from a file url
     *
     * @param string %url to grab from
     * @param string $eTag to be compared to previous etag to determine last download
     * @return string $eTag to be compared to previous etag to determine last download
     * @throws \RuntimeException if file doesn't exist
     **/

    public static function getMetaData(string $url, string $eTag) {
        if($eTag !== "checkbook") {
            throw(new \RuntimeException("not a valid etag, 400"));
        }
        $options = [];
        $options["http"] = [];
        $options["http"] ["method"] = "HEAD";
        $context = stream_context_create($options);

        // "@" suppresses warnings and errors, fopen opens the actual file
        $fd = fopen($url, "rb", false, $context);
        $metaData = stream_get_meta_data($fd);
        if($fd === false) {
            throw(new \RuntimeException("unable to open HTTP stream"));
        }
        fclose($fd);
        $header = $metaData["wrapper_data"];
        foreach($header as $value) {
            $explodeETag = explode(": ", $value);
            $findETag = array_search("ETag", $explodeETag);
            if($findETag !== false) {
                $eTag = $explodeETag[1];
            }
        }

        if($eTag === null) {
            throw(new \RuntimeException("etag cannot be found", 404));
        }

        $config = readConfig("/etc/apache2/capstone-mysql/abq-vast.ini");
        $eTag = xmlstr($config["etag"]);
        $previousETag = $eTag->$metaData;
        if($previousETag < $eTag) {
            return ($eTag);
        } else {
            return($previousETag);
        }

    }
    public static function BasicSimpleXML($xmlstr) {
        $xmlstr = <<<XML
<?xml version='1.0' standalone='yes' ?>
<checkbook>
<vendorname>
<invoiceamount></invoiceamount>
<invoicedate></invoicedate>
<invoicenum></invoicenum>
<paymentdate></paymentdate>
<referencenum></referencenum>
</vendorname>
</checkbook>
XML;
    }
}

$meta = DataDownloader::getMetaData("http://data.cabq.gov/government/vendorcheckbook/VendorCheckBookCABQ-en-us.xml");