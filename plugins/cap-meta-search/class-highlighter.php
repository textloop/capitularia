<?php
/**
 * Capitularia Meta Search Highlighter
 *
 * @package Capitularia
 */

namespace cceh\capitularia\meta_search;

/**
 * TEI metadata extraction and search.
 */

class Highlighter
{
    /**
     * Get start and end of snippet
     *
     * Given a match returns the start offset (strpos) and end offset of the
     * snippet in the post content.
     *
     * @param string $content     The post content.
     * @param int    $content_len The post content length.
     * @param object $match       The preg_match match structure.
     *
     * @return array Begin and end of the snippet.
     */

    private function get_bounds ($content, $content_len, $match)
    {
        // offsets in $match are byte offsets even if the regex uses /u !!!
        // convert byte offset into char offset
        $char_offset = mb_strlen (mb_strcut ($content, 0, $match[0][1]));

        $start = max ($char_offset - 100, 0);
        $end   = min ($char_offset + 100, $content_len);

        if ($start && ($space = mb_strpos ($content, ' ', $start))) {
            $start = $space + 1;
        }
        if ($end   && ($space = mb_strpos ($content, ' ', $end))) {
            $end = $space;
        }

        return array ('begin' => $start, 'end' => $end);
    }

    /**
     * Extract snippets
     *
     * Given a regex, search a post and return a list of snippets.  The snippets
     * contain the highlighted search term and are returned formatted as HTML
     * unordred list.
     *
     * @param string  $content      The post content to search
     * @param string  $regex        The regex to search for
     * @param integre $max_snippets The max. no. of snippets to return
     *
     * @return string HTML list of snippets
     */

    private function get_snippets ($content, $regex, $max_snippets = 3)
    {
        $regex = "#$regex#ui";
        $matches = array ();
        preg_match_all ($regex, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $content_len = mb_strlen ($content);
        $snippets = array (); // array of array ('begin' => pos, 'end' => pos)
        $n_snippets = 0;

        foreach ($matches as $match) {
            $snippet = $this->get_bounds ($content, $content_len, $match);
            if (($n_snippets > 0) && (($snippet['begin'] - $snippets[$n_snippets - 1]['end']) < 5)) {
                // extend previous snippet
                $snippets[$n_snippets - 1]['end'] = $snippet['end'];
            } else {
                // add a new snippet
                $snippets[] = $snippet;
                $n_snippets++;
            }
            if ($n_snippets >= $max_snippets) {
                break;
            }
        }

        $text = "<ul>\n";

        foreach ($snippets as $snippet) {
            $start = $snippet['begin'];
            $len   = $snippet['end'] - $start;
            $text .= "<li class='snippet'>\n";
            $text .= preg_replace ($regex, '<mark>${0}</mark>', mb_substr ($content, $start, $len));
            $text .= "</li>\n";
        }

        $text .= "</ul>\n";
        return $text;
    }

    /**
     * Escape search term for regexp.
     *
     * Escapes a search term so that preg_* functions can safely use it. '#' is
     * the preg separator, eg. #term#u .
     *
     * @param string $term The user input.
     *
     * @return string The escaped user input.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */

    private function escape_search_term ($term)
    {
        return preg_quote ($term, '#');
    }

    /**
     * Return search results snippets.
     *
     * Build an excerpt using snippets with highlighted search results.
     *
     * @param string $content The unused old excerpt content.
     *
     * @return string A series of Highlighted snippets.
     */

    public function on_get_the_excerpt ($content)
    {
        global $wp_query;
        if (!is_admin () && $wp_query->is_main_query () && $wp_query->is_search ()) {
            if ($terms = $wp_query->get ('search_terms')) {
                $content = wp_strip_all_tags (
                    apply_filters ('the_content', get_the_content ()),
                    true
                );
                $terms = array_map (array ($this, 'escape_search_term'), $terms);
                $regex = implode ('|', $terms);
                return $this->get_snippets ($content, $regex);
            }
            return wp_strip_all_tags ($content);
        }
        return $content;
    }

    /**
     * Highlight search terms in full post if referenced from search page.
     *
     * @param string $content The post content.
     *
     * @return string The highlighted post content.
     */

    public function on_the_content ($content)
    {
        if (!is_admin () && isset ($_SERVER['HTTP_REFERER']) && is_singular () && in_the_loop ()) {
            $referrer = $_SERVER['HTTP_REFERER'];
            $args = explode ('?', $referrer);
            if (count ($args) > 1) {
                $args = wp_parse_args ($args[1], array ());
                // $local_search = stripos ($referrer, $_SERVER['SERVER_NAME']) !== false;
                if (!empty ($args['s'])) {
                    $terms = array_map (array ($this, 'escape_search_term'), explode (' ', $args['s']));
                    $regex = implode ('|', $terms);
                    $regex = "#($regex)#ui";

                    // The naive approach:
                    //
                    //   return preg_replace ($regex, '<mark>${0}</mark>', $content);
                    //
                    // did not work because we were also replacing text in HTML
                    // tags and attributes.  This whole rigmarole is needed so
                    // we can parse the content as HTML and then search the text
                    // nodes only.

                    $doc = new \DomDocument ();

                    // keep server error log small (seems to be a problem at uni-koeln.de)
                    libxml_use_internal_errors (true);

                    $doc->loadHTML (
                        "<?xml encoding='UTF-8'>\n<div id='dropme'>\n" .
                        $content . "</div>\n",
                        LIBXML_NONET
                    );
                    foreach ($doc->childNodes as $item) {
                        if ($item->nodeType == XML_PI_NODE) {
                            $doc->removeChild ($item); // remove xml declaration
                        }
                    }
                    $doc->encoding = 'UTF-8'; // insert proper encoding

                    $xpath  = new \DOMXpath ($doc);
                    $text_nodes = $xpath->query ('//text()');
                    foreach ($text_nodes as $text_node) {
                        $splits = preg_split ($regex, $text_node->textContent, -1, PREG_SPLIT_DELIM_CAPTURE);
                        if (count ($splits) > 1) {
                            $parent = $text_node->parentNode;
                            $i = 0;
                            foreach ($splits as $split) {
                                if (($i % 2) == 0) {
                                    $new_node = $doc->createTextNode ($split);
                                } else {
                                    $new_node = $doc->createElement ('mark', $split);
                                }
                                $parent->insertBefore ($new_node, $text_node);
                                $i++;
                            }
                            $parent->removeChild  ($text_node);
                        }
                    }
                    $div = $xpath->query ("//div[@id='dropme']");
                    $content = $doc->saveHTML ($div[0]);
                }
            }
        }
        return $content;
    }
}