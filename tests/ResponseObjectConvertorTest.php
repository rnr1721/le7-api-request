<?php

use Core\Utils\ResponseConvertors\ResponseObjectConvertor;
use PHPUnit\Framework\TestCase;
use Core\Exceptions\ResponseConvertorException;

class ResponseObjectConvertorTest extends TestCase
{

    public function testConvertJsonToObject()
    {
        $convertor = new ResponseObjectConvertor();
        $json = '{"name": "John", "age": 30}';

        $data = $convertor->convertJsonToObject($json);

        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertEquals('John', $data->name);
        $this->assertEquals(30, $data->age);
    }

    public function testConvertJsonToObjectThrowsExceptionForInvalidJson()
    {
        $convertor = new ResponseObjectConvertor();
        $invalidJson = '{"name": "John", "age": 30,}';

        $this->expectException(ResponseConvertorException::class);
        $this->expectExceptionMessage('Failed to decode JSON');

        $convertor->convertJsonToObject($invalidJson);
    }

    public function testConvertXmlToObject()
    {
        $convertor = new ResponseObjectConvertor();
        $xml = '<?xml version="1.0" encoding="UTF-8"?><root><name>John</name><age>30</age></root>';

        $data = $convertor->convertXmlToObject($xml);

        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertEquals('John', $data->name);
        $this->assertEquals(30, $data->age);
    }

    public function testConvertXmlToObjectThrowsExceptionForInvalidXml()
    {
        $convertor = new ResponseObjectConvertor();
        $invalidXml = '<?xml version="1.0" encoding="UTF-8"?><root><name>John</name><age>30</age>';

        $this->expectException(ResponseConvertorException::class);
        $this->expectExceptionMessage('Failed to parse XML');

        $convertor->convertXmlToObject($invalidXml);
    }

    public function testConvertCsvToObject()
    {
        $convertor = new ResponseObjectConvertor();
        $csv = "John,Doe,30\nJane,Smith,25";

        $data = $convertor->convertCsvToObject($csv);

        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertCount(2, $data->rows);
        $this->assertEquals(['John', 'Doe', '30'], $data->rows[0]->data->cells);
        $this->assertEquals(['Jane', 'Smith', '25'], $data->rows[1]->data->cells);
    }

}
