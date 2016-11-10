<?php

namespace BootPress\Pagination;

use BootPress\Page\Component as Page;

class Component
{
    private $page;
    private $get;
    private $url;
    private $offset;
    private $limit;
    private $total;
    private $current;
    private $pager = array();
    private $links = array();

    /**
     * @param string $framework You can indicate your preference now, or later in ``$this->html()``.
     *
     * ```php
     * use BootPress\Pagination\Component as Paginator;
     *
     * $pagination = new Paginator;
     * ```
     */
    public function __construct($framework = 'bootstrap')
    {
        $this->page = Page::html();
        $this->get = false;
        $this->set();
        $this->html($framework);
    }

    /**
     * Access the following information:
     *
     * - '**pager**' - (array) The current HTML pager styles.
     * - '**links**' - (array) The current HTML pagination styles.
     * - '**offset**' - (int) How much to offset the records, starting at 0.
     * - '**length**' - (int) The total number of records to display.
     *   - '**offset**' and '**length**' are meant to be compatible with ``array_slice()``.
     * - '**limit**' - (string) A string to add onto the end of your query eg. ``" LIMIT {$offset}, {$length}"``.
     *   - If no limit is needed, then this will be an empty string.
     * - '**last_page**' - (bool) Whether or not this is the last page being looked at.
     * - '**current_page**' - (int) The current page number being viewed, starting at 1.
     * - '**number_pages**' - (int) The total number of pages, starting at 1.
     * - '**previous_url**' - (string|null) The previous page url, if applicable.
     * - '**next_url**' - (string|null) The next page url, if applicable.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @example
     *
     * ```php
     * $records = range(1, 100);
     * if (!$pagination->set()) {
     *     $pagination->total(count($records));
     * }
     * $display = array_slice($records, $pagination->offset, $pagination->length);
     * echo implode(',', $display); // 1,2,3,4,5,6,7,8,9,10
     * ```
     */
    public function __get($name)
    {
        switch ($name) {
            case 'pager':
            case 'links':
                return $this->$name;
                break;
            case 'offset':
                return ($this->get) ? $this->offset : 0;
                break;
            case 'length':
                return ($this->get) ? $this->limit : null;
                break;
            case 'limit':
                return ($this->get) ? ' LIMIT '.$this->offset.', '.$this->limit : '';
                break;
            case 'last_page':
                return ($this->get && $this->current == $this->total) ? true : false;
                break;
            case 'current_page':
                return ($this->get) ? $this->current : 1;
                break;
            case 'number_pages':
                return ($this->get) ? $this->total : 1;
                break;
            case 'previous_url':
                return ($this->get && $this->current > 1) ? $this->page($this->current - 1) : null;
                break;
            case 'next_url':
                return ($this->get && $this->current < $this->total) ? $this->page($this->current + 1) : null;
                break;
        }

        return;
    }

    /**
     * This is here for the sake of Twig templates, and also because ``empty($this->limit)`` was returning true while ``$this->limit`` would return " LIMIT 0, 10" when accessed directly.
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'pager':
            case 'links':
            case 'offset':
            case 'length':
            case 'limit':
            case 'last_page':
            case 'current_page':
            case 'number_pages':
            case 'previous_url':
            case 'next_url':
                return true;
                break;
        }

        return false;
    }

    /**
     * Check if we need a total count.  There's no sense in querying the database if you don't have to.  Plus, you have to call this anyways to set things up.
     *
     * @param string $page  The url query parameter that pertains these records.
     * @param int    $limit How many records to return at a time.
     * @param string $url   The url to use.  The default is the current url.
     *
     * @return bool
     *
     * @example
     *
     * ```php
     * if (!$pagination->set()) {
     *     $pagination->total(100);
     * }
     * ```
     */
    public function set($page = 'page', $limit = 10, $url = null)
    {
        if (is_null($url)) {
            $url = $this->page->url();
        }
        $params = $this->page->url('params', $url);
        $this->get = $page;
        $this->url = $url;
        $this->offset = 0;
        $this->limit = $limit;
        $this->total = 1;
        $this->current = 1;
        if (isset($params[$page])) {
            $page = array_map('intval', explode('of', $params[$page]));
            if (($current = array_shift($page)) && $current > 1) { // not the first page
                $this->current = $current;
                $this->offset = ($current - 1) * $this->limit;
                if (($total = array_shift($page)) && $current < $total) { // and not the last page
                    $this->total = $total;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Let us know how many records we're working with.  Check if ``$this->set()`` already before telling us to save yourself a query.
     *
     * @param int $count
     */
    public function total($count)
    {
        if ($this->get) {
            $this->total = ($count > $this->limit) ? ceil($count / $this->limit) : 1;
        }
    }

    /**
     * Customize the pagination and pager links. 
     *
     * @param string $type    Either the CSS framework you want to use ('[**bootstrap**](http://getbootstrap.com/components/#pagination)', '[**zurb_foundation**](http://foundation.zurb.com/docs/components/pagination.html)', '[**semantic_ui**](http://semantic-ui.com/collections/menu.html#pagination)', '[**materialize**](http://materializecss.com/pagination.html)', '[**uikit**](http://getuikit.com/docs/pagination.html)'), or what you want to override (the pagination '**links**', or '**pager**' html).
     * @param array  $options The values you want to override.  The default (bootstrap) values are:
     *
     * - If ``$type == 'links'``
     *   - '**wrapper**' => ``'<ul class="pagination">{{ value }}</ul>'``,
     *   - '**link**' => ``'<li><a href="{{ url }}">{{ value }}</a></li>'``,
     *   - '**active**' => ``'<li class="active"><span>{{ value }}</span></li>'``,
     *   - '**disabled**' => ``'<li class="disabled"><span>{{ value }}</span></li>'``,
     *   - '**previous**' => ``'&laquo;'``, // Setting to ``null`` will remove this entirely
     *   - '**next**' => ``'&raquo;'``, // Setting to ``null`` will remove this entirely
     *   - '**dots**' => ``'&hellip;'``, // Setting to ``null`` will remove this entirely
     * - If ``$type == 'pager'``
     *   - '**wrapper**' => ``'<ul class="pager">{{ value }}</ul>'``,
     *   - '**previous**' => ``'<li class="previous"><a href="{{ url }}">&laquo; {{ value }}</a></li>'``,
     *   - '**next**' => ``'<li class="next"><a href="{{ url }}">{{ value }} &raquo;</a></li>'``,
     *
     * Put '**{{ value }}**' and '**{{ url }}**' where you want those to go.  If you set anything to ``null``, then only the '**{{ value }}**' will be returned.
     *
     * @example
     *
     * ```php
     * $pagination->html('links', array(
     *     'wrapper' => '<ul class="pagination pagination-sm">{{ value }}</ul>',
     * ));
     * ```
     */
    public function html($type = 'bootstrap', array $options = array())
    {
        if ($type == 'links' && !empty($this->links)) {
            $this->links = array_merge($this->links, $options);
        } elseif ($type == 'pager' && !empty($this->pager)) {
            $this->pager = array_merge($this->pager, $options);
        } else {
            // http://getbootstrap.com/components/#pagination
            $this->links = array(
                'wrapper' => '<ul class="pagination">{{ value }}</ul>',
                'link' => '<li><a href="{{ url }}">{{ value }}</a></li>',
                'active' => '<li class="active"><span>{{ value }}</span></li>',
                'disabled' => '<li class="disabled"><span>{{ value }}</span></li>',
                'previous' => '&laquo;',
                'next' => '&raquo;',
                'dots' => '&hellip;',
            );
            $this->pager = array(
                'wrapper' => '<ul class="pager">{{ value }}</ul>',
                'previous' => '<li class="previous"><a href="{{ url }}">&laquo; {{ value }}</a></li>',
                'next' => '<li class="next"><a href="{{ url }}">{{ value }} &raquo;</a></li>',
            );
            switch ($type) {
                case 'zurb_foundation': // http://foundation.zurb.com/docs/components/pagination.html
                    $this->html('links', array(
                        'active' => '<li class="current"><a href="">{{ value }}</a></li>',
                        'disabled' => '<li class="unavailable"><a href="">{{ value }}</a></li>',
                    ));
                    break;
                case 'semantic_ui': // http://semantic-ui.com/collections/menu.html#pagination
                    $this->html('links', array(
                        'wrapper' => '<div class="ui pagination menu">{{ value }}</div>',
                        'link' => '<a class="item" href="{{ url }}">{{ value }}</a>',
                        'active' => '<div class="active item">{{ value }}</div>',
                        'disabled' => '<div class="disabled item">{{ value }}</div>',
                        'previous' => '<i class="left arrow icon"></i>',
                        'next' => '<i class="right arrow icon"></i>',
                    ));
                    break;
                case 'materialize': // http://materializecss.com/pagination.html
                    $this->html('links', array(
                        'link' => '<li class="waves-effect"><a href="{{ url }}">{{ value }}</a></li>',
                        'active' => '<li class="active"><a href="#!">{{ value }}</a></li>',
                        'disabled' => '<li class="disabled"><a href="#!">{{ value }}</a></li>',
                        'previous' => '<i class="material-icons">keyboard_arrow_left</i>',
                        'next' => '<i class="material-icons">keyboard_arrow_right</i>',
                    ));
                    break;
                case 'uikit': // http://getuikit.com/docs/pagination.html
                    $this->html('links', array(
                        'wrapper' => '<ul class="uk-pagination">{{ value }}</ul>',
                        'active' => '<li class="uk-active"><span>{{ value }}</span></li>',
                        'disabled' => '<li class="uk-disabled"><span>{{ value }}</span></li>',
                        'previous' => '<i class="uk-icon-angle-double-left"></i>',
                        'next' => '<i class="uk-icon-angle-double-right"></i>',
                    ));
                    $this->html('pager', array(
                        'wrapper' => '<ul class="uk-pagination">{{ value }}</ul>',
                        'previous' => '<li class="uk-pagination-previous"><a href="{{ url }}"><i class="uk-icon-angle-double-left"></i> {{ value }}</a></li>',
                        'next' => '<li class="uk-pagination-next"><a href="{{ url }}">{{ value }} <i class="uk-icon-angle-double-right"></i></a></li>',
                    ));
                    break;
            }
        }
    }

    /**
     * Display pagination links.
     *
     * @param int $pad The number of neighboring links you would like to be displayed to the right, and to the left of the currently active link.  We fudge this number at times for there to be a consistent total number of links throughout.
     *
     * @return string
     */
    public function links($pad = 3)
    {
        if ($this->get === false || $this->total === 1) {
            return '';
        }
        $begin = $this->current - $pad;
        $end = $this->current + $pad;
        if ($begin < 1) {
            $begin = 1;
            $end = $pad * 2 + 1;
        }
        if ($end > $this->total) {
            $end = $this->total;
            $begin = $end - ($pad * 2);
            if ($begin < 1) {
                $begin = 1;
            }
        }
        $p = $this->links;
        $links = array();
        if ($p['previous'] && $this->current > 1) {
            $links[] = $this->format($p['link'], $p['previous'], $this->current - 1);
        }
        if ($p['dots'] && $begin > 1) {
            $links[] = $this->format($p['link'], 1);
            if ($begin == 3) {
                $links[] = $this->format($p['link'], 2);
            } elseif ($begin != 2) {
                $links[] = $this->format($p['disabled'], $p['dots']);
            }
        }
        for ($num = $begin; $num <= $end; ++$num) {
            if ($num == $this->current) {
                $links[] = $this->format($p['active'], $num);
            } else {
                $links[] = $this->format($p['link'], $num);
            }
        }
        if ($p['dots'] && $end < $this->total) {
            if ($end == ($this->total - 2)) {
                $links[] = $this->format($p['link'], $this->total - 1);
            } elseif ($end != ($this->total - 1)) {
                $links[] = $this->format($p['disabled'], $p['dots']);
            }
            $links[] = $this->format($p['link'], $this->total);
        }
        if ($p['next'] && $this->current < $this->total) {
            $links[] = $this->format($p['link'], $p['next'], $this->current + 1);
        }

        return "\n".$this->format($p['wrapper'], "\n\t".implode("\n\t", $links))."\n";
    }

    /**
     * Display pager links.  If you pass **$previous** and **$next** arrays, then there is no need to ``$this->set()`` anything.
     *
     * @param string|array $previous A prompt (string) for the previous page, or an ``array('url'=>'', 'title'=>'')`` to pass the values directly.
     * @param string|array $next     A prompt (string) for the next page, or an ``array('url'=>'', 'title'=>'')`` to pass the values directly.
     *
     * @return string
     */
    public function pager($previous = 'Previous', $next = 'Next')
    {
        $links = '';
        if (!empty($previous)) {
            if (is_array($previous)) {
                if (isset($previous['url']) && isset($previous['title'])) {
                    $links .= $this->format($this->pager['previous'], $previous['title'], $previous['url']);
                }
            } elseif (is_string($previous)) {
                if ($this->get && $this->total > 1 && $this->current > 1) {
                    $links .= $this->format($this->pager['previous'], $previous, $this->current - 1);
                }
            }
        }
        if (!empty($next)) {
            if (is_array($next)) {
                if (isset($next['url']) && isset($next['title'])) {
                    $links .= $this->format($this->pager['next'], $next['title'], $next['url']);
                }
            } elseif (is_string($next)) {
                if ($this->get && $this->current < $this->total) {
                    $links .= $this->format($this->pager['next'], $next, $this->current + 1);
                }
            }
        }

        return (!empty($links)) ? "\n".$this->format($this->pager['wrapper'], $links) : '';
    }

    /**
     * Formats a string, replacing '{{ value }}' and '{{ url }}' with those given.
     * 
     * @param mixed      $string The twig-styled format you want.  If it's not a string, then we just return the $value.
     * @param int|string $value  The value we are formatting.
     * @param int|string $url    The link.  If it's a page number then we'll append it to ``$this->url``.  You can skip this if $value is the page number.
     * 
     * @return string
     */
    private function format($string, $value, $url = null)
    {
        if (is_string($string)) {
            if (is_null($url) && is_numeric($value)) {
                $url = $value;
            }
            if (is_numeric($url)) {
                $url = $this->page($url);
            }
            $value = str_replace(array('{{ value }}', '{{ url }}'), array($value, $url), $string);
        }

        return $value;
    }

    /**
     * Append a page number to ``$this->url``.  The query parameter is removed on the first page.
     * 
     * @param int $num
     * 
     * @return string
     */
    private function page($num)
    {
        if ($num == 1) {
            return $this->page->url('delete', $this->url, $this->get);
        }

        return $this->page->url('add', $this->url, $this->get, $num.'of'.$this->total);
    }
}
