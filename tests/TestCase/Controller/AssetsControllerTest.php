<?php
/**
 * This file is part of Assets.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/assets
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Assets\Test\TestCase\Controller;

use Assets\Controller\AssetsController;
use Assets\TestSuite\IntegrationTestCase;
use Assets\Utility\AssetsCreator;
use Cake\Core\Configure;
use Cake\Filesystem\File;

/**
 * AssetControllerTest class
 */
class AssetsControllerTest extends IntegrationTestCase
{
    /**
     * Test for `asset()` method, with a a no existing file
     * @expectedException Assets\Http\Exception\AssetNotFoundException
     * @expectedExceptionMessageRegExp /^File `[\w\d\/\\.:]+` doesn't exist$/
     * @test
     */
    public function testAssetNoExistingFile()
    {
        (new AssetsController)->asset('noexistingfile.js');
    }

    /**
     * Test the response for `asset()` method, with a a no existing file
     * @test
     */
    public function testAssetNoExistingFileResponse()
    {
        $this->get('/assets/noexistingfile.js');
        $this->assertResponseError();
        $this->assertNull($this->_response->getFile());
    }

    /**
     * Test for `asset()` method, with a css asset
     * @test
     */
    public function testAssetWithCss()
    {
        //This is the filename
        $filename = sprintf('%s.%s', (new AssetsCreator('test', 'css'))->create(), 'css');

        $this->get(sprintf('/assets/%s', $filename));
        $this->assertResponseOk();
        $this->assertContentType('text/css');
        $this->assertFileResponse(Configure::read(ASSETS . '.target') . DS . $filename);
        $this->assertInstanceOf(File::class, $this->_response->getFile());
        $this->assertEquals([
            'dirname' => Configure::read(ASSETS . '.target'),
            'basename' => $filename,
            'extension' => 'css',
            'filename' => pathinfo($filename, PATHINFO_FILENAME),
            'filesize' => filesize(Configure::read(ASSETS . '.target') . DS . $filename),
            'mime' => 'text/plain',
        ], $this->_response->getFile()->info);

        //Gets the `Last-Modified` header
        $lastModified = $this->_response->getHeader('Last-Modified')[0];
        $this->assertNotEmpty($lastModified);

        //It still requires the same asset file. It gets the 304 status code
        sleep(1);
        $this->configRequest(['headers' => ['If-Modified-Since' => $lastModified]]);
        $this->get(sprintf('/assets/%s', $filename));
        $this->assertResponseCode(304);

        //Deletes the asset file. Now the `Last-Modified` header is different
        //@codingStandardsIgnoreLine
        @unlink(Configure::read(ASSETS . '.target') . DS . $filename);

        sleep(1);
        $filename = sprintf('%s.%s', (new AssetsCreator('test', 'css'))->create(), 'css');
        $this->get(sprintf('/assets/%s', $filename));
        $this->assertResponseOk();
        $this->assertNotEquals($lastModified, $this->_response->getHeader('Last-Modified')[0]);
    }

    /**
     * Test for `asset()` method, with a js asset
     * @test
     */
    public function testAssetWithJs()
    {
        //This is the filename
        $filename = sprintf('%s.%s', (new AssetsCreator('test', 'js'))->create(), 'js');

        $this->get(sprintf('/assets/%s', $filename));
        $this->assertResponseOk();
        $this->assertContentType('application/javascript');
        $this->assertFileResponse(Configure::read(ASSETS . '.target') . DS . $filename);
        $this->assertInstanceOf(File::class, $this->_response->getFile());
        $this->assertEquals([
            'dirname' => Configure::read(ASSETS . '.target'),
            'basename' => $filename,
            'extension' => 'js',
            'filename' => pathinfo($filename, PATHINFO_FILENAME),
            'filesize' => filesize(Configure::read(ASSETS . '.target') . DS . $filename),
            'mime' => 'text/plain',
        ], $this->_response->getFile()->info);
    }
}
