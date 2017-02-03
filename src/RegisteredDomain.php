<?php
namespace Geekwright\RegDom;

class RegisteredDomain
{
    protected $tree;
    protected $psl;

    public function __construct()
    {
        $this->psl = new PublicSuffixList(__DIR__ .'/../data/public_suffix_list.dat');
    }

    /**
     * Given a URL or bare host name, return a normalized host name, converting punycode to UTF-8
     * and converting to lower case
     *
     * @param string $url URL or host name
     * @return string
     */
    protected function normalizeHost($url)
    {
        $host = (false!==strpos($url, '/')) ? parse_url($url, PHP_URL_HOST) : $url;
        $parts = explode('.', $host);
        $utf8Host = '';
        foreach ($parts as $part) {
            $utf8Host = $utf8Host . (($utf8Host === '') ? '' : '.') . idn_to_utf8($part);
        }

        return mb_strtolower($utf8Host);
    }

    public function getRegisteredDomain($host)
    {
        $this->tree = $this->psl->getTree();

        $signingDomain = $this->normalizeHost($host);
        $signingDomainParts = explode('.', $signingDomain);

        $result = $this->findRegisteredDomain($signingDomainParts, $this->tree);

        if (empty($result)) {
            // this is an invalid domain name
            return null;
        }

        // assure there is at least 1 TLD in the stripped signing domain
        if (!strpos($result, '.')) {
            $cnt = count($signingDomainParts);
            if ($cnt==1 || $signingDomainParts[$cnt-2]=="") {
                return null;
            }
            return $signingDomainParts[$cnt-2].'.'.$signingDomainParts[$cnt-1];
        }
        return $result;
    }

// recursive helper method
    protected function findRegisteredDomain($remainingSigningDomainParts, &$treeNode)
    {
        $sub = array_pop($remainingSigningDomainParts);

        $result = null;
        if (isset($treeNode['!'])) {
            return '';
        } elseif (is_array($treeNode) && array_key_exists($sub, $treeNode)) {
            $result = $this->findRegisteredDomain($remainingSigningDomainParts, $treeNode[$sub]);
        } elseif (is_array($treeNode) && array_key_exists('*', $treeNode)) {
            $result = $this->findRegisteredDomain($remainingSigningDomainParts, $treeNode['*']);
        } else {
            return $sub;
        }

        if ($result === '') {
            return $sub;
        } elseif (strlen($result)>0) {
            return $result.'.'.$sub;
        }
        return null;
    }
}
