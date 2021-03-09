# Nova Translation Editor

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

Register the tool in NovaServiceProvider

```php
public function tools()
{
    return [
        new NovaTranslationEditor(),
    ];
}
```


## Publish

Publish the translations via the nova tool or via command line:

```bash
php artisan nova-translation-editor:publish
```

## Credits

- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
