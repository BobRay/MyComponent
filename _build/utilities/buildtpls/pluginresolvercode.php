<?php
            $pluginId = null;
            $pluginObj = null;
            $mpes = array();
            $plugin = '[[+plugin]]';
            $events = '[[+events]]';
            $events = explode(',', $events);
            $pluginObj = $modx->getObject('modPlugin', array('name' => $plugin));
            if ($pluginObj) {
                $pluginId = $pluginObj->get('id');
                foreach ($events as $event) {

                    $pluginEvent = $modx->getObject('modPluginEvent', array(
                        'pluginid' => $pluginId,
                        'event' => $event,
                    ));
                    if ($pluginEvent == null) {
                        $pluginEvent = $modx->newObject('modPluginEvent');
                        $pluginEvent->set('pluginid', $pluginId);
                        $pluginEvent->set('event', $event);
                        $pluginEvent->set('priority', 0);
                        $pluginEvent->save();
                    }
                }
            }