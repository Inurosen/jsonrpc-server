<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inurosen\JsonRPCServer\DTO;


class RequestDTO
{
    /**
     * @var string
     */
    private $version;
    /**
     * @var string
     */
    private $method;
    /**
     * @var null|mixed
     */
    private $params;
    /**
     * @var null|mixed
     */
    private $id;

    public function __construct(string $version, string $method, $params = null, $id = null)
    {
        $this->version = $version;
        $this->method = $method;
        $this->params = $params;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return null
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }
}
