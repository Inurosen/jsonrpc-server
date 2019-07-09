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

class JsonRPCOptionsTest extends \PHPUnit\Framework\TestCase
{
    public function testDiResolver()
    {
        $jsonRpcRequest1 = '{ "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1, "id": 1 }';
        $jsonRpcRequest2 = '{ "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1, "id": 1 }';
        $jsonRpcRequest3 = '{ "jsonrpc": "2.0", "method": "rpc.testClosure", "params": [1, 2], "id": 1 }';

        $jsonRpcResult1 = '{"jsonrpc":"2.0","id":1,"result":"foo"}';
        $jsonRpcResult2 = '{"jsonrpc":"2.0","id":1,"result":1}';
        $jsonRpcResult3 = '{"jsonrpc":"2.0","id":1,"result":[1,2]}';

        $service = new JsonRPCService([
            JsonRPCService::OPTION_DI_RESOLVER => function ($class) {
                if ($class === TestService::class) {
                    return new $class;
                }

                return null;
            },
        ]);

        $service->call($jsonRpcRequest1);
        $result = $service->getResponse();
        $this->assertEquals($jsonRpcResult1, $result->toString());

        $service->call($jsonRpcRequest2);
        $result = $service->getResponse();
        $this->assertEquals($jsonRpcResult2, $result->toString());

        $service->call($jsonRpcRequest3);
        $result = $service->getResponse();
        $this->assertEquals($jsonRpcResult3, $result->toString());
    }

    public function testDiResolverNonExistentService()
    {
        $jsonRpcRequest1 = '{ "jsonrpc": "2.0", "method": "rpc.testFoo", "params": 1, "id": 1 }';
        $jsonRpcRequest2 = '{ "jsonrpc": "2.0", "method": "rpc.testBar", "params": 1, "id": 1 }';
        $jsonRpcRequest3 = '{ "jsonrpc": "2.0", "method": "rpc.testClosure", "params": [1, 2], "id": 1 }';

        $jsonRpcResult1 = '{"jsonrpc":"2.0","id":1,"error":{"code":-32001,"message":"Server error","data":{"code":-32601,"message":"Method not found"}}}';
        $jsonRpcResult2 = '{"jsonrpc":"2.0","id":1,"error":{"code":-32001,"message":"Server error","data":{"code":-32601,"message":"Method not found"}}}';
        $jsonRpcResult3 = '{"jsonrpc":"2.0","id":1,"result":[1,2]}';

        $service = new JsonRPCService([
            JsonRPCService::OPTION_DI_RESOLVER => function ($class) {
                return null;
            },
        ]);

        $service->call($jsonRpcRequest1);
        $result = $service->getResponse();
        $this->assertEquals($jsonRpcResult1, $result->toString());

        $service->call($jsonRpcRequest2);
        $result = $service->getResponse();
        $this->assertEquals($jsonRpcResult2, $result->toString());

        $service->call($jsonRpcRequest3);
        $result = $service->getResponse();
        $this->assertEquals($jsonRpcResult3, $result->toString());
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
