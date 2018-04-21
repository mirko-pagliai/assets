# 1.x branch
## 1.1 branch
### 1.1.8
* solved a serious bug that prevented correct paths resolution under Windows;
* `AssetNotFoundException` moved to `Assets\Http\Exception` namespace;
* replaced `InternalErrorException` with `InvalidArgumentException` and 
    `RuntimeException`. This allows compatibility with CakePHP 3.6 branch;
* added `IntegrationTestCase` and `TestCase` classes;
* full support for Windows, added Appveyor tests;
* now it uses the `mirko-pagliai/php-tools` package. This also replaces
    `mirko-pagliai/reflection`.

### 1.1.7
* the `Last-Modified` header is set up and sent to the client. It indicates the
    date and time at which the asset file was modified for the last time;
* the timestamp is attached to the assets, if required by the application
    configuration (`Asset.timestamp`);
* added `AssetCreator:filename()` and `AssetCreator::path()` methods;
* `AssetCreator::getAssetPath()` method renamed as `resolveAssetPath()` and 
    `AssetCreator::resolvePath()` method renamed as `resolveFilePaths()`. This
    creates less confusion about the names and functions of class methods.

### 1.1.6
* updated for CakePHP 3.5.

### 1.1.5
* the MIT license has been applied.

### 1.1.4
* the target directory is created automatically, if it does not exist;
* added `ASSETS` constant.

### 1.1.3
* added `AssetNotFoundException` class. This exception is thrown when you
    request an asset that is not available;
* methods that have been deprecated with CakePHP 3.4 have been replaced;
* updated for CakePHP 3.4.

### 1.1.2
* fixed bug. Urls no longer contain the type of asset, but only the filename.

### 1.1.1
* the `AssetCreator` class has been rewritten, using only objects and improving
    the code;
* you can configure the plugin both before and after it is loaded.

### 1.1.0
* it uses `matthiasmullie/minify` instead of `clean-css` and `UglifyJS`;
* the `Asset` utility has been renamed as `AssetsCreator` and now it uses the
    temporary directory (`APP/tmp/assets/`);
* added the `AssetsController` with the `asset()` method, which takes care of
    sending assets;
* added tests for `AssetsController`, `AssetsCreator` and `AssetHelper` classes.

## 1.0 branch
### 1.0.2
* fixed code for CakePHP Code Sniffer.

### 1.0.1
* improved code;
* executables are set up and checked in the bootstrap.
