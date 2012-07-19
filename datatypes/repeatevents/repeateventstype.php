<?php
/**
 * File containing repeatEventsType class
 *
 * @copyright Copyright (C) 1999-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU General Public License v2.0
 *
 */

class repeatEventsType extends eZDataType
{
    const DATA_TYPE_STRING = "repeatevents";

    /*!
     Construction of the class, note that the second parameter in eZDataType 
     is the actual name showed in the datatype dropdown list.
    */
	function __construct()
	{
	    parent::__construct( self::DATA_TYPE_STRING, 'Repeat Events', array( 'serialize_supported' => true ) );
	}

    /*!
      Validates the input and returns true if the input was
      valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $objectAttribute )
    {
        return eZInputValidator::STATE_ACCEPTED;
    }


    /*!
    */
	function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
	    if ( $http->hasPostVariable( $base . '_ezstring_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
	    {
			$start_time = $this->get_start_time( $contentObjectAttribute );
	        $data = $http->postVariable( $base . '_ezstring_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
			$data_r = explode("&start_time", $data);
			if (is_array($data_r)) $data = $data_r[0];
		    $data .= "&start_time=$start_time";
	        $contentObjectAttribute->setAttribute( 'data_text', $data );
			$repeatdata = new eZRepeatEvent( $data );
			$contentObjectAttribute->setContent( $repeatdata );
	        return true;
	    }
	    return false;
	}

    /*!
     Store the content. Since the content has been stored in function 
     fetchObjectAttributeHTTPInput(), this function is with empty code.
    */
    function storeObjectAttribute( $objectattribute )
    {
    }

    /*!
     Returns the meta data used for storing search indices.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     Returns the text.
    */
    function title( $objectAttribute, $name = null)
    {
        return $this->metaData( $objectAttribute );
    }

    function isIndexable()
    {
        return true;
    }

	function get_start_time( $objectAttribute ) {
		$calini = eZINI::instance( 'tccalendar.ini' );
		$EventClassStartDateAttributes=$calini->variable( "ClassSettings", "EventClassStartDateAttributes");
		$classId = $objectAttribute->contentClassAttribute()->ContentClassID;
		if (array_key_exists($classId, $EventClassStartDateAttributes)) {
			$dm = $objectAttribute->object()->dataMap();
			$contentObjectAttribute = eZContentObjectAttribute::fetchByIdentifier('event/'.$EventClassStartDateAttributes[$classId]);
			$atts = eZContentObject::fetch($objectAttribute->attribute('contentobject_id'))->currentVersion()->contentObjectAttributes();
			$myat = false;
			foreach ($atts as $at) {
				if ($at->ContentClassAttributeIdentifier == $EventClassStartDateAttributes[$classId]) {
					$myat = $at;
					break;
				}
			}
			if ($myat) {
				$http = eZHTTPTool::instance();
				$imp = $myat->fetchInput( $http, 'ContentObjectAttribute' );
				return $myat->attribute('data_int');
			} else {
				return time();
			}	
		} else {
			return time();
		}
	}

    function sortKey( $objectAttribute )
    {
        return $this->objectAttributeContent($objectAttribute)->get_end_time();
    }
  
    function sortKeyType()
    {
        return 'int';
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( 'data_text' ) ) != '';
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
		$repeatdata = new eZRepeatEvent( $contentObjectAttribute->attribute( 'data_text' ), $contentObjectAttribute);
		return $repeatdata;
    }

    function objectDisplayInformation( $objectAttribute, $mergeInfo = false )
    {
        $info = array( 'edit' => array( 'grouped_input' => true ),
                       'collection' => array( 'grouped_input' => true ) );
        return eZDataType::objectDisplayInformation( $objectAttribute, $info );
    }

}

eZDataType::register( repeatEventsType::DATA_TYPE_STRING, 'repeatEventsType' );
?>
