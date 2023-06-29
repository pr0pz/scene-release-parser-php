# __Scene Release Parser__

![Made with PHP](https://img.shields.io/static/v1?label&message=PHP&color=777BB3&logo=php&logoColor=fff)
![Packagist package version](https://img.shields.io/packagist/v/propz/release-parser?color=777BB3&label=Packagist)
![Minimum PHP version: 7.0.0](https://img.shields.io/packagist/dependency-v/propz/release-parser/php?color=777BB3&label=PHP)

## __A library for parsing scene release names into simpler, reusable data.__

_Like it? I'd appreciate the support :)_

[![Follow on Twitter](https://img.shields.io/static/v1?label=Follow%20on&message=Twitter&color=1DA1F2&logo=twitter&logoColor=fff)](https://twitter.com/pr0pz)
[![Watch on Twitch](https://img.shields.io/static/v1?label=Watch%20on&message=Twitch&color=bf94ff&logo=twitch&logoColor=fff)](https://www.twitch.tv/the_propz)
[![Join on Discord](https://img.shields.io/static/v1?label=Join%20on&message=Discord&color=7289da&logo=discord&logoColor=fff)](https://discord.gg/FtuYUFC5)
[![Donate on Ko-Fi](https://img.shields.io/static/v1?label=Donate%20on&message=Ko-Fi&color=ff5f5f&logo=kofi&logoColor=fff)](https://ko-fi.com/propz)

### __Description__

This library parses scene release names and splits the data into smaller, simpler, human readable and therefore more reusable data.

The applied rules are mostly based on studying the [existing collection of Scene rules](https://scenerules.org/) and other release examples from a PreDB, since a lot of releases are not named correctly (specially older ones).

The approach was to implement an algorithm that can really parse a variety of scene releases from all decades. The main test file covers some more complex names.

### __Instructions__

I assume you already know some PHP and [composer](https://getcomposer.org/) is already installed on your computer. The next steps are:

› Install the library via composer ___OR___ download the [latest release](https://github.com/pr0pz/scene-release-parser-php/releases/latest);
```sh
$ composer require propz/release-parser
```

› Include the composer autoloader file into your project:\
```php
require_once __DIR__ . '/vendor/autoload.php'
```
› Create a new ReleaseParser Class and pass the release name and (optionally) the release section (for better type parsing) as parameters;\
› You can use the get() function to retrieve an array with all values or just target a specific value with get('name')

__Example:__

```php
<?php
// Include main composer autoloader file ...
require_once __DIR__ . '/vendor/autoload.php'

// Create class
$release = new \ReleaseParser\ReleaseParser( '24.S02E02.9.00.Uhr.bis.10.00.Uhr.German.DL.TV.Dubbed.DVDRip.SVCD.READ.NFO-c0nFuSed', 'tv' );

// See whats inside
print_r( $release->get() );

=> (
    [release] => 24.S02E02.9.00.Uhr.bis.10.00.Uhr.German.DL.TV.Dubbed.DVDRip.SVCD.READ.NFO-c0nFuSed
    [title] => 24
    [title_extra] => 9 00 Uhr bis 10 00 Uhr
    [group] => c0nFuSed
    [year] =>
    [date] =>
    [season] => 2
    [episode] => 2
    [flags] => Array
        (
            [0] => READNFO
            [1] => TV Dubbed
        )

    [source] => DVDRip
    [format] => SVCD
    [resolution] =>
    [audio] =>
    [device] =>
    [os] =>
    [version] =>
    [language] => Array
        (
            [de] => German
            [multi] => Multilingual
        )

    [type] => TV
)

// Other examples
echo $release->get( 'source' );
DVDRip

echo $release->get( 'format' );
SVCD

print_r( $release->get( 'flags' ) );
Array
(
    [0] => READNFO
    [1] => TV Dubbed
)

```

### __Found any Bugs?__

If you find any bugs/errors, feel free to [post an issue](https://github.com/pr0pz/scene-release-parser-php/issues).

### __Similar projects and inspirations__
- [pr0pz/scene-release-parser](https://github.com/pr0pz/scene-release-parser) (JavaScript)
- [matiassingers/scene-release](https://github.com/matiassingers/scene-release) (JavaScript)
- [thcolin/scene-release-parser-php](https://github.com/thcolin/scene-release-parser-php) (PHP)
- [majestixx/scene-release-parser-php-lib](https://github.com/majestixx/scene-release-parser-php-lib) (PHP)


### __License__

![License: MIT](https://img.shields.io/packagist/l/propz/release-parser)

_That's it!_

___Be excellent to each other. And, Party on, dudes!___