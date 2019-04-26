<?php
namespace DividoPayment\Tests;
use Shopware\Components\Test\Plugin\TestCase;
class ExampleTest extends TestCase
{
    /**
     * Should load our plugin with the config variables contained 
     * within the array
     */
    protected static $ensureLoadedPlugins = [
        'DividoPayment' => [
            'Api Key' => 'sandbox_9cd4dd19.3921d11e609e5a0a68c91c664e04fc8a'
        ]
    ];
    /**
     * Just a simple test to see if the API key is getting set
     */
    public function testApiKeyIsSet()
    {
        require_once(__DIR__.'/../Controllers/Frontend/DividoPayment.php');
        $stub = $this->createMock(\Shopware_Controllers_Frontend_DividoPayment::class);
        
        $this->assertEquals(
            self::$ensureLoadedPlugins['DividoPayment']['Api Key'],
            $stub->getDividoApiKey()
        );
    }

    
}