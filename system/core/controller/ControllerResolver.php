<?php
defined("IN_GOMA") OR die();

/**
 * Resolves Controller for given Model.
 *
 * @package Goma
 *
 * @author Goma Team
 * @copyright 2016 Goma Team
 *
 * @version 1.0
 */
class ControllerResolver {
    /**
     * returns controller classname for given model.
     * @param ViewAccessableData $model
     * @return string|null
     */
    public static function controllerClassForModel($model) {
        $dataClass = $model->DataClass();

        $reflectionClass = new ReflectionClass($model->DataClass());
        if($reflectionClass->hasProperty("controller")) {
            $property = $reflectionClass->getProperty("controller");
            if(ClassManifest::isSameClass($property->getDeclaringClass()->name, $dataClass)) {
                $property->setAccessible(true);

                if ($property->isStatic()) {
                    $value = $property->getValue();
                } else {
                    if ($model->classname == $dataClass) {
                        $value = $property->getValue($model);
                    } else {
                        $value = $property->getValue(gObject::instance($dataClass));
                    }
                }
                if (ClassInfo::exists($value)) {
                    return $value;
                }
            }
        }

        if (ClassInfo::exists($dataClass . "controller"))
        {
            return $dataClass . "controller";
        } else if (ClassInfo::getParentClass($dataClass) != "viewaccessabledata") {
            $parent = $dataClass;
            while(($parent = ClassInfo::getParentClass($parent)) != "viewaccessabledata") {
                if (!$parent)
                    return null;

                if (ClassInfo::exists($parent . "controller")) {
                    return $parent . "controller";
                }
            }
        }

        return null;
    }

    /**
     * creates an instance of a controller by given model.
     * @param ViewAccessableData $model
     * @return Controller|null
     */
    public static function instanceForModel($model) {
        if($class = self::controllerClassForModel($model)) {
            return gObject::instance($class)->setModelInst($model);
        }

        return null;
    }
}
