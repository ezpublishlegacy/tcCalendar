<?php

/**
 * @package tcCalendar
 * @author  Serhey Dolgushev <serhey@contextualcode.com>
 * @date    26 Apr 2016
 * */

$fetchContainerParams = array(
    'Depth'            => false,
    'ClassFilterType'  => 'include',
    'ClassFilterArray' => array( 'folder' ),
    'AttributeFilter'  => array(
        array( 'folder/name', '=', 'Archive' )
    ),
    'LoadDataMap'      => false,
    'AsObject'         => false,
    'IgnoreVisibility' => true,
    'Limitation'       => array()
);

$date              = new DateTime();
$fetchEventsParams = array(
    'Depth'            => false,
    'ClassFilterType'  => 'include',
    'ClassFilterArray' => array( 'event' ),
    'AttributeFilter'  => array(
        'and',
        array( 'event/date_to', '<=', $date->format( 'U' ) ),
        array( 'event/date_to', '>', 0 )
    ),
    'LoadDataMap'      => false,
    'AsObject'         => false,
    'IgnoreVisibility' => false
);
$nodes             = eZContentObjectTreeNode::subTreeByNodeID( $fetchEventsParams, 2 );
$count             = count( $nodes );
foreach( $nodes as $key => $node ) {
    $object = eZContentObject::fetch( $node['contentobject_id'] );
    if( $object instanceof eZContentObject === false ) {
        continue;
    }

    $archiveNodes = eZContentObjectTreeNode::subTreeByNodeID( $fetchContainerParams, $node['parent_node_id'] );
    if( count( $archiveNodes ) > 0 ) {
        $newParentNodeID = $archiveNodes[0]['node_id'];
    } else {
        $parentName    = $object->attribute( 'main_node' )->attribute( 'parent' )->attribute( 'name' );
        $publishParams = array(
            'parent_node_id'   => $node['parent_node_id'],
            'class_identifier' => 'folder',
            'remote_id'        => $parentName . '_archive',
            'attributes'       => array(
                'name' => 'Archive'
            )
        );
        $archiveObject = eZContentFunctions::createAndPublishObject( $publishParams );
        if( $archiveObject === false ) {
            $cli->error( 'Unable to create archive container for ' . $parentName . ' (node ID: ' . $node['parent_node_id'] . ')' );
            continue;
        }

        $newParentNodeID = $archiveObject->attribute( 'main_node_id' );
    }

    $newParentNode = eZContentObjectTreeNode::fetch( $newParentNodeID );
    if( $newParentNode instanceof eZContentObjectTreeNode === false ) {
        $cli->error( 'Unable to fetch archive container node#' . $newParentNode );
        continue;
    }

    if( (bool) $newParentNode->attribute( 'is_invisible' ) === false ) {
        eZContentObjectTreeNode::hideSubTree( $newParentNode );
    }

    $cli->output( 'Archiving "' . $object->attribute( 'name' ) . '" (node ID:' . $object->attribute( 'main_node_id' ) . ')' );

    eZContentObjectTreeNodeOperations::move( $object->attribute( 'main_node_id' ), $newParentNode->attribute( 'node_id' ) );
    eZContentObject::fixReverseRelations( $object->attribute( 'id' ), 'move' );

    eZContentObject::clearCache( $object->attribute( 'id' ) );
    $object->resetDataMap();

    if( $key % 100 === 0 ) {
        $memoryUsage = number_format( memory_get_usage( true ) / (1024 * 1024), 2 );
        $output      = number_format( $key / $count * 100, 2 ) . '% (' . ($key + 1) . '/' . $count . ')';
        $output .= ', Memory usage: ' . $memoryUsage . ' Mb';
        $cli->output( $output );
    }
}

eZContentCacheManager::clearAllContentCache();
eZUser::cleanupCache();
