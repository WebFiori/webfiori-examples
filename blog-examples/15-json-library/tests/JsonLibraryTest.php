<?php
namespace Tests;

use App\Domain\Customer;
use App\Domain\LineItem;
use App\Domain\Order;
use App\Domain\Product;
use PHPUnit\Framework\TestCase;
use WebFiori\Json\Json;

class JsonLibraryTest extends TestCase {

    protected function setUp(): void {
        Json::resetDefaults();
    }

    // --- Basic serialization ---

    public function testBasicScalarTypes(): void {
        $json = new Json([
            'name'    => 'Alice',
            'age'     => 30,
            'active'  => true,
            'score'   => 9.5,
            'notes'   => null,
        ]);

        $decoded = json_decode((string) $json, true);
        $this->assertEquals('Alice', $decoded['name']);
        $this->assertEquals(30, $decoded['age']);
        $this->assertTrue($decoded['active']);
        $this->assertEquals(9.5, $decoded['score']);
        $this->assertNull($decoded['notes']);
    }

    public function testAssociativeArrayEncodedAsObject(): void {
        $json = new Json();
        $json->addArray('address', ['city' => 'Riyadh', 'country' => 'SA']);

        $decoded = json_decode((string) $json, true);
        $this->assertEquals('Riyadh', $decoded['address']['city']);
        $this->assertEquals('SA', $decoded['address']['country']);
    }

    // --- Auto-mapping with attributes ---

    public function testJsonPropertyRenamesField(): void {
        $json = new Json();
        $json->addObject('product', new Product(1, 'Laptop', 999.99, 'electronics'));

        $decoded = json_decode((string) $json, true);
        $this->assertArrayHasKey('product_id', $decoded['product']);
        $this->assertEquals(1, $decoded['product']['product_id']);
    }

    public function testJsonIgnoreExcludesField(): void {
        $json = new Json();
        $json->addObject('product', new Product(1, 'Laptop', 999.99, 'electronics'));

        $decoded = json_decode((string) $json, true);
        $this->assertArrayNotHasKey('internalCode', $decoded['product']);
        $this->assertArrayNotHasKey('internal_code', $decoded['product']);
        $this->assertArrayNotHasKey('internalMargin', $decoded['product']);
        $this->assertArrayNotHasKey('internal_margin', $decoded['product']);
    }

    public function testAutoMappingIncludesGetters(): void {
        $json = new Json([], 'snake'); // snake style: getName() -> 'name'
        $json->addObject('product', new Product(1, 'Laptop', 999.99, 'electronics'));

        $decoded = json_decode((string) $json, true);
        $this->assertArrayHasKey('name', $decoded['product']);
        $this->assertArrayHasKey('price', $decoded['product']);
        $this->assertArrayHasKey('category', $decoded['product']);
        $this->assertEquals('Laptop', $decoded['product']['name']);
    }

    // --- Naming styles ---

    public function testCamelCaseStyle(): void {
        $json = new Json(['first-name' => 'Ibrahim', 'last-name' => 'Ali'], 'camel');

        $decoded = json_decode((string) $json, true);
        $this->assertArrayHasKey('firstName', $decoded);
        $this->assertArrayHasKey('lastName', $decoded);
    }

    public function testSnakeCaseStyle(): void {
        $json = new Json(['firstName' => 'Ibrahim', 'lastName' => 'Ali'], 'snake');

        $decoded = json_decode((string) $json, true);
        $this->assertArrayHasKey('first_name', $decoded);
        $this->assertArrayHasKey('last_name', $decoded);
    }

    public function testNoneStylePreservesKeys(): void {
        $json = new Json(['first-name' => 'Ibrahim', 'lastName' => 'Ali'], 'none');

        $decoded = json_decode((string) $json, true);
        $this->assertArrayHasKey('first-name', $decoded);
        $this->assertArrayHasKey('lastName', $decoded);
    }

    // --- Application-wide defaults ---

    public function testSetDefaultsAppliesToAllNewInstances(): void {
        Json::setDefaults(style: 'snake');

        $json = new Json(['firstName' => 'Alice', 'lastName' => 'Smith']);
        $decoded = json_decode((string) $json, true);

        $this->assertArrayHasKey('first_name', $decoded);
        $this->assertArrayHasKey('last_name', $decoded);
    }

    // --- Typed deserialization: simple object ---

    public function testDecodeAsSimpleObject(): void {
        $jsonStr = '{"name":"Alice","email":"alice@example.com"}';
        $customer = Json::decodeAs($jsonStr, Customer::class);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('Alice', $customer->getName());
        $this->assertEquals('alice@example.com', $customer->getEmail());
    }

    // --- Typed deserialization: nested object ---

    public function testDecodeAsWithNestedObject(): void {
        $jsonStr = json_encode([
            'id'       => 1,
            'customer' => ['name' => 'Bob', 'email' => 'bob@example.com'],
            'items'    => [
                ['productName' => 'Laptop', 'quantity' => 1, 'unitPrice' => 999.99],
                ['productName' => 'Mouse',  'quantity' => 2, 'unitPrice' => 29.99],
            ],
            'status' => 'confirmed',
        ]);

        $order = Json::decodeAs($jsonStr, Order::class);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(1, $order->getId());
        $this->assertInstanceOf(Customer::class, $order->getCustomer());
        $this->assertEquals('Bob', $order->getCustomer()->getName());
        $this->assertCount(2, $order->getItems());
        $this->assertInstanceOf(LineItem::class, $order->getItems()[0]);
        $this->assertEquals('Laptop', $order->getItems()[0]->getProductName());
        $this->assertEquals('confirmed', $order->getStatus());
    }

    public function testDeserializedOrderTotalIsCorrect(): void {
        $jsonStr = json_encode([
            'id'       => 2,
            'customer' => ['name' => 'Carol', 'email' => 'carol@example.com'],
            'items'    => [
                ['productName' => 'Desk', 'quantity' => 1, 'unitPrice' => 500.00],
                ['productName' => 'Chair', 'quantity' => 2, 'unitPrice' => 150.00],
            ],
            'status' => 'pending',
        ]);

        $order = Json::decodeAs($jsonStr, Order::class);

        $this->assertEquals(800.00, $order->getTotal());
    }

    // --- setTypeMap for dynamic mapping ---

    public function testSetTypeMapHydratesNestedObject(): void {
        $json = Json::decode('{"name":"Alice","email":"alice@example.com"}');
        $json->setTypeMap(['name' => 'string', 'email' => 'string']);

        $this->assertEquals('Alice', $json->get('name'));
        $this->assertEquals('alice@example.com', $json->get('email'));
    }

    // --- decode and get ---

    public function testDecodeAndGet(): void {
        $json = Json::decode('{"username":"ibrahim","score":99}');

        $this->assertEquals('ibrahim', $json->get('username'));
        $this->assertEquals(99, $json->get('score'));
    }

    public function testHasKey(): void {
        $json = new Json(['x' => 1]);

        $this->assertTrue($json->hasKey('x'));
        $this->assertFalse($json->hasKey('y'));
    }

    // --- Convert to PHP array via json_decode ---

    public function testConvertToArrayViaJsonDecode(): void {
        $json = new Json(['name' => 'Alice', 'age' => 30]);

        $arr = json_decode((string) $json, true);

        $this->assertIsArray($arr);
        $this->assertEquals('Alice', $arr['name']);
        $this->assertEquals(30, $arr['age']);
    }

    public function testNestedJsonDecodesCorrectly(): void {
        $inner = new Json(['city' => 'Riyadh']);
        $outer = new Json();
        $outer->addObject('address', $inner);

        $arr = json_decode((string) $outer, true);

        $this->assertIsArray($arr['address']);
        $this->assertEquals('Riyadh', $arr['address']['city']);
    }

    // --- remove ---

    public function testRemoveDeletesProperty(): void {
        $json = new Json(['a' => 1, 'b' => 2]);
        $json->remove('a');

        $decoded = json_decode((string) $json, true);
        $this->assertArrayNotHasKey('a', $decoded);
        $this->assertArrayHasKey('b', $decoded);
    }

    // --- formatted output ---

    public function testFormattedOutput(): void {
        $json = new Json(['name' => 'Alice']);
        $json->setIsFormatted(true);

        $output = (string) $json;
        $this->assertStringContainsString("\n", $output);
    }
}
