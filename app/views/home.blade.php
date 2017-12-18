@extends('lu.layouts.default')

<?php
$page_title = 'MFLF/KLC App Portal';
?>

@section('content')

<script type="text/javascript">
var content =
{
    /*main panel*/
    flex : 1, padding : 1,
    layout:
    {
        type: 'hbox'
    },
    items :
    [
        {
            id: 'menu_panel', xtype : 'panel', width:250, html : 'main menu'
            /*menu below*/
        },
        {
            id: 'main_panel', xtype : 'panel', flex:1, html : '6666'
            /*main below*/
        },
        {
            id: 'widget_panel', xtype : 'panel', width:250, html : 'widget'
            /*widget below*/
        }
    ]
};
</script>

@stop