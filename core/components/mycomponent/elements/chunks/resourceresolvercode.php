<?php

            $resourceObj = null;
            $templateObj = null;
            $template = '[[+template]]';
            $resources = '[[+resources]]';
            $resources = explode(',', $resources);

            $templateObj = $modx->getObject('modTemplate', array('templatename'=> $template));

            foreach ($resources as $resource) {
                $resourceObj = $modx->getObject('modResource', array('pagetitle'=> $resource));
                if ($resourceObj && $templateObj) {
                    $resourceObj->set('template', $templateObj->get('id'));
                    $resourceObj->save();
                }
            }

