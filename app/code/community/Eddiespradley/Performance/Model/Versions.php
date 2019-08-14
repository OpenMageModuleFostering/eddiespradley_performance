<?php
class Eddiespradley_Performance_Model_Versions extends Mage_Core_Model_Abstract
{

	function getMysqlVersion()
	{
	    $resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	     
	    $query = "SHOW VARIABLES WHERE variable_name = 'version'";

	    $version = $readConnection->fetchAll($query);

	    return $version[0]['Value'];
	}

	function getMysqlSize()
	{
	    $resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	     
	    $query = 'SELECT table_schema "name", 
					Round(Sum(data_length + index_length), 1) "value"
					FROM   information_schema.tables 
					GROUP  BY table_schema';

	    $version = $readConnection->fetchAll($query);

	    return $version;
	}

	function getMysqlVersionNotes()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	     
	    $query = "SHOW VARIABLES WHERE variable_name = 'version_comment'";

	    $version = $readConnection->fetchAll($query);

	    return $version[0]['Value'];
		
	}

}
