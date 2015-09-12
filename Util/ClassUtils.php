<?php

/*
* This file is part of the Tempo-project package http://tempo-project.org/>.
*
* (c) Mlanawo Mbechezi  <mlanawo.mbechezi@ikimea.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tempo\Bundle\ResourceExtraBundle\Util;

use Doctrine\Common\Util\ClassUtils as BaseClassUtils;

class ClassUtils
{
    /**
     * @param $class
     * @return string
     */
    public static function getShortName($class, $capitalized = true)
    {
        if (is_object($class)) {
            $class = BaseClassUtils::getClass($class);
        }

        $resourceName = BaseClassUtils::newReflectionClass($class)->getShortName();

        if ($capitalized) {
            $resourceName = strtolower($resourceName);
        }

        return $resourceName;
    }

    /**
     * Reverse a CamelCase string.
     *
     * Examples:
     *     uncamel('lowerCamelCase') === 'lower_camel_case'
     *     uncamel('UpperCamelCase') === 'upper_camel_case'
     *     uncamel('ThisIsAString') === 'this_is_a_string'
     *     uncamel('notcamelcase') === 'notcamelcase'
     *     uncamel('lowerCamelCase', ' | ') === 'lower | camel | case'
     *
     * @link      http://stackoverflow.com/a/1993772/5103614
     *
     * @param  string $content The CamelCase string.
     * @param  string $separator The glue for the compound words. Defaults to '_'.
     * @return string
     */
    public static function uncamel($content, $separator = '_')
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $content, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode($separator, $ret);
    }
}
