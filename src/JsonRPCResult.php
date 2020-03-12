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


use Inurosen\JsonRPCServer\Exceptions\ServerErrorException;
use Inurosen\JsonRPCServer\Exceptions\ValidationException;

class JsonRPCResult implements \JsonSerializable
{
    private $id;
    private $result;

    public function __construct($id, $result)
    {
        $this->id = $id;
        $this->result = $result;
    }

    public function jsonSerialize()
    {
        $jsonSerialized = [
            'jsonrpc' => '2.0',
            'id'      => $this->id,
        ];

        if ($this->result instanceof ValidationException) {
            $jsonSerialized['error'] = [
                'code'    => $this->result->getCode(),
                'message' => $this->result->getMessage(),
                'data'    => $this->result->getContext(),
            ];

            return $jsonSerialized;
        }

        if ($this->result instanceof ServerErrorException) {
            $jsonSerialized['error'] = [
                'code'    => $this->result->getCode(),
                'message' => $this->result->getMessage(),

            ];
            if ($this->result->getPrevious()) {
                $jsonSerialized['error']['data'] = [
                    'code'    => $this->result->getPrevious()->getCode(),
                    'message' => $this->result->getPrevious()->getMessage(),
                ];
            }

            return $jsonSerialized;
        }

        if ($this->result instanceof \Throwable) {
            $jsonSerialized['error'] = [
                'code'    => $this->result->getCode(),
                'message' => $this->result->getMessage(),
            ];

            return $jsonSerialized;
        }

        if ($this->result instanceof \JsonSerializable) {
            $jsonSerialized['result'] = $this->result->jsonSerialize();
        } elseif (is_object($this->result) && method_exists($this->result, 'toArray')) {
            $jsonSerialized['result'] = $this->result->toArray();
        } else {
            $jsonSerialized['result'] = $this->result;
        }

        return $jsonSerialized;
    }

    public function toArray()
    {
        return $this->jsonSerialize();
    }
}
