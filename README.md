# Scene Release Parser
## A PHP library for parsing scene release names.

The applied rules are mostly based on studying the [existing collection of Scene rules](https://scenerules.org/) and other release examples from a [PreDB](https://predb.de/), since a lot of releases are not named correctly (specially older ones).

The approach was to implement an algorithm that can really parse a variety of scene releases from all decades. The main test file covers some more complex names.


## Install

```sh
$ composer require propz/release-parser
```

TODO

## Usage

```php
use \ReleaseParser\ReleaseParser;

$release = new ReleaseParser( '24.S02E02.9.00.Uhr.bis.10.00.Uhr.German.DL.TV.Dubbed.DVDRip.SVCD.READ.NFO-c0nFuSed', 'tv' )

TODO ...
```


## CLI

TODO

## Similar projects and inspirations
- [matiassingers/scene-release](https://github.com/matiassingers/scene-release) (JavaScript)
- [thcolin/scene-release-parser-php](https://github.com/thcolin/scene-release-parser-php) (PHP)
- [majestixx/scene-release-parser-php-lib](https://github.com/majestixx/scene-release-parser-php-lib) (PHP)


## License

release-parser-php is licensed under the MIT License (MIT).