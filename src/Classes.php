<?php

namespace CodexSoft\Code\Classes;

class Classes
{

    /**
     * Returns name of the first (in class hierarchy) common parent class of all provided objects or classes.
     * Returns FALSE when common class is not found.
     *
     * Example:
     *
     * class A {
     * }
     *
     * class B extends A {
     * }
     *
     * class D extends B {
     * }
     *
     * class E extends B {
     * }
     *
     * class C extends A {
     * }
     *
     * class F extends C {
     * }
     *
     * class G extends F {
     * }
     *
     * class H {
     * }
     *
     * //returns "A"
     * get_first_common_parent(array('G', 'E'));
     *
     * //returns "F"
     * get_first_common_parent(array(new G(), 'F'));
     *
     * //returns false (no common parent)
     * get_first_common_parent(array('C', 'H'));
     *
     * //returns false (non-existent class provided)
     * get_first_common_parent(array(new B(), 'X'));
     *
     * @param array $objects Array that can contain objects or class names.
     * @return mixed
     */
    public static function firstCommonParent($objects)
    {

        $common_ancestors = null;
        foreach($objects as $object) {
            if (is_object($object)) {
                $class_name = get_class($object);
            } else {
                $class_name = $object;
            }

            $parent_class_names = array();
            $parent_class_name = $class_name;
            do {
                $parent_class_names[] = $parent_class_name;
            } while($parent_class_name = get_parent_class($parent_class_name));

            if ($common_ancestors === null) {
                $common_ancestors = $parent_class_names;
            } else {
                $common_ancestors = array_intersect($common_ancestors, $parent_class_names);
            }
        }

        return reset($common_ancestors);

    }

    /**
     * Получить массив с родительскими классами
     * E extends D, D extends C, C extends B, B extends A
     * getParentClasses(E) = [D,C,B,A]
     * getParentClasses(E,B) = [D,C]
     * @param $class
     * @param null $until
     *
     * @return string[]
     */
    public static function getParentClasses( $class, $until = null ): array
    {

        if ( \is_object($class) ) {
            $class = \get_class( $class );
        }

        $parents = [];
        while ( ( $parent = get_parent_class( $class ) ) && ( !$until || $parent !== $until ) ) {
            $parents[] = $parent;
            $class = $parent;
        }

        return $parents;

    }

    ///**
    // * Вернет TRUE если $class implements $interface
    // * @param string $class
    // * @param string $interface
    // *
    // * @return bool
    // */
    //public static function implement(string $class, string $interface): bool
    //{
    //    return \in_array($interface, class_implements($class), true);
    //}

    /**
     * @param $class - className or object
     * @param $interface
     *
     * @return bool
     */
    public static function isImplements($class, $interface): bool
    {
        return in_array($interface, class_implements($class));
    }

    ///**
    // * static version
    // * @param $ancestor
    // * @param $parentClass
    // *
    // * @return bool
    // */
    //public static function getIsSameOrExtends( $ancestor, $parentClass ) {
    //    return static::tool()->isSameOrExtends($ancestor, $parentClass);
    //}

    /**
     * @param string|\Object $ancestor className or object
     * @param string|\Object $parent className or object
     *
     * @return bool
     */
    public static function isSameOrExtends($ancestor, $parent): bool
    {
        $ancestorClass = \is_object( $ancestor ) ? \get_class( $ancestor ) : $ancestor;
        $parentClass = \is_object( $parent ) ? \get_class( $parent ) : $parent;
        return ( $ancestorClass === $parentClass || is_subclass_of($ancestorClass,$parentClass) );
    }

    public static function isNamespaced($className): bool
    {
        return (substr_count( $className, "\\" ) + 1 > 1);
    }

    public static function getNamespace($className): string
    {

        $parts = explode('\\', $className);

        if ( \count($parts) === 1) {
            return '';
        }

        array_pop($parts);
        return implode('\\',$parts);
    }

    /**
     * Returns just a class name
     * # for LE\Application\Buro\Standard returns Standard
     *
     * @param object|string $namespacedClassName
     *
     * @return string
     */
    public static function shortClass($namespacedClassName): string
    {
        $className = $namespacedClassName;
        if (\is_object($namespacedClassName)) {
            $className = \get_class( $namespacedClassName );
        }

        $className = str_replace("\\", '/', $className);
        $className = basename( $className );
        return basename( $className );
    }

    //public static function short( $NamespacedClassName ) {
    //    return self::tool()->shortClass($NamespacedClassName);
    //}

    /**
     * Returns the parameters of a function or method.
     *
     * @param \ReflectionMethod $method
     * @param bool             $forCall
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function getMethodParameters(\ReflectionMethod $method, $forCall = false)
    {
        $parameters = [];

        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();

            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }

            if ($parameter->isVariadic()) {
                if ($forCall) {
                    continue;
                }

                $name = '...' . $name;
            }

            $nullable        = '';
            $default         = '';
            $reference       = '';
            $typeDeclaration = '';

            if (!$forCall) {
                if (PHP_VERSION_ID >= 70100 && $parameter->hasType() && $parameter->allowsNull()) {
                    $nullable = '?';
                }

                if ($parameter->hasType() && (string) $parameter->getType() !== 'self') {
                    if (\in_array($parameter->getType(), ['string', 'int', 'float', 'bool'])) {
                        $typeDeclaration = (string) $parameter->getType() . ' ';
                    } else {
                        $typeDeclaration = (string) '\\'.$parameter->getType() . ' ';
                    }

                } elseif ($parameter->isArray()) {
                    $typeDeclaration = 'array ';
                } elseif ($parameter->isCallable()) {
                    $typeDeclaration = 'callable ';
                } else {
                    try {
                        $class = $parameter->getClass();
                    } catch (\ReflectionException $e) {
                        throw new \RuntimeException(
                            \sprintf(
                                'Cannot mock %s::%s() because a class or ' .
                                'interface used in the signature is not loaded',
                                $method->getDeclaringClass()->getName(),
                                $method->getName()
                            ),
                            0,
                            $e
                        );
                    }

                    if ($class !== null) {
                        $typeDeclaration = $class->getName() . ' ';
                    }
                }

                if (!$parameter->isVariadic()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $value   = $parameter->getDefaultValue();
                        $default = ' = ' . \var_export($value, true);
                    } elseif ($parameter->isOptional()) {
                        $default = ' = null';
                    }
                }
            }

            if ($parameter->isPassedByReference()) {
                $reference = '&';
            }

            $parameters[] = $nullable . $typeDeclaration . $reference . $name . $default;
        }

        return \implode(', ', $parameters);
    }

    /**
     * @param string $methodName
     * @param string|\ReflectionClass $interfaceClass
     *
     * @param bool $searchInInterfaceClass
     * @param bool $recursiveSearch
     *
     * @param array $skipCheckOfInterfaces
     *
     * @return bool
     * @throws \ReflectionException
     */
    public static function isMethodDeclaredInInterface(string $methodName, $interfaceClass, $searchInInterfaceClass = true, $recursiveSearch = false, $skipCheckOfInterfaces = []): bool
    {

        if (!$interfaceClass instanceof \ReflectionClass) {
            $interfaceClass = new \ReflectionClass($interfaceClass);
        }

        if ($searchInInterfaceClass) {
            if ($interfaceClass->hasMethod($methodName)) {
                return true;
            }
        }

        if ($recursiveSearch) {
            $usedInterfaces = $interfaceClass->getInterfaces();
            foreach($usedInterfaces as $usedInterface) {
                if (\in_array($usedInterface->getName(),$skipCheckOfInterfaces,true)) { continue; }
                if (self::isMethodDeclaredInInterface($methodName,$usedInterface,$recursiveSearch)) {
                    return true;
                }
            }

        }

        return false;

    }

    /**
     * returns array with keys are constant values and values are constant names
     * @param $class
     * @param string $prefix
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function grabConstantsFromClass($class, $prefix = ''): array
    {
        $oClass = ($class instanceof \ReflectionClass)
            ? $class
            : new \ReflectionClass($class);

        $constants = $oClass->getConstants();
        $result = [];

        foreach ($constants as $constant => $value) {
            if (!$prefix || \mb_strpos($constant, $prefix) === 0) {
                // todo: values can be same, so some information can be missed!!!
                $result[$value] = $constant;
            }
        }

        return $result;
    }

    /**
     * returns array with keys are constant names and values are constant values
     * constants names must start with prefix if is provided
     *
     * @param string $class
     * @param string $prefix
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function grabPrefixedConstantsFromClass(string $class, string $prefix = ''): array
    {
        $oClass = new \ReflectionClass($class);
        $constants = $oClass->getConstants();
        $result = [];

        foreach ($constants as $constant => $value) {
            if (!$prefix || \mb_strpos($constant, $prefix) === 0) {
                $result[$constant] = $value;
            }
        }

        return $result;
    }

    /**
     * Вернуть ReflectionProperty единственного свойства класса, либо null если оно не единственное
     *
     * @param string $class
     *
     * @return null|\ReflectionProperty
     */
    public static function getSinglePublicReflectionPropertyOrNull(string $class): ?\ReflectionProperty
    {
        $publicPropertiesCount = 0;
        $lastReflectionProperty = null;
        try {
            $oClass = new \ReflectionClass($class);
            $props = $oClass->getProperties();
            foreach ($props as $prop) {
                if ($prop->isPublic()) {
                    $publicPropertiesCount++;
                    $lastReflectionProperty = $prop;
                }
            }

            if ($publicPropertiesCount === 1) {
                return $lastReflectionProperty;
            }

        } catch (\ReflectionException $e) {
        }

        return null;

    }

    /**
     * Посчитать количество публичных свойств в классе
     * @param string $class
     *
     * @return int|null
     */
    public static function publicPropertiesCountOrNull(string $class): ?int
    {
        $count = 0;
        try {
            $oClass = new \ReflectionClass($class);
            $props = $oClass->getProperties();
            foreach ($props as $prop) {
                if ($prop->isPublic()) {
                    $count++;
                }
            }
            return $count;
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    //public static function publicPropertiesCount($classOrObjectOrClassReflection)
    //{
    //    if (\is_string($classOrObjectOrClassReflection)) {
    //        class
    //    }
    //}

    /**
     * Get constant name from class constants (constants set can be restricted using prefix)
     * should be used as fallback for annotations
     *
     * @param $value
     * @param string $class
     * @param string $constantPrefix
     *
     * @return null|string
     */
    public static function getConstantNameByValue($value, string $class, string $constantPrefix = ''): ?string
    {
        try {
            $constants = self::grabPrefixedConstantsFromClass($class,$constantPrefix);
            foreach ($constants as $constantName => $constantValue) {
                if ($constantValue === $value) {
                    return $constantName;
                }
            }
        } catch (\ReflectionException $e) {
        }
        return null;
    }

    /**
     * Вернет TRUE если $class implements $interface
     *
     * @param string $class
     * @param string $interface
     *
     * @return bool
     */
    public static function implement(string $class, string $interface): bool
    {
        return \in_array($interface, class_implements($class), true);
    }

    /**
     * static version
     *
     * @param $ancestor
     * @param $parentClass
     *
     * @return bool
     */
    public static function getIsSameOrExtends($ancestor, $parentClass)
    {
        return self::isSameOrExtends($ancestor, $parentClass);
    }

    /**
     * @param object|string $namespacedClassName
     *
     * @return string
     */
    public static function short($namespacedClassName): string
    {
        return self::shortClass($namespacedClassName);
    }

    protected static function generateMethodWithoutBody(\ReflectionMethod $entityMethod): string
    {
        $name = $entityMethod->getName();

        $parametersString = self::getMethodParameters($entityMethod);

        $isAbstract = $entityMethod->isAbstract() ? 'abstract ' : '';
        $isStatic = $entityMethod->isStatic() ? ' static' : '';

        /** @noinspection NestedTernaryOperatorInspection */
        $visibility = $entityMethod->isPublic()
            ? 'public'
            : ($entityMethod->isPrivate() ? 'private' : 'protected');

        $definition = "\n";

        $definition .= "    $isAbstract$visibility$isStatic function $name(";

        $definition .= $parametersString;
        $definition .= ')';

        if ($entityMethod->hasReturnType()) {
            /** @var \ReflectionType $returnType */
            $returnType = $entityMethod->getReturnType();
            $definition .= ': ';

            if ($returnType->allowsNull()) {
                $definition .= '?';
            }
            if (!$returnType->isBuiltin() && ($returnType->getName() !== 'self')) {
                $definition .= "\\";
            }

            $definition .= $returnType;

        }

        $definition .= ';';

        return $definition;
    }

}
