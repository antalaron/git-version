<?php

/*
 * This file is part of GitVersion.
 *
 * (c) Antal Áron <antalaron@antalaron.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Antalaron\GitVersion;

use PHPUnit\Framework\TestCase as BaseTestCase;

if (class_exists(BaseTestCase::class)) {
    class_alias(BaseTestCase::class, 'Tests\Antalaron\GitVersion\TestCase');
} else {
    class_alias('PHPUnit_Framework_TestCase', 'Tests\Antalaron\GitVersion\TestCase');
}
