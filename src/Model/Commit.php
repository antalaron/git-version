<?php

/*
 * This file is part of GitVersion.
 *
 * (c) Antal Áron <antalaron@antalaron.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antalaron\GitVersion\Model;

/**
 * @author Antal Áron <antalaron@antalaron.hu>
 */
class Commit
{
    private $author;
    private $authorDate;
    private $committer;
    private $committerDate;
    private $message;
    private $description;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        $commitDetails = explode("\n\n", $data, 2);
        if (!\is_array($commitDetails) || 2 > \count($commitDetails)) {
            return;
        }

        foreach (explode("\n", $commitDetails[0]) as $commitDetailLine) {
            $commitDetail = explode(' ', $commitDetailLine);

            if ('author' === $commitDetail[0]) {
                preg_match('/^author (?<author>(.*)?<.*>) (?<timestamp>.*)/', $commitDetailLine, $matches);
                $this->author = $matches['author'];
                $this->authorDate = (new \DateTime())->setTimestamp((int) $matches['timestamp']);
            } elseif ('committer' === $commitDetail[0]) {
                preg_match('/^committer (?<committer>(.*)?<.*>) (?<timestamp>.*)/', $commitDetailLine, $matches);
                $this->committer = $matches['committer'];
                $this->committerDate = (new \DateTime())->setTimestamp((int) $matches['timestamp']);
            }
        }

        $messageParts = explode("\n\n", $commitDetails[1], 2);
        if (!\is_array($messageParts) || 1 > \count($messageParts)) {
            return;
        }

        $this->message = rtrim($messageParts[0], "\n");
        if (\array_key_exists(1, $messageParts)) {
            $this->description = $messageParts[1];
        }
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAuthorDate()
    {
        return $this->authorDate;
    }

    /**
     * @return string
     */
    public function getCommitter()
    {
        return $this->committer;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCommitterDate()
    {
        return $this->committerDate;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isEqual(self $commit)
    {
        return $this->author === $commit->getAuthor()
            && $this->dateEquals($this->authorDate, $commit->getAuthorDate())
            && $this->committer === $commit->getCommitter()
            && $this->dateEquals($this->committerDate, $commit->getCommitterDate())
            && $this->message === $commit->getMessage()
            && $this->description === $commit->getDescription()
        ;
    }

    private function dateEquals(\DateTimeInterface $date, \DateTimeInterface $dateToCompare)
    {
        $interval = $date->diff($dateToCompare);

        return '00-0-0 00:0:0' === $interval->format('%Y-%m-%d %H:%i:%s');
    }
}
