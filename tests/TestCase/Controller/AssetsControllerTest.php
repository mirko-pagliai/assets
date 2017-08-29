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
use Assets\Utility\AssetsCreator;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestCase;

/**
 * AssetControllerTest class
 */
class AssetsControllerTest extends IntegrationTestCase
{
    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        //Deletes all assets
        foreach (glob(Configure::read(ASSETS . '.target') . DS . '*') as $file) {
            unlink($file);
        }
    }

    /**
     * Test for `asset()` method, with a a no existing file
     * @expectedException Assets\Network\Exception\AssetNotFoundException
     * @expectedExceptionMessage File `/tmp/assets/noexistingfile.js` doesn't exist
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
        $this->assertEquals(404, $this->_response->statusCode());
        $this->assertNull($this->_response->getFile());
        $this->assertResponseError();
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

        $file = $this->_response->getFile();

        $this->assertInstanceOf('Cake\Filesystem\File', $file);
        $this->assertEquals([
            'dirname' => Configure::read(ASSETS . '.target'),
            'basename' => $filename,
            'extension' => 'css',
            'filename' => pathinfo($filename, PATHINFO_FILENAME),
            'filesize' => filesize(Configure::read(ASSETS . '.target') . DS . $filename),
            'mime' => 'text/plain',
        ], $file->info);
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

        $file = $this->_response->getFile();

        $this->assertInstanceOf('Cake\Filesystem\File', $file);
        $this->assertEquals([
            'dirname' => Configure::read(ASSETS . '.target'),
            'basename' => $filename,
            'extension' => 'js',
            'filename' => pathinfo($filename, PATHINFO_FILENAME),
            'filesize' => filesize(Configure::read(ASSETS . '.target') . DS . $filename),
            'mime' => 'text/plain',
        ], $file->info);
    }
}
