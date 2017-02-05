<?php
namespace Geekwright\RegDom;

/**
 * Based on code by:
 * Florian Sager, 06.08.2008, sager@agitos.de
 * Also, Marcus Bointon's https://github.com/Synchro/regdom-php
 *
 * Generate PHP array tree that contains all TLDs from the URL (see below);
 */

class PublicSuffixList
{
    protected $sourceURL = 'https://publicsuffix.org/list/public_suffix_list.dat';
    protected $localPSL = 'public_suffix_list.dat';

    protected $tree;
    protected $url;
    protected $dataDir;

    /**
     * PublicSuffixList constructor.
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        $this->setURL($url);
        $this->dataDir = realpath(__DIR__ . '/../data/') . '/';
    }

    /**
     * @param string $url
     * @return void
     */
    public function setURL($url)
    {
        $this->url = $url;
        $this->tree = null;
    }

    protected function setFallbackURL()
    {
        $this->setLocalPSLName($this->url);
        if (null === $this->url) {
            $this->url = file_exists($this->localPSL) ? $this->localPSL : $this->sourceURL;
        }
    }

    /**
     * make tree
     */
    protected function makeTree()
    {
        $this->setFallbackURL();

        $this->tree = $this->readCachedPSL($this->url);
        if (false !== $this->tree) {
            return;
        }

        $this->tree = array();
        $list = $this->readPSL();

        if (false===$list) {
            $e = new \RuntimeException('Cannot read ' . $this->url);
            throw $e;
        }

        $this->parsePSL($list);
        $this->cachePSL($this->url);
    }

    protected function parsePSL($list)
    {
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

    /**
     * @return bool|string PSL file contents or false on error
     */
    protected function readPSL()
    {
        $parts = parse_url($this->url);
        $remote = isset($parts['scheme']) || isset($parts['host']);
        // try to read with file_get_contents
        $newPSL = file_get_contents($this->url);
        if (false !== $newPSL) {
            if ($remote) {
                $this->saveLocalPSL($newPSL);
            }
            return $newPSL;
        }

        // try again with curl if file_get_contents failed
        if (function_exists('curl_init') && false !== ($curlHandle  = curl_init())) {
            curl_setopt($curlHandle, CURLOPT_URL, $this->url);
            curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
            $curlReturn = curl_exec($curlHandle);
            curl_close($curlHandle);
            if (false !== $curlReturn) {
                if ($remote) {
                    $this->saveLocalPSL($curlReturn);
                }
                return $curlReturn;
            }
        }
        return false;
    }

    /**
     * Determine cache file name for a specified source
     *
     * @param string $url URL/filename of source PSL
     *
     * @return string cache file name for given resource
     */
    protected function getCacheFileName($url)
    {
        return $this->dataDir . 'cached_' . md5($url);
    }

    /**
     * Attempt to load a cached Public Suffix List tree for a given source
     *
     * @param string $url URL/filename of source PSL
     *
     * @return bool|string[] PSL tree
     */
    protected function readCachedPSL($url)
    {
        $cacheFile = $this->getCacheFileName($url);
        if (file_exists($cacheFile)) {
            $cachedTree = file_get_contents($cacheFile);
            return unserialize($cachedTree);
        }
        return false;
    }

    /**
     * Cache the current Public Suffix List tree and associate with the specified source
     *
     * @param string $url URL/filename of source PSL
     *
     * @return bool|int the number of bytes that were written to the file, or false on failure
     */
    protected function cachePSL($url)
    {
        return file_put_contents($this->getCacheFileName($url), serialize($this->tree));
    }

    /**
     * Save a local copy of a retrieved Public Suffix List
     *
     * @param string $fileContents URL/filename of source PSL
     *
     * @return bool|int the number of bytes that were written to the file, or false on failure
     */
    protected function saveLocalPSL($fileContents)
    {
        return file_put_contents($this->localPSL, $fileContents);
    }

    protected function setLocalPSLName($url)
    {
        if (null === $url) {
            $url = $this->sourceURL;
        }
        $parts = parse_url($url);
        $fileName = basename($parts['path']);
        $this->localPSL = $this->dataDir . $fileName;
    }

    /**
     * Delete files in the data directory
     *
     * @param string $prefix limit clearing to file names starting with this prefix, null for all files
     *
     * @return void
     */
    public function clearDataDirectory($prefix = null)
    {
        $dir = $this->dataDir;
        if (is_dir($dir)) {
            if ($dirHandle = opendir($dir)) {
                while (($file = readdir($dirHandle)) !== false) {
                    if (filetype($dir . $file) === 'file' && (null===$prefix || $this->startsWith($file, $prefix))) {
                        unlink($dir . $file);
                    }
                }
                closedir($dirHandle);
            }
        }
    }
}
