<?php

/*
 * This file is part of GitVersion.
 *
 * (c) Antal Áron <antalaron@antalaron.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antalaron\GitVersion;

/**
 * GitVersion.
 *
 * @author Antal Áron <antalaron@antalaron.hu>
 */
class GitVersion
{
    /**
     * Get Git version.
     *
     * @param string   $startDirectory Directory where to start finding git repo
     * @param int|null $hashLength     Lenght of the hash to return
     *
     * @return string|null The hash, or null if error
     */
    public static function getGitVersion($startDirectory, $hashLength = null)
    {
        $gitVersion = new self();

        return $gitVersion->getVersion($startDirectory, $hashLength);
    }

    /**
     * Get version.
     *
     * @param string   $startDirectory Directory where to start finding git repo
     * @param int|null $hashLength     Lenght of the hash to return
     *
     * @return string|null The hash, or null if error
     */
    public function getVersion($startDirectory, $hashLength = null)
    {
        $gitDirectory = $this->getGitDirectory(realpath($startDirectory));

        if (null === $gitDirectory) {
            return null;
        }

        if (!file_exists($headFile = $gitDirectory.'/HEAD') || !is_readable($headFile)) {
            return null;
        }

        preg_match('/ref: (?P<ref>[a-zA-Z_\-\/]+)$/', file_get_contents($headFile), $matches);
        if (!\array_key_exists('ref', $matches)) {
            return null;
        }

        if (!file_exists($refFile = $gitDirectory.'/'.$matches['ref']) || !is_readable($refFile)) {
            return null;
        }

        list($hash) = explode("\n", file_get_contents($refFile));

        if (null !== $hashLength) {
            $hash = substr($hash, 0, $hashLength);
        }

        return $hash;
    }

    /**
     * Get Git latest commit message.
     *
     * @param string $startDirectory Directory where to start finding git repo
     * @param bool   $usePack        Weather use the pack files or not
     *
     * @return string|null The message, or null if error
     *
     * @throws \RuntimeException If ext-zlib is not enabled
     */
    public static function getGitLatestCommit($startDirectory, $usePack = true)
    {
        $gitVersion = new self();

        return $gitVersion->getLatestCommit($startDirectory, $usePack);
    }

    /**
     * Get latest commit message.
     *
     * @param string $startDirectory Directory where to start finding git repo
     * @param bool   $usePack        Weather use the pack files or not
     *
     * @return string|null The message, or null if error
     *
     * @throws \RuntimeException If ext-zlib is not enabled
     */
    public function getLatestCommit($startDirectory, $usePack = true)
    {
        if (!\function_exists('zlib_decode')) {
            throw new \RuntimeException(sprintf('You should enable ext-zlib extension to use %s()', __METHOD__));
        }

        $gitDirectory = $this->getGitDirectory(realpath($startDirectory));
        $ref = $this->getVersion($startDirectory);

        $data = null;
        if (!file_exists($commitFile = $gitDirectory.'/objects/'.substr($ref, 0, 2).'/'.substr($ref, 2)) || !is_readable($commitFile)) {
            if (!$usePack) {
                return null;
            }
            $unpacker = new GitUnpacker($ref, $gitDirectory);

            $data = $unpacker->getCommitMessage();
            if (null === $data) {
                return null;
            }
        }

        if (null === $data) {
            $data = @zlib_decode(file_get_contents($commitFile));
        }

        $commitMessage = explode("\n\n", $data, 2);
        if (!\is_array($commitMessage) || !\array_key_exists(1, $commitMessage) || !\is_string($commitMessage[1])) {
            return null;
        }

        $commitMessage = explode("\n", $commitMessage[1], 2);

        if (!\is_array($commitMessage) || !\array_key_exists(0, $commitMessage) || !\is_string($commitMessage[0])) {
            return null;
        }

        return $commitMessage[0];
    }

    /**
     * Get git direcoty.
     *
     * @param string $startDirectory
     *
     * @return string|null
     */
    protected function getGitDirectory($startDirectory)
    {
        $directory = $startDirectory;

        // For tests
        $gitDir = false !== getenv('GIT_DOT_DIR') ? getenv('GIT_DOT_DIR') : '.git';

        while (true) {
            if (file_exists($directory.\DIRECTORY_SEPARATOR.$gitDir) && is_dir($directory.\DIRECTORY_SEPARATOR.$gitDir)) {
                break;
            }

            if ($directory === \dirname($directory)) {
                return null;
            }

            $directory = \dirname($directory);
        }

        return $directory.\DIRECTORY_SEPARATOR.$gitDir;
    }
}
