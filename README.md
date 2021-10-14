# Nova Translation Editor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dive-be/nova-translation-editor.svg?style=flat-square)](https://packagist.org/packages/dive-be/nova-translation-editor)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/dive-be/nova-translation-editor.svg?style=flat-square)](https://packagist.org/packages/dive-be/nova-translation-editor)

⚠️ Minor releases of this package may cause breaking changes as it has no stable release yet.

## Installation

```shell
composer require dive-be/nova-translation-editor
```

You must publish and run the migrations to create the `language_lines` table:

```bash
php artisan vendor:publish --provider="Dive\NovaTranslationEditor\ToolServiceProvider" --tag="migrations"
php artisan migrate
```

Optionally you could publish the config file using this command.

```bash
php artisan vendor:publish --provider="Dive\NovaTranslationEditor\ToolServiceProvider" --tag="config"
```

Register the tool in `NovaServiceProvider`.

```php
public function tools()
{
    return [
        new NovaTranslationEditor(),
    ];
}
```

Publish the translations via the nova tool or via command line:

```bash
php artisan nova-translation-editor:publish
```
## Usage

TODO

## Credits

- [Michiel Vancoillie](https://github.com/dive-michiel)
- [Muhammed Sari](https://github.com/mabdullahsari)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
