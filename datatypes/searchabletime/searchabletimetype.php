<?php

class searchableTimeType extends eZTimeType
{

    function isIndexable()
    {
        return true;
    }
}

eZDataType::register( searchableTimeType::DATA_TYPE_STRING, "searchableTimeType" );

?>
