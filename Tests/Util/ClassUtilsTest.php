<?php

/*
* This file is part of the Tempo-project package http://tempo-project.org/>.
*
* (c) Mlanawo Mbechezi  <mlanawo.mbechezi@ikimea.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tempo\Bundle\ResourceExtraBundle\Tests\Util;

use Tempo\Bundle\ResourceExtraBundle\Util\ClassUtils;

class ClassUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetShortName()
    {
        $this->assertEquals('classutils', ClassUtils::getShortName('Tempo\Bundle\ResourceExtraBundle\Util\ClassUtils'));
    }

    public function testGetShortNameNotCapitalized()
    {
        $this->assertEquals('ClassUtils', ClassUtils::getShortName('Tempo\Bundle\ResourceExtraBundle\Util\ClassUtils', false));
    }

    public function testGetShortNameWithInstance()
    {
        $this->assertEquals('classutils', ClassUtils::getShortName(new ClassUtils));
    }

    public function testUncamel()
    {
        $tests = array(
            'simpleTest' => 'simple_test',
            'easy' => 'easy',
            'HTML' => 'html',
            'simpleXML' => 'simple_xml',
            'PDFLoad' => 'pdf_load',
            'startMIDDLELast' => 'start_middle_last',
            'AString' => 'a_string',
            'Some4Numbers234' => 'some4_numbers234',
            'TEST123String' => 'test123_string',
        );

        foreach ($tests as $test => $result) {
            $this->assertEquals($result, ClassUtils::uncamel($test));
        }
    }
}
