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
        if (!array_key_exists('ref', $matches)) {
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
            if (file_exists($directory.DIRECTORY_SEPARATOR.$gitDir) && is_dir($directory.DIRECTORY_SEPARATOR.$gitDir)) {
                break;
            }

            if ($directory === dirname($directory)) {
                return null;
            }

            $directory = dirname($directory);
        }

        return $directory.DIRECTORY_SEPARATOR.$gitDir;
    }
}
