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
                    if (strstr($event, ':')) {
                        $data = explode(':', $event);
                        $event = trim($data[0]);
                        $priority = (integer) trim($data[1]);
                    } else {
                        $priority = 0;
                    }
                    $pluginEvent = $modx->getObject('modPluginEvent', array(
                        'pluginid' => $pluginId,
                        'event' => $event,
                    ));
                    if ($pluginEvent == null) {
                        $pluginEvent = $modx->newObject('modPluginEvent');
                        $pluginEvent->set('pluginid', $pluginId);
                        $pluginEvent->set('event', $event);
                        $pluginEvent->set('priority', $priority);
                        $pluginEvent->save();
                    }
                }
            }