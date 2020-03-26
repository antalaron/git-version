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

use Antalaron\GitVersion\GitVersion;

/**
 * GitVersionTest.
 *
 * @author Antal Áron <antalaron@antalaron.hu>
 */
class GitVersionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        putenv('GIT_DOT_DIR=_git');
    }

    public function tearDown()
    {
        putenv('GIT_DOT_DIR=');
    }

    public function testCorrectVersion()
    {
        $gitVersion = $this->getObject();

        $this->assertSame('b302440fd9972a341f2ecbe50d347b9fbd5b9f0b', $gitVersion->getVersion(__DIR__.'/Fixtures/correct'));
    }

    public function testCorrectVersionUnderSubdirs()
    {
        $gitVersion = $this->getObject();

        $this->assertSame('b302440fd9972a341f2ecbe50d347b9fbd5b9f0b', $gitVersion->getVersion(__DIR__.'/Fixtures/correct/subdir/subdir'));
    }

    public function testCorrectVersionWithLength()
    {
        $gitVersion = $this->getObject();

        $this->assertSame('b302440', $gitVersion->getVersion(__DIR__.'/Fixtures/correct', 7));
    }

    public function testNoGitDirectory()
    {
        $gitVersion = $this->getObject();

        $this->assertNull($gitVersion->getVersion(__DIR__.'/Fixtures/no-git-dir'));
    }

    public function testHeadRefFile()
    {
        $gitVersion = $this->getObject();

        $this->assertNull($gitVersion->getVersion(__DIR__.'/Fixtures/no-head-file'));
    }

    public function testNoRefFile()
    {
        $gitVersion = $this->getObject();

        $this->assertNull($gitVersion->getVersion(__DIR__.'/Fixtures/no-ref-file'));
    }

    public function testInvalidHeadFile()
    {
        $gitVersion = $this->getObject();

        $this->assertNull($gitVersion->getVersion(__DIR__.'/Fixtures/invalid-head-file'));
    }

    public function testInInvalidSubdir()
    {
        $gitVersion = $this->getObject();

        $this->assertNull($gitVersion->getVersion(__DIR__.'/Fixtures/correct/not-existing-subdir'));
    }

    public function testCorrectVersionWithCommitMessage()
    {
        $gitVersion = $this->getObject(true);

        $this->assertSame('Initial commit', $gitVersion->getLatestCommit(__DIR__.'/Fixtures/correct-with-commit'));
    }

    public function testCorrectVersionWithCommitMessageAndDescription()
    {
        $gitVersion = $this->getObject(true);

        $this->assertSame('Initial commit', $gitVersion->getLatestCommit(__DIR__.'/Fixtures/correct-with-commit-and-description'));
    }

    public function testCommitMessageWithNoCommitFile()
    {
        $gitVersion = $this->getObject(true);

        $this->assertNull($gitVersion->getLatestCommit(__DIR__.'/Fixtures/no-commit-file'));
    }

    public function testCommitMessageWithInvalidCommitFile()
    {
        $gitVersion = $this->getObject(true);

        $this->assertNull($gitVersion->getLatestCommit(__DIR__.'/Fixtures/invalid-commit-file'));
    }

    private function getObject($zlibAware = false)
    {
        if ($zlibAware && !\extension_loaded('zlib')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        return new GitVersion();
    }
}
