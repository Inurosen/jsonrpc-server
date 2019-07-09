<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inurosen\JsonRPCServer;

use Inurosen\JsonRPCServer\Exceptions\JsonRPCValidationException;
use Inurosen\JsonRPCServer\Exceptions\ServerErrorException;
use Inurosen\JsonRPCServer\Exceptions\ValidationException;
use Inurosen\JsonRPCServer\Interfaces\ValidatorInterface;

class JsonRPCService
{
    public const API_VERSION = '2.0';

    public const E_CODE_PARSE_ERROR = -32700;
    public const E_CODE_INVALID_REQUEST = -32600;
    public const E_CODE_METHOD_NOT_FOUND = -32601;
    public const E_CODE_INVALID_PARAMS = -32602;
    public const E_CODE_INTERNAL_ERROR = -32603;
    public const E_CODE_VALIDATOR_NOT_FOUND = -32000;
    public const E_CODE_SERVER_ERROR = -32001;

    public const E_MSG_PARSE_ERROR = 'Parse error';
    public const E_MSG_INVALID_REQUEST = 'Invalid request';
    public const E_MSG_METHOD_NOT_FOUND = 'Method not found';
    public const E_MSG_INVALID_PARAMS = 'Invalid params';
    public const E_MSG_INTERNAL_ERROR = 'Internal error';
    public const E_MSG_VALIDATOR_NOT_FOUND = 'Validator not found';
    public const E_MSG_SERVER_ERROR = 'Server error';

    public const OPTION_DI_RESOLVER = 'di_resolver';
    public const OPTION_SCOPE = 'scope';

    private $methods;
    private $results;
    private $isBatch = false;
    private $diResolver;
    private $scope;

    public function __construct($options = [])
    {
        $this->applyOptions($options);
        $this->methods = MethodRegistry::getMethods($this->scope);
    }

    private function applyOptions($options)
    {
        $this->diResolver = $options[self::OPTION_DI_RESOLVER] ?? null;
        $this->scope = $options[self::OPTION_SCOPE] ?? MethodRegistry::SCOPE_DEFAULT;
    }

    public function call(string $request)
    {
        try {
            $this->reset();
            $requests = $this->decode($request);
            $this->execute($requests);
        } catch (\Throwable $exception) {
            $this->results[] = new JsonRPCResult(null, $exception);
        }
    }

    private function reset()
    {
        $this->results = null;
        $this->isBatch = false;
    }

    private function decode(string $request)
    {
        $requestArray = json_decode($request, true);
        if (!is_array($requestArray) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(self::E_MSG_PARSE_ERROR, self::E_CODE_PARSE_ERROR);
        }
        if (strpos($request, '[') === 0) {
            $this->isBatch = true;

            return $requestArray;
        }

        return [$requestArray];
    }

    private function execute(array $requests)
    {
        foreach ($requests as $request) {
            try {
                $this->validate($request);
                $result = $this->resolveHandler($request);
            } catch (ValidationException $exception) {
                $result = $exception;
            } catch (\Throwable $exception) {
                $exception = new ServerErrorException(self::E_MSG_SERVER_ERROR, self::E_CODE_SERVER_ERROR, $exception);
                $result = $exception;
            }

            /**
             * Results are required only for non-notification requests
             *
             * @link https://www.jsonrpc.org/specification#notification
             */
            if (!empty($request['id'])) {
                $this->results[] = new JsonRPCResult($request['id'], $result);
            }
        }
    }

    private function validate(array $request)
    {
        if (empty($request['jsonrpc'])
            || $request['jsonrpc'] !== self::API_VERSION
            || empty($request['method'])) {
            throw new JsonRPCValidationException(self::E_MSG_INVALID_REQUEST, self::E_CODE_INVALID_REQUEST);
        }

        if (!isset($this->methods[$request['method']])) {
            throw new JsonRPCValidationException(self::E_MSG_METHOD_NOT_FOUND, self::E_CODE_METHOD_NOT_FOUND);
        }

        if (!empty($this->methods[$request['method']]['validator']) && $errors = $this->runValidator($request)) {
            throw new ValidationException(self::E_MSG_INVALID_PARAMS, self::E_CODE_INVALID_PARAMS, null, $errors);
        }
    }

    private function resolveHandler($request)
    {
        $resolvedMethod = $this->methods[$request['method']]['handler'];

        if (!empty($request['params'])) {
            $params = $request['params'];
        } else {
            $params = null;
        }

        if (is_callable($resolvedMethod)) {
            return call_user_func_array($resolvedMethod, [$params]);
        } else {
            list($class, $method) = explode('@', $resolvedMethod);

            if ($this->diResolver !== null) {
                $object = call_user_func_array($this->diResolver, [$class]);
            } else {
                $object = new $class;
            }

            if (!is_object($object) || !method_exists($object, $method)) {
                throw new \Exception(self::E_MSG_METHOD_NOT_FOUND, self::E_CODE_METHOD_NOT_FOUND);
            }

            return call_user_func_array([$object, $method], [$params]);
        }
    }

    private function runValidator($request)
    {
        $resolvedValidator = $this->methods[$request['method']]['validator'];

        if (!empty($request['params'])) {
            $params = $request['params'];
        } else {
            $params = null;
        }

        if (is_callable($resolvedValidator) && !$resolvedValidator instanceof ValidatorInterface) {
            return call_user_func_array($resolvedValidator, [$params]);
        } else {
            if (!method_exists($resolvedValidator, 'validate')) {
                throw new \Exception(self::E_MSG_VALIDATOR_NOT_FOUND, self::E_CODE_VALIDATOR_NOT_FOUND);
            }

            /** @var ValidatorInterface $object */
            $object = new $resolvedValidator($params);
            call_user_func_array([$object, 'validate'], [$params]);

            return $object->getErrors();
        }
    }

    /**
     * @deprecated
     */
    public function getResult()
    {
        return $this->getResponse();
    }

    public function getResponse()
    {
        return new JsonRPCResponse($this->results, $this->isBatch);
    }
}
