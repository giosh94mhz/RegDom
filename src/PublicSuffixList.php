<?php
namespace Geekwright\RegDom;

/**
 * Based on code by:
 * Florian Sager, 06.08.2008, sager@agitos.de
 * Also, Marcus Bointon's https://github.com/Synchro/regdom-php
 *
 * Generate PHP array tree that contains all TLDs from the URL (see below);
 */

// DEFINE('URL', 'https://publicsuffix.org/list/public_suffix_list.dat');
// define('URL', __DIR__ .'../data/public_suffix_list.dat');


class PublicSuffixList
{
    const URL = 'https://publicsuffix.org/list/public_suffix_list.dat';

    protected $tree;
    protected $url;

    public function __construct($url = self::URL)
    {
        $this->setURL($url);
    }

    public function setURL($url)
    {
        $this->url = $url;
        $this->tree = null;
    }

    protected function makeTree()
    {
        $this->tree = array();
        $list = file_get_contents($this->url);

        if (false===$list) {
            $e = new \RuntimeException('Cannot read ' . $this->url);
            throw $e;
        }

        $lines = explode("\n", $list);

        foreach ($lines as $line) {
            if ($this->startsWith($line, "//") || $line == '') {
                continue;
            }

            // this line should be a TLD
            $tldParts = explode('.', $line);

            $this->buildSubDomain($this->tree, $tldParts);
        }
    }

    /*
     * Does $search start with $startString?
     */
    protected function startsWith($search, $startString)
    {
        return (substr($search, 0, strlen($startString)) == $startString);
    }

    protected function buildSubDomain(&$node, $tldParts)
    {
        $dom = trim(array_pop($tldParts));

        $isNotDomain = false;
        if ($this->startsWith($dom, "!")) {
            $dom = substr($dom, 1);
            $isNotDomain = true;
        }

        if (!array_key_exists($dom, $node)) {
            if ($isNotDomain) {
                $node[$dom] = array("!" => "");
            } else {
                $node[$dom] = array();
            }
        }

        if (!$isNotDomain && count($tldParts) > 0) {
            $this->buildSubDomain($node[$dom], $tldParts);
        }
    }

    public function getTree()
    {
        if (null===$this->tree) {
            $this->makeTree();
        }
        return $this->tree;
    }
}
