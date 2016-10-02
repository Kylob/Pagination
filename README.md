# use BootPress\Pagination\Component as Pagination;

[![Packagist][badge-version]][link-packagist]
[![License MIT][badge-license]](LICENSE.md)
[![HHVM Tested][badge-hhvm]][link-travis]
[![PHP 7 Supported][badge-php]][link-travis]
[![Build Status][badge-travis]][link-travis]
[![Code Climate][badge-code-climate]][link-code-climate]
[![Test Coverage][badge-coverage]][link-coverage]

Creates customizable pagination and pager links.  Limits and offsets arrays and database queries.  Built-in styles for Bootstrap, Zurb Foundation, Semantic UI, Materialize, and UIkit.

## Installation

Add the following to your ``composer.json`` file.

``` bash
{
    "require ": {
        "bootpress/pagination": "^1.0"
    }
}
```

## Example Usage

``` php
<?php

use BootPress\Pagination\Component as Paginator;

$pagination = new Paginator;

// Paginate an array
$records = range(1, 100);
if (!$pagination->set('page', 10, 'http://example.com')) {
    $pagination->total(count($records));
}
$display = array_slice($records, $pagination->offset, $pagination->length);
echo implode(',', $display); // 1,2,3,4,5,6,7,8,9,10

// Generate pagination links
echo $pagination->links();
/*
<ul class="pagination">
    <li class="active"><span>1</span></li>
    <li><a href="http://example.com?page=2of10">2</a></li>
    <li><a href="http://example.com?page=3of10">3</a></li>
    <li><a href="http://example.com?page=4of10">4</a></li>
    <li><a href="http://example.com?page=5of10">5</a></li>
    <li><a href="http://example.com?page=6of10">6</a></li>
    <li><a href="http://example.com?page=7of10">7</a></li>
    <li class="disabled"><span>&hellip;</span></li>
    <li><a href="http://example.com?page=10of10">10</a></li>
    <li><a href="http://example.com?page=2of10">&raquo;</a></li>
</ul>
*/

// And a pager for good measure
echo $pagination->pager();
/*
<ul class="pager">
    <li class="next"><a href="http://example.com?page=2of10">Next &raquo;</a></li>
</ul>
*/
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[badge-version]: https://img.shields.io/packagist/v/bootpress/pagination.svg?style=flat-square&label=Packagist
[badge-license]: https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square
[badge-hhvm]: https://img.shields.io/badge/HHVM-Tested-8892bf.svg?style=flat-square
[badge-php]: https://img.shields.io/badge/PHP%207-Supported-8892bf.svg?style=flat-square
[badge-travis]: https://img.shields.io/travis/Kylob/Pagination/master.svg?style=flat-square
[badge-code-climate]: https://img.shields.io/codeclimate/github/Kylob/Pagination.svg?style=flat-square
[badge-coverage]: https://img.shields.io/codeclimate/coverage/github/Kylob/Pagination.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bootpress/pagination
[link-travis]: https://travis-ci.org/Kylob/Pagination
[link-code-climate]: https://codeclimate.com/github/Kylob/Pagination
[link-coverage]: https://codeclimate.com/github/Kylob/Pagination/coverage
