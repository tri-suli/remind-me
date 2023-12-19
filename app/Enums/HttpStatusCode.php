<?php

declare(strict_types=1);

namespace App\Enums;

use Symfony\Component\HttpFoundation\Response;

enum HttpStatusCode: int
{
    case OK = Response::HTTP_OK;
    case CREATED = Response::HTTP_CREATED;
    case NO_CONTENT = Response::HTTP_NO_CONTENT;
    case BAD_REQUEST = Response::HTTP_BAD_REQUEST;
    case UN_AUTHENTICATED = Response::HTTP_UNAUTHORIZED;
    case NOT_FOUND = Response::HTTP_NOT_FOUND;
    case FORBIDDEN = Response::HTTP_FORBIDDEN;
    case INVALID_ENTITY = Response::HTTP_UNPROCESSABLE_ENTITY;
    case ERROR_SERVER = Response::HTTP_INTERNAL_SERVER_ERROR;
    case ERROR_GATEWAY = Response::HTTP_BAD_GATEWAY;

    /**
     * Create a new http status code instance base on code
     *
     * @param  int  $code
     * @return HttpStatusCode
     */
    public static function create(int $code): HttpStatusCode
    {
        return match ($code) {
            200 => self::OK,
            201 => self::CREATED,
            204 => self::NO_CONTENT,
            400 => self::BAD_REQUEST,
            401 => self::UN_AUTHENTICATED,
            403 => self::FORBIDDEN,
            404 => self::NOT_FOUND,
            422 => self::INVALID_ENTITY,
            500 => self::ERROR_SERVER,
            502 => self::ERROR_GATEWAY
        };
    }

    /**
     * Get http status code text
     *
     * @return string
     */
    public function text(): string
    {
        return match ($this) {
            self::OK,
            self::CREATED,
            self::NO_CONTENT,
            self::BAD_REQUEST,
            self::FORBIDDEN,
            self::NOT_FOUND        => str_replace('_', ' ', strtolower($this->name)),
            self::UN_AUTHENTICATED => 'unauthenticated/unauthorized',
            self::INVALID_ENTITY   => 'unprocessable entity',
            self::ERROR_SERVER     => 'internal server error',
            self::ERROR_GATEWAY    => 'bad gateway'
        };
    }
}
