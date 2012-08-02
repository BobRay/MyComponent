<?php
            $pluginId = null;
            $pluginObj = null;
            $plugin = '[[+plugin]]';
            $events = '[[+events]]';
            $events = explode(',', $events);
            $pluginObj = $modx->getObject('modPlugin', array('name'=> $plugin));
            if (!$pluginObj) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, 'Could not get ' . $plugin . ' ' . ' plugin');
            } else {
                $pluginId = $pluginObj->get('id');
            }
            foreach ($events as $event) {
                $mpe = $modx->getObject('modPluginEvent', array('event' => $event, 'pluginid' => $pluginId));
                if (! $mpe) {
                    $mpe = $modx->newObject('modPluginEvent');
                }
                if ($mpe && $pluginObj) {
                    $mpe->set('event', $event);
                    $mpe->set('pluginid', $pluginId);
                    $mpe->set('priority', 0);
                    $mpe->set('propertyset', 0);
                    if ($mpe->save()) {
                        $this->modx->log(MODX::LOG_LEVEL_INFO, 'Attached ' . $plugin . ' plugin to ' . $event . ' event');
                    }
                }
            }
