<?php
$plugin = '[[+plugin]]';
$events = '[[+events]]';
$events = explode(',', $events);
$pluginObj = $modx->getObject('modPlugin', array('name'=> $plugin));
$pluginId = $pluginObj->get('id');
foreach ($events as $event) {
    $mpe = $modx->getObject('modPluginEvent', array('event' => $event, 'pluginid' => $pluginId));
    if (! $mpe) {
        $e = $modx->newObject('modPluginEvent');
        if ($e) {
            $e->set('event', $event);
            $e->set('pluginid', $pluginId);
            $e->set('priority', 0);
            $e->set('propertyset', 0);
            $e->save();
        }
    }
}