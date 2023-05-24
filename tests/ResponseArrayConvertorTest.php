<?php

use Core\Utils\ResponseConvertors\ResponseArrayConvertor;
use PHPUnit\Framework\TestCase;

class ResponseArrayConvertorTest extends TestCase
{

    public function testConvertJsonToArray()
    {
        $convertor = new ResponseArrayConvertor();

        $json = '{"key": "value"}';
        $result = $convertor->convertJsonToArray($json);

        $this->assertIsArray($result);
        $this->assertSame(['key' => 'value'], $result);
    }

    public function testConvertXmlToArray()
    {
        $convertor = new ResponseArrayConvertor();

        $xml = '<?xml version="1.0" encoding="UTF-8"?><root><key>value</key></root>';
        $result = $convertor->convertXmlToArray($xml);

        $expectedResult = [
            'key' => [
                'key' => 'value'
            ]
        ];

        $this->assertIsArray($result);
        $this->assertSame($expectedResult, $result);
    }

    public function testConvertCsvToArray()
    {
        $convertor = new ResponseArrayConvertor();

        $csvWithHeaders = "name,age,city\nJohn,25,New York\nJane,30,Los Angeles";
        $resultWithHeaders = $convertor->convertCsvToArray($csvWithHeaders);

        $expectedResultWithHeaders = [
            ['name', 'age', 'city'],
            ['John', '25', 'New York'],
            ['Jane', '30', 'Los Angeles']
        ];

        $this->assertIsArray($resultWithHeaders);
        $this->assertCount(3, $resultWithHeaders);
        $this->assertEquals($expectedResultWithHeaders, $resultWithHeaders);

        $csvWithoutHeaders = "John,25,New York\nJane,30,Los Angeles";
        $resultWithoutHeaders = $convertor->convertCsvToArray($csvWithoutHeaders);

        $expectedResultWithoutHeaders = [
            ['John', '25', 'New York'],
            ['Jane', '30', 'Los Angeles']
        ];

        $this->assertIsArray($resultWithoutHeaders);
        $this->assertCount(2, $resultWithoutHeaders);
        $this->assertEquals($expectedResultWithoutHeaders, $resultWithoutHeaders);
    }

}
