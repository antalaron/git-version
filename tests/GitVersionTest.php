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
use Antalaron\GitVersion\Model\Commit;

/**
 * GitVersionTest.
 *
 * @author Antal Áron <antalaron@antalaron.hu>
 */
class GitVersionTest extends TestCase
{
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

        $commit = $this->createCommit([
            'author' => 'Antal Áron <antalaron@antalaron.hu>',
            'authorDate' => (new \DateTime())->setTimestamp(1516971227),
            'committer' => 'Antal Áron <antalaron@antalaron.hu>',
            'committerDate' => (new \DateTime())->setTimestamp(1516971227),
            'message' => 'Initial commit',
        ]);

        $this->assertTrue($gitVersion->getLatestCommitDetails(__DIR__.'/Fixtures/correct-with-commit')->isEqual($commit));
        $this->assertSame('Initial commit', $gitVersion->getLatestCommit(__DIR__.'/Fixtures/correct-with-commit'));
    }

    public function testCorrectVersionWithCommitMessageAndDescription()
    {
        $gitVersion = $this->getObject(true);

        $commit = $this->createCommit([
            'author' => 'Antal Áron <antalaron@antalaron.hu>',
            'authorDate' => (new \DateTime())->setTimestamp(1516971695),
            'committer' => 'Antal Áron <antalaron@antalaron.hu>',
            'committerDate' => (new \DateTime())->setTimestamp(1516971695),
            'message' => 'Initial commit',
            'description' => "Description content\n",
        ]);

        $this->assertTrue($gitVersion->getLatestCommitDetails(__DIR__.'/Fixtures/correct-with-commit-and-description')->isEqual($commit));
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

    public function testCorrectWithNumberVersion()
    {
        $gitVersion = $this->getObject();

        $this->assertSame('b302440fd9972a341f2ecbe50d347b9fbd5b9f0b', $gitVersion->getVersion(__DIR__.'/Fixtures/correct-with-number'));
    }

    public function testCorrectPacked()
    {
        $gitVersion = $this->getObject(true);

        $commit = $this->createCommit([
            'author' => 'Antal Áron <antalaron@antalaron.hu>',
            'authorDate' => (new \DateTime())->setTimestamp(1585229077),
            'committer' => 'Antal Áron <antalaron@antalaron.hu>',
            'committerDate' => (new \DateTime())->setTimestamp(1585229077),
            'message' => 'Second commit',
        ]);

        $this->assertSame('f2213cbda1486db7befc77d8422d066f585958d4', $gitVersion->getVersion(__DIR__.'/Fixtures/correct-packed'));
        $this->assertSame('Second commit', $gitVersion->getLatestCommit(__DIR__.'/Fixtures/correct-packed'));
        $this->assertNull($gitVersion->getLatestCommit(__DIR__.'/Fixtures/correct-packed', false));
    }

    public function testWithSignedCommit()
    {
        $gitVersion = $this->getObject(true);

        $commit = $this->createCommit([
            'author' => 'Antal Áron <antalaron@antalaron.hu>',
            'authorDate' => (new \DateTime())->setTimestamp(1638041187),
            'committer' => 'Antal Áron <antalaron@antalaron.hu>',
            'committerDate' => (new \DateTime())->setTimestamp(1638041187),
            'message' => 'Initial commit',
        ]);

        $this->assertTrue($gitVersion->getLatestCommitDetails(__DIR__.'/Fixtures/signed-commit')->isEqual($commit));
        $this->assertSame('Initial commit', $gitVersion->getLatestCommit(__DIR__.'/Fixtures/signed-commit'));
    }

    private function getObject($zlibAware = false)
    {
        if ($zlibAware && !\extension_loaded('zlib')) {
            $this->markTestSkipped('The zlib extension is not available.');
        }

        return new GitVersion();
    }

    private function createCommit(array $data = null)
    {
        $commit = new Commit('');

        $reflection = new \ReflectionClass($commit);
        foreach ($data as $property => $value) {
            $property = $reflection->getProperty($property);
            $property->setAccessible(true);
            $property->setValue($commit, $value);
        }

        return $commit;
    }
}
