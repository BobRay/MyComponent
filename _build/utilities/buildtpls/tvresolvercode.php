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
            } else {
                $templateObj = $modx->getObject('modPlugin', array('templatename'=> $template));
                if (!$templateObj) {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, 'Could not get ' . $template . ' ' . ' template');
                } else {
                    $templateId = $templateObj->get('id');
                }
            }
            foreach ($tvs as $tv) {
                $tvObj = $modx->getObject('modTemplateVar', array('name'=> $tv));
                if ($tvObj) {
                    $tvId = $tvObj->get('id');
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, 'Could not get ' . $tv . ' ' . ' template variable');
                }
                if ($tvObj && $templateObj) {
                    $tvt = $modx->getObject('modTemplateVarTemplate', array('tmplvarid' => $tvId, 'templateid' => $templateId));
                    if (! $tvt) {
                        $tvt = $modx->newObject('modTemplateVarTemplate');
                    }
                    if ($tvt) {
                        $tvt->set('tmplvarid', $tvId);
                        $tvt->set('templateid', $templateId);
                        $tvt->set('rank', 0);
                        if ($tvt->save()) {
                            $this->modx->log(MODX::LOG_LEVEL_INFO, 'Attached ' . $tv . ' Template Variable to ' . $template . ' Template');
                        }
                    }
                }
            }

