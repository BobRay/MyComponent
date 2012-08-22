<?php
            $propertySetObj = null;
            $elementObj = null;
            $propertySet = '[[+propertySet]]';
            $elements = '[[+elements]]';
            $elements = explode(',', $elements);

            $propertySetObj = $modx->getObject('modPropertySet', array('name'=> $propertySet));

            foreach ($elements as $element) {
                $element = explode(':', $element);
                $elementName = trim($element[0]);
                $elementType = trim($element[1]);
                $alias = getNameAlias($elementType);
                $elementObj = $modx->getObject($elementType, array($alias => $elementName));
                if ($propertySetObj && $elementObj) {
                    $propertySetId = $propertySetObj->get('id');
                    $elementId = $elementObj->get('id');
                    $elementPropertySet = $modx->getObject('modElementPropertySet', array('property_set' => $propertySetId, 'element' => $elementId));
                    if (! $elementPropertySet) {
                        $tvt = $modx->newObject('modElementPropertySet');
                    }
                    if ($elementPropertySet) {
                        $elementPropertySet->set('property_set', $propertySetId);
                        $elementPropertySet->set('element', $elementId);
                        $elementPropertySet->set('element_class', $elementType);
                        $elementPropertySet->save();
                    }
                }
            }

