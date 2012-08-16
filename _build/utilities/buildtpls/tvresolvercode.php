<?php
            $tvObj = null;
            $templateObj = null;
            $tvId = 0;
            $templateId = 0;
            $template = '[[+template]]';
            $tvs = '[[+tvs]]';
            $tvs = explode(',', $tvs);
            if ($template == 'default') {
                $templateId = $modx->getOption('default_template');
                $templateObj = $modx->getObject('modTemplate', (integer) $templateId);
            } else {
                $templateObj = $modx->getObject('modTemplate', array('templatename'=> $template));
                if ($templateObj) {
                    $templateId = $templateObj->get('id');
                }
            }
            foreach ($tvs as $tv) {
                $tvObj = $modx->getObject('modTemplateVar', array('name'=> $tv));
                if ($tvObj && $templateObj) {
                    $tvId = $tvObj->get('id');
                    $tvt = $modx->getObject('modTemplateVarTemplate', array('tmplvarid' => $tvId, 'templateid' => $templateId));
                    if (! $tvt) {
                        $tvt = $modx->newObject('modTemplateVarTemplate');
                    }
                    if ($tvt) {
                        $tvt->set('tmplvarid', $tvId);
                        $tvt->set('templateid', $templateId);
                        $tvt->set('rank', 0);
                        $tvt->save();
                    }
                }
            }

