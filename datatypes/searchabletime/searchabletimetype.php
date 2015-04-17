<?php
/**
 * File containing the searchableTimeType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 * @package kernel
 */


class searchableTimeType extends eZTimeType
{
    const DATA_TYPE_STRING = "searchabletime";

    function searchableTimeType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "Searchable Time", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    function isIndexable()
    {
        return true;
    }
}

eZDataType::register( searchableTimeType::DATA_TYPE_STRING, "searchableTimeType" );

?>
