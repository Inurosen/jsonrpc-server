<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Inurosen\JsonRPCServer\JsonRPCService;
use Inurosen\JsonRPCServer\MethodRegistry;

class JsonRPCValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testValid()
    {
        $jsonRpcRequest1 = '{ "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1, "id": 1 }';
        $jsonRpcRequest2 = '{ "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1, "id": 1 }';
        $jsonRpcRequest3 = '{ "jsonrpc": "2.0", "method": "rpc.testBaz", "params": [1, 2], "id": 1 }';
        $jsonRpcRequest4 = '{ "jsonrpc": "2.0", "method": "rpc.testClosure", "params": {"id": 1}, "id": 1 }';

        $jsonRpcResult1 = '{"jsonrpc":"2.0","id":1,"result":"foo"}';
        $jsonRpcResult2 = '{"jsonrpc":"2.0","id":1,"result":1}';
        $jsonRpcResult3 = '{"jsonrpc":"2.0","id":1,"error":{"code":-32001,"message":"Server error","data":{"code":123,"message":"On noes!"}}}';
        $jsonRpcResult4 = '{"jsonrpc":"2.0","id":1,"result":{"id":1}}';

        $service = new JsonRPCService();

        $service->call($jsonRpcRequest1);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult1, $result->toString());

        $service->call($jsonRpcRequest2);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult2, $result->toString());

        $service->call($jsonRpcRequest3);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult3, $result->toString());

        $service->call($jsonRpcRequest4);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult4, $result->toString());
    }

    public function testInvalid()
    {
        $jsonRpcRequest1 = '{ "jsonrpc": "2.0", "method": "rpc.testFoo", "params": "not integer", "id": 1 }';
        $jsonRpcRequest2 = '{ "jsonrpc": "2.0", "method": "rpc.testBar", "params": "not 1", "id": 1 }';
        $jsonRpcRequest3 = '{ "jsonrpc": "2.0", "method": "rpc.testBaz", "params": "not array", "id": 1 }';
        $jsonRpcRequest4 = '{ "jsonrpc": "2.0", "method": "rpc.testClosure", "params": {}, "id": 1 }';

        $jsonRpcResult1 = '{"jsonrpc":"2.0","id":1,"error":{"code":-32602,"message":"Invalid params","data":[["Field must be integer"]]}}';
        $jsonRpcResult2 = '{"jsonrpc":"2.0","id":1,"error":{"code":-32602,"message":"Invalid params","data":["Error"]}}';
        $jsonRpcResult3 = '{"jsonrpc":"2.0","id":1,"error":{"code":-32602,"message":"Invalid params","data":[["Field must be array"]]}}';
        $jsonRpcResult4 = '{"jsonrpc":"2.0","id":1,"error":{"code":-32602,"message":"Invalid params","data":{"id":["Field is required"]}}}';

        $service = new JsonRPCService();

        $service->call($jsonRpcRequest1);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult1, $result->toString());

        $service->call($jsonRpcRequest2);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult2, $result->toString());

        $service->call($jsonRpcRequest3);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult3, $result->toString());

        $service->call($jsonRpcRequest4);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult4, $result->toString());
    }

    protected function setUp()
    {
        MethodRegistry::register('rpc.testFoo', 'TestService@foo', TestValidatorSimpleInteger::class);
        MethodRegistry::register('rpc.testBar', 'TestService@bar', function ($params) {
            return $params === 1 ? [] : ['Error'];
        });
        MethodRegistry::register('rpc.testBaz', 'TestService@baz', TestValidatorSimpleArray::class);
        MethodRegistry::register('rpc.testClosure', function ($params) {
            return $params;
        }, TestValidatorStructureInteger::class);
    }

    protected function tearDown()
    {
        MethodRegistry::reset();
    }
}
