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

use Inurosen\JsonRPCServer\DTO\RequestDTO;
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
    public const OPTION_ERROR_HANDLER = 'error_handler';
    public const OPTION_PARAMS_GETTER = 'params_getter';
    public const SCOPE_DEFAULT = 'default';

    private static $instances = [];

    private $methods;
    private $results;
    private $isBatch = false;
    private $diResolver;
    private $scope;
    private $errorHandler;
    private $beforeExecute = [];
    private $paramsGetter;

    public function __construct($options = [])
    {
        $this->applyOptions($options);
        $this->methods = MethodRegistry::getMethods($this->scope);
    }

    /**
     * Get an instance of JsonRPCService for specified scope
     * If the instance doesn't exist it will be created
     *
     * @param string $scope
     * @return static
     */
    public static function scope(string $scope = self::SCOPE_DEFAULT): self
    {
        if (!isset(self::$instances[$scope])) {
            self::$instances[$scope] = new self([self::OPTION_SCOPE => $scope]);
        }

        return self::$instances[$scope];
    }

    /**
     * Call a JSON-RPC request
     *
     * @param string $request
     * @return $this
     */
    public function call(string $request): self
    {
        try {
            $this->reset();
            $requests = $this->decode($request);
            $this->execute($requests);
        } catch (\Throwable $exception) {
            $this->results[] = new JsonRPCResult(null, $exception);
        }

        return $this;
    }

    /**
     * Get a response for the last call
     *
     * @return JsonRPCResponse
     */
    public function getResponse(): JsonRPCResponse
    {
        return new JsonRPCResponse($this->results, $this->isBatch);
    }

    /**
     * Add a function to be executed before executing the single JSON-RPC request
     * This function will be called for each request in batch
     * The callable function accepts following arguments:
     * - RequestDTO object
     *
     * @param callable $function
     * @return $this
     * @see RequestDTO
     */
    public function addBeforeExecute(callable $function): self
    {
        $this->beforeExecute[] = $function;

        return $this;
    }

    /**
     * Set an error handler
     * The callable function accepts following arguments:
     * - Thrown exception
     *
     * @param callable|null $function
     * @return $this
     */
    public function setErrorHandler(?callable $function): self
    {
        $this->applyOptions([self::OPTION_ERROR_HANDLER => $function]);

        return $this;
    }

    /**
     * Set a dependency resolver function
     * The callable function accepts following arguments:
     * - Class name of a request handler
     * - RequestDTO object
     *
     * @param callable|null $function
     * @return $this
     */
    public function setDependencyResolver(?callable $function): self
    {
        $this->applyOptions([self::OPTION_DI_RESOLVER => $function]);

        return $this;
    }

    /**
     * Set a request params getter function
     * Whatever the callable function returns will be supplied to request handler
     * The callable function accepts following arguments:
     * - RequestDTO object
     *
     * @param callable|null $function
     * @return $this
     */
    public function setParamsGetter(?callable $function): self
    {
        $this->applyOptions([self::OPTION_PARAMS_GETTER => $function]);

        return $this;
    }

    private function applyOptions($options)
    {
        $this->diResolver = $options[self::OPTION_DI_RESOLVER] ?? null;
        $this->scope = $options[self::OPTION_SCOPE] ?? self::SCOPE_DEFAULT;
        if (isset($options[self::OPTION_ERROR_HANDLER]) && is_callable($options[self::OPTION_ERROR_HANDLER])) {
            $this->errorHandler = $options[self::OPTION_ERROR_HANDLER];
        }
        if (isset($options[self::OPTION_PARAMS_GETTER]) && is_callable($options[self::OPTION_PARAMS_GETTER])) {
            $this->paramsGetter = $options[self::OPTION_PARAMS_GETTER];
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
                $request = $this->convertRequestToDTO($request);
                $this->validate($request);
                $this->callBeforeExecute($request);
                $result = $this->resolveHandler($request);
            } catch (ValidationException $exception) {
                $result = $exception;
            } catch (\Throwable $exception) {
                if ($this->errorHandler) {
                    $handled = call_user_func($this->errorHandler, $exception);
                    if ($handled instanceof \Throwable) {
                        $exception = $handled;
                    }
                } else {
                    $exception = new ServerErrorException(self::E_MSG_SERVER_ERROR, self::E_CODE_SERVER_ERROR,
                        $exception);
                }
                $result = $exception;
            }

            /**
             * Results are required only for non-notification requests
             *
             * @link https://www.jsonrpc.org/specification#notification
             */
            if (!empty($request->getId())) {
                $this->results[] = new JsonRPCResult($request->getId(), $result);
            }
        }
    }

    private function validate(RequestDTO $request)
    {
        if ($request->getVersion() !== self::API_VERSION
            || empty($request->getMethod())) {
            throw new JsonRPCValidationException(self::E_MSG_INVALID_REQUEST, self::E_CODE_INVALID_REQUEST);
        }

        if (!isset($this->methods[$request->getMethod()])) {
            throw new JsonRPCValidationException(self::E_MSG_METHOD_NOT_FOUND, self::E_CODE_METHOD_NOT_FOUND);
        }

        if (!empty($this->methods[$request->getMethod()]['validator']) && $errors = $this->runValidator($request)) {
            throw new ValidationException(self::E_MSG_INVALID_PARAMS, self::E_CODE_INVALID_PARAMS, null, $errors);
        }

        return $request;
    }

    private function resolveHandler(RequestDTO $request)
    {
        $resolvedMethod = $this->methods[$request->getMethod()]['handler'];

        $params = $this->getParams($request);

        if (is_callable($resolvedMethod)) {
            return call_user_func_array($resolvedMethod, [$params, $request]);
        } else {
            if (is_array($resolvedMethod)) {
                $class = $resolvedMethod[0];
                $method = $resolvedMethod[1];
            } else {
                $handler = explode('@', $resolvedMethod);
                $class = $handler[0];

                if (isset($handler[1])) {
                    $method = $handler[1];
                }
            }
            if ($this->diResolver !== null) {
                $object = call_user_func_array($this->diResolver, [$class, $request]);
            } else {
                $object = new $class($request);
            }

            if (!is_object($object)) {
                throw new \Exception(self::E_MSG_METHOD_NOT_FOUND, self::E_CODE_METHOD_NOT_FOUND);
            }

            if (isset($method)) {
                return call_user_func_array([$object, $method], [$params]);
            }

            return call_user_func_array($object, [$params]);
        }
    }

    private function runValidator(RequestDTO $request)
    {
        $resolvedValidator = $this->methods[$request->getMethod()]['validator'];

        $params = $request->getParams();

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

    private function callBeforeExecute(RequestDTO $request)
    {
        foreach ($this->beforeExecute as $beforeExecute) {
            call_user_func_array($beforeExecute, [$request]);
        }
    }

    private function convertRequestToDTO($request): RequestDTO
    {
        $version = $request['jsonrpc'] ?? '';
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? null;
        $id = $request['id'] ?? null;

        return new RequestDTO($version, $method, $params, $id);
    }

    private function getParams(RequestDTO $request)
    {
        $params = $request->getParams();
        if (is_callable($this->paramsGetter)) {
            $params = call_user_func_array($this->paramsGetter, [$request]);
        }

        return $params;
    }
}
