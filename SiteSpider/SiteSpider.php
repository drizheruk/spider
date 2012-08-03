<?php

/**
 * @package SiteSpider
 * @author Vladimir Drizheruk <vladimir.drizheruk@gmail.com> 
 */
class SiteSpider {

    /**
     * MultiCurl
     * @var $mcurl object 
     */
    private $mcurl;

    /**
     * Urls for scrapping
     * @var $urls array 
     */
    private $urls = array();

    /**
     * Links after scrapping
     * @var $links array 
     */
    private $links = array();

    /**
     * constructor
     * init base variables 
     */
    function __construct() {
        $this->mcurl = new MultiCurl;
        $this->mcurl->timeout = 30;
    }

    /**
     * set urls for crawling
     * @param array $urls 
     */
    public function setUrls($urls) {
        $this->urls = $urls;
    }

    /**
     * getAllLinks
     * @return mixed array 
     */
    public function getAllLinks() {
        if (!empty($this->urls)) {
            $content = $this->mcurl->multiget($this->urls);
            foreach ($this->urls as $k => $url) {
                $this->links[$k]['links'] = array();
                $this->links[$k]['base_link'] = $url;
                $this->links[$k]['http_code'] = $content[$k]['http_code'];
                $this->links[$k]['page_size'] = $content[$k]['page_size'];
                $this->links[$k]['content_type'] = $content[$k]['content_type'];
                $this->links[$k]['redirect_url'] = $content[$k]['redirect_url'];
                $this->links[$k]['title'] = '';
                $this->links[$k]['meta_description'] = '';
                $this->links[$k]['meta_keywords'] = '';
                if (isset($content[$k]['content'])) {
                    $html = $content[$k]['content'];
                    $dom = new DOMDocument();
                    @$dom->loadHTML($html);
                    $xpath = new DOMXPath($dom);
                    if (is_object($xpath)) {
                        $titleNode = $xpath->query('//title')->item(0);
                        if(isset($titleNode)){
                            $this->links[$k]['title'] = mb_convert_encoding($titleNode->textContent, 'utf-8');
                        }
                        $nodes = $xpath->query('/html/head/meta');
                        if (isset($nodes)) {
                            foreach ($nodes as $node) {
                                switch ($node->getAttribute('name')) {
                                    case 'description':
                                        $this->links[$k]['meta_description'] = mb_convert_encoding($node->getAttribute('content'), 'utf-8');
                                        break;
                                    case 'keywords':
                                        $this->links[$k]['meta_keywords'] = mb_convert_encoding($node->getAttribute('content'), 'utf-8');
                                        break;
                                }
                            }
                        }

                        $href = $xpath->evaluate("/html/body//a");
                        for ($i = 0; $i < $href->length; $i++) {
                            $data = $href->item($i);
                            $url = $data->getAttribute('href');
                            if (!empty($url)) {
                                if (!in_array($url, $this->links[$k]['links'])) {
                                    array_push($this->links[$k]['links'], $url);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->links;
    }

    /**
     * getOwnerLinks
     * @return mixed array - links from the same domain 
     */
    public function getOwnerLinks() {
        $links = $this->getAllLinks();

        if (!empty($links)) {

            foreach ($links as $k => $item) {
                $preparedLinks = array();
                if (!empty($item['links'])) {
                    $parse_url = parse_url($links[$k]['base_link']);
                    foreach ($item['links'] as $z => $link) {
                        if (isset($parse_url['host'])) {
                            $link_parse_url = parse_url($link);
                            if (isset($link_parse_url['host'])) {
                                if ($link_parse_url['host'] == $parse_url['host']) {
                                    array_push($preparedLinks, $link);
                                }
                            } elseif ('/' == substr($link, 0, 1)) {


                                $link = $parse_url['scheme'] . '://' . $parse_url['host'] . $link;
                                array_push($preparedLinks, $link);
                            }
                        }
                    }
                    $this->links[$k]['links'] = $preparedLinks;
                }
            }
            return $this->links;
        }
    }

}