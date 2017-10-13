# LogAnalyzer
## Usage

Simple usage.

```php
$builder = new CollectionBuilder();
$builder->addApacheLog('/path/to/apache.log');
$collection = $builder->build();

$collection->dimension('request')->addColumn('host')->addColumn('HeaderUserAgent')->display();

/*
+-----------------------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
| request                                 | Count | host                           | HeaderUserAgent                                                          |
+-----------------------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
| POST /gmo_id/xmlrpc.php HTTP/1.0        | 3     | [133.130.35.34, 133.130.35.35] | PHP XMLRPC 1.0                                                           |
| GET /?mode=rss HTTP/1.1                 | 1     | 23.96.184.214                  | NewsGatorOnline/2.0 (http://www.newsgator.com; 1 subscribers)            |
| GET /robots.txt HTTP/1.1                | 1     | 93.158.152.5                   | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)         |
| GET /api/waf/api_free.php HTTP/1.1      | 1     | 93.158.152.5                   | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)         |
| GET /api/waf/design.php HTTP/1.1        | 1     | 66.249.79.82                   | Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html) |
| GET /api/waf/page.css?20140314 HTTP/1.1 | 1     | 66.249.79.82                   | Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html) |
+-----------------------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
*/
```

You can calculate dimension value by closure. In closure, you can get log value through ItemInterface.

```php
$builder = new CollectionBuilder();
$builder->addApacheLog('/path/to/apache.log');
$collection = $builder->build();

$collection->dimension('request', function (ItemInterface $item) {
    // Remove HTTP method and HTTP version.
    if (preg_match('/[^\s]+\s([^\s]+)\sHTTP/', $item->get('request'), $matches)) {
        return $matches[1];
    }
    return null;
})->addColumn('host')->addColumn('HeaderUserAgent')->display();

/*
+----------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
| request                    | Count | host                           | HeaderUserAgent                                                          |
+----------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
| /gmo_id/xmlrpc.php         | 3     | [133.130.35.34, 133.130.35.35] | PHP XMLRPC 1.0                                                           |
| /?mode=rss                 | 1     | 23.96.184.214                  | NewsGatorOnline/2.0 (http://www.newsgator.com; 1 subscribers)            |
| /robots.txt                | 1     | 93.158.152.5                   | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)         |
| /api/waf/api_free.php      | 1     | 93.158.152.5                   | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)         |
| /api/waf/design.php        | 1     | 66.249.79.82                   | Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html) |
| /api/waf/page.css?20140314 | 1     | 66.249.79.82                   | Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html) |
+----------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
*/
```

If you want to analyze more detail, you can get Collection object recursively from the View object.

```php
$builder = new CollectionBuilder();
$builder->addApacheLog('/path/to/apache.log');
$collection = $builder->build();

$view = $collection->dimension('request');
$view->addColumn('host')->addColumn('HeaderUserAgent')->display();

$collection = $view->getCollection('POST /gmo_id/xmlrpc.php HTTP/1.0');
$collection->dimension('host')->addColumn('HeaderUserAgent')->display();

/*
+-----------------------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
| request                                 | Count | host                           | HeaderUserAgent                                                          |
+-----------------------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
| POST /gmo_id/xmlrpc.php HTTP/1.0        | 3     | [133.130.35.34, 133.130.35.35] | PHP XMLRPC 1.0                                                           |
| GET /?mode=rss HTTP/1.1                 | 1     | 23.96.184.214                  | NewsGatorOnline/2.0 (http://www.newsgator.com; 1 subscribers)            |
| GET /robots.txt HTTP/1.1                | 1     | 93.158.152.5                   | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)         |
| GET /api/waf/api_free.php HTTP/1.1      | 1     | 93.158.152.5                   | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)         |
| GET /api/waf/design.php HTTP/1.1        | 1     | 66.249.79.82                   | Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html) |
| GET /api/waf/page.css?20140314 HTTP/1.1 | 1     | 66.249.79.82                   | Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html) |
+-----------------------------------------+-------+--------------------------------+--------------------------------------------------------------------------+
+---------------+-------+-----------------+
| host          | Count | HeaderUserAgent |
+---------------+-------+-----------------+
| 133.130.35.34 | 2     | PHP XMLRPC 1.0  |
| 133.130.35.35 | 1     | PHP XMLRPC 1.0  |
+---------------+-------+-----------------+
*/
```
