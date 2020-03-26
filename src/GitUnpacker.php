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
 * @internal
 *
 * @author Antal Áron <antalaron@antalaron.hu>
 *
 * @see https://codewords.recurse.com/issues/three/unpacking-git-packfiles
 */
class GitUnpacker
{
    private $ref;
    private $gitDirectory;

    public function __construct($ref, $gitDirectory)
    {
        $this->ref = $ref;
        $this->gitDirectory = $gitDirectory;
    }

    public function getCommitMessage()
    {
        foreach ($this->getIndexes() as $indexFile) {
            if (!file_exists($indexFile->getPathname()) || !is_readable($indexFile->getPathname())) {
                continue;
            }

            $fileResource = fopen($indexFile->getPathname(), 'rb');

            if ('00' === substr($this->ref, 0, 2)) {
                $unpacked = 0;
            } else {
                fseek($fileResource, 0x08 + (hexdec(substr($this->ref, 0, 2)) - 1) * 0x04);
                $binary = fread($fileResource, 0x04);

                $unpacked = unpack('N', $binary);
                $unpacked = array_values($unpacked);
                $unpacked = $unpacked[0];
            }

            $position = $unpacked;

            fseek($fileResource, 0x08 + hexdec(substr($this->ref, 0, 2)) * 0x04);
            $binary = fread($fileResource, 4);

            $unpacked = unpack('N', $binary);
            $unpacked = array_values($unpacked);
            $count = $unpacked[0] - $position;

            fseek($fileResource, 0x08 + 0xff * 0x04);
            $binary = fread($fileResource, 4);

            $unpacked = unpack('C4', $binary);
            $unpacked = array_values($unpacked);

            $unpacked = unpack('N', $binary);
            $unpacked = array_values($unpacked);

            $totalCount = $unpacked[0];

            $found = false;
            fseek($fileResource, 0x08 + 0x100 * 0x04 + $position * 20);
            for ($i = 0; $i < $count; ++$i) {
                $binary = fread($fileResource, 20);

                $unpacked = unpack('C20', $binary);
                $unpacked = array_values($unpacked);

                $hash = implode('', array_map(function ($byte) {
                    return sprintf('%02x', $byte);
                }, $unpacked));

                if ($hash === $this->ref) {
                    $found = $i;

                    break;
                }
            }

            $absolutePosition = $position + $found;

            if (false === $found) {
                fclose($fileResource);

                continue;
            }

            fseek($fileResource, 0x08 + 0x100 * 0x04 + $totalCount * 20 + $totalCount * 0x04 + $absolutePosition * 0x04);
            $binary = fread($fileResource, 4);

            $unpacked = unpack('N', $binary);
            $unpacked = array_values($unpacked);

            $packPosition = $unpacked[0];
            // Pack files >2GB
            if (0x80000000 === 0x80000000 & $packPosition) {
                $packOffset = 0x7fffffff & $packPosition;

                fseek($fileResource, 0x08 + 0x100 * 0x04 + $totalCount * 20 + $totalCount * 0x04 + $totalCount * 0x04 + $packOffset * 0x08);
                $binary = fread($fileResource, 8);

                $unpacked = unpack('J', $binary);
                $unpacked = array_values($unpacked);

                $packPosition = $unpacked[0];
            }

            $result = $this->readPack(str_replace('.idx', '.pack', $indexFile->getPathname()), $packPosition);

            fclose($fileResource);

            return $result;
        }

        return null;
    }

    private function readPack($file, $offset)
    {
        if (!file_exists($file) || !is_readable($file)) {
            return null;
        }

        $fileResource = fopen($file, 'rb');
        fseek($fileResource, $offset);
        $i = 0;
        $value = 0;
        do {
            $binary = fread($fileResource, 1);
            $unpacked = unpack('C1', $binary);
            $unpacked = array_values($unpacked);
            $value = $value | (0x7f & $unpacked[0]) << max($i * 0x07 - 0x03, 0);
            ++$i;
        } while ($unpacked[0] > 0x7f);

        // assume compressed size is smaller then decompressed
        $size = min(filesize($file) - $offset - $i, $value);
        $binary = fread($fileResource, $size);
        $unpacked = unpack('C'.$size, $binary);
        $unpacked = array_values($unpacked);
        $encoded = array_map('chr', $unpacked);
        $encoded = implode('', $encoded);

        return @zlib_decode($encoded);
    }

    private function getIndexes()
    {
        if (!file_exists($this->gitDirectory.'/objects/pack/') || !is_dir($this->gitDirectory.'/objects/pack/')) {
            return;
        }

        $directory = new \DirectoryIterator($this->gitDirectory.'/objects/pack/');

        foreach ($directory as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }

            if ('idx' === $fileinfo->getExtension()) {
                yield $fileinfo;
            }
        }
    }
}
