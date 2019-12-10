<?php
/**
 * Created by PhpStorm.
 * User: dx
 * Date: 30.05.18
 * Time: 0:27
 */

namespace CodexSoft\Code\Classes;

use CodexSoft\Code\Classes\ClassesTestData\ChildClass;
use CodexSoft\Code\Classes\ClassesTestData\GrandparentClass;
use CodexSoft\Code\Classes\ClassesTestData\ParentClass;
use PHPUnit\Framework\TestCase;

class ClassesTest extends TestCase
{

    public function testIsSameOrExtends()
    {

        $child = new ChildClass();
        $parent = new ParentClass();
        $grandparent = new GrandparentClass();

        $this->assertTrue(Classes::getIsSameOrExtends($child, $child));
        $this->assertTrue(Classes::getIsSameOrExtends($child, $parent));
        $this->assertTrue(Classes::getIsSameOrExtends($child, $grandparent));

        $this->assertTrue(Classes::getIsSameOrExtends(ChildClass::class, $child));
        $this->assertTrue(Classes::getIsSameOrExtends(ChildClass::class, $parent));
        $this->assertTrue(Classes::getIsSameOrExtends(ChildClass::class, $grandparent));

        $this->assertTrue(Classes::getIsSameOrExtends(ChildClass::class, ChildClass::class));
        $this->assertTrue(Classes::getIsSameOrExtends(ChildClass::class, ParentClass::class));
        $this->assertTrue(Classes::getIsSameOrExtends(ChildClass::class, GrandparentClass::class));

        $this->assertTrue(Classes::getIsSameOrExtends($child, ChildClass::class));
        $this->assertTrue(Classes::getIsSameOrExtends($child, ParentClass::class));
        $this->assertTrue(Classes::getIsSameOrExtends($child, GrandparentClass::class));

        $this->assertTrue(Classes::getIsSameOrExtends($parent, $parent));
        $this->assertTrue(Classes::getIsSameOrExtends($parent, $grandparent));

        $this->assertTrue(Classes::getIsSameOrExtends(ParentClass::class, $parent));
        $this->assertTrue(Classes::getIsSameOrExtends(ParentClass::class, $grandparent));

        $this->assertTrue(Classes::getIsSameOrExtends(ParentClass::class, ParentClass::class));
        $this->assertTrue(Classes::getIsSameOrExtends(ParentClass::class, GrandparentClass::class));

        $this->assertTrue(Classes::getIsSameOrExtends($parent, ParentClass::class));
        $this->assertTrue(Classes::getIsSameOrExtends($parent, GrandparentClass::class));

        $this->assertTrue(Classes::getIsSameOrExtends($grandparent, $grandparent));
        $this->assertTrue(Classes::getIsSameOrExtends(GrandparentClass::class, $grandparent));
        $this->assertTrue(Classes::getIsSameOrExtends(GrandparentClass::class, GrandparentClass::class));
        $this->assertTrue(Classes::getIsSameOrExtends($grandparent, GrandparentClass::class));

    }
}
