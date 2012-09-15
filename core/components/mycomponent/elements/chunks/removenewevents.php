<?php
            /* Remove new System Events created during install */

            $events = '[[+newEvents]]';
            $events = empty($events)? array() : explode(',', $events);
            /* @var $e modEvent */
            foreach ($events as $event) {
                $e = $modx->getObject('modEvent', array('name' => $event));
                if ($e) {
                    $e->remove();
                }
            }