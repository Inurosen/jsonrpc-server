<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Inurosen\JsonRPCServer\MethodRegistry;

class JsonRPCServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testCallProcedure()
    {
        $jsonRpcRequest1 = '{ "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1, "id": 1 }';
        $jsonRpcRequest2 = '{ "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1, "id": 1 }';
        $jsonRpcRequest3 = '{ "jsonrpc": "2.0", "method": "rpc.testClosure", "params": [1, 2], "id": 1 }';

        $jsonRpcResult1 = '{"jsonrpc":"2.0","id":1,"result":"foo"}';
        $jsonRpcResult2 = '{"jsonrpc":"2.0","id":1,"result":1}';
        $jsonRpcResult3 = '{"jsonrpc":"2.0","id":1,"result":[1,2]}';

        $service = new \Inurosen\JsonRPCServer\JsonRPCService();

        $service->call($jsonRpcRequest1);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult1, $result->toString());

        $service->call($jsonRpcRequest2);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult2, $result->toString());

        $service->call($jsonRpcRequest3);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult3, $result->toString());
    }

    public function testCallNotification()
    {
        $service = new \Inurosen\JsonRPCServer\JsonRPCService();

        $jsonRpcRequest = '{ "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1}';

        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals('', $result->toString());
    }

    public function testCallProcedureBatch()
    {
        $jsonRpcRequest = '[
            { "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1, "id": 1 },
            { "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1, "id": 2 },
            { "jsonrpc": "2.0", "method": "rpc.testClosure", "params": [1, 2], "id": 3 }
        ]';

        $jsonRpcResult = '[{"jsonrpc":"2.0","id":1,"result":"foo"},{"jsonrpc":"2.0","id":2,"result":1},{"jsonrpc":"2.0","id":3,"result":[1,2]}]';

        $service = new \Inurosen\JsonRPCServer\JsonRPCService();
        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult, $result->toString());
    }

    public function testCallNotificationBatch()
    {
        $jsonRpcRequest = '[
            { "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1},
            { "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1},
            { "jsonrpc": "2.0", "method": "rpc.testClosure", "params": [1, 2]}
        ]';

        $service = new \Inurosen\JsonRPCServer\JsonRPCService();
        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals('', $result->toString());
    }

    public function testCallMixedBatch()
    {
        $jsonRpcRequest = '[
            { "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1, "id": 1 },
            { "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1 },
            { "jsonrpc": "2.0", "method": "rpc.testClosure", "params": [1, 2], "id": 3 }
        ]';

        $jsonRpcResult = '[{"jsonrpc":"2.0","id":1,"result":"foo"},{"jsonrpc":"2.0","id":3,"result":[1,2]}]';

        $service = new \Inurosen\JsonRPCServer\JsonRPCService();
        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult, $result->toString());
    }

    public function testParseError()
    {
        $service = new \Inurosen\JsonRPCServer\JsonRPCService();

        $jsonRpcRequest = '{ "jsonrpc": "2.0", "method": "rpc.testFoo", "params}';

        $jsonRpcResult = '{"jsonrpc":"2.0","id":null,"error":{"code":-32700,"message":"Parse error"}}';

        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult, $result->toString());
    }

    public function testInvalidRequest()
    {
        $service = new \Inurosen\JsonRPCServer\JsonRPCService();

        $jsonRpcRequest = '{ "jsonrpc": "2.0", "params": 1, "id": 1}';

        $jsonRpcResult = '{"jsonrpc":"2.0","id":1,"error":{"code":-32001,"message":"Server error","data":{"code":-32600,"message":"Invalid request"}}}';

        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult, $result->toString());
    }

    public function testMethodNotFound()
    {
        $service = new \Inurosen\JsonRPCServer\JsonRPCService();

        $jsonRpcRequest = '{ "jsonrpc": "2.0", "method": "rpc.testNonExistent", "params": 1, "id": 1}';

        $jsonRpcResult = '{"jsonrpc":"2.0","id":1,"error":{"code":-32001,"message":"Server error","data":{"code":-32601,"message":"Method not found"}}}';

        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult, $result->toString());
    }

    public function testBatchWithErrors()
    {
        $jsonRpcRequest = '[
            { "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1, "id": 1 },
            { "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1, "id": 2 },
            { "jsonrpc": "2.0", "method": "rpc.testNonExistent", "params": [1, 2], "id": 3 }
        ]';

        $jsonRpcResult = '[{"jsonrpc":"2.0","id":1,"result":"foo"},{"jsonrpc":"2.0","id":2,"result":1},{"jsonrpc":"2.0","id":3,"error":{"code":-32001,"message":"Server error","data":{"code":-32601,"message":"Method not found"}}}]';

        $service = new \Inurosen\JsonRPCServer\JsonRPCService();
        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult, $result->toString());
    }

    public function testServiceError()
    {
        $service = new \Inurosen\JsonRPCServer\JsonRPCService();

        $jsonRpcRequest = '{ "jsonrpc": "2.0", "method": "rpc.testBaz", "params": 1, "id": 1}';
        $jsonRpcResult = '{"jsonrpc":"2.0","id":1,"error":{"code":-32001,"message":"Server error","data":{"code":123,"message":"On noes!"}}}';

        $service->call($jsonRpcRequest);
        $result = $service->getResult();
        $this->assertEquals($jsonRpcResult, $result->toString());

    }


    protected function setUp()
    {
        MethodRegistry::register('rpc.testFoo', 'TestService@foo');
        MethodRegistry::register('rpc.testBar', 'TestService@bar');
        MethodRegistry::register('rpc.testBaz', 'TestService@baz');
        MethodRegistry::register('rpc.testClosure', function ($params) {
            return $params;
        });
    }

    protected function tearDown()
    {
        MethodRegistry::reset();
    }
}
