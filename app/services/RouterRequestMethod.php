<?php

namespace app\services;

enum RouterRequestMethod: string
{

    case ROUTER_DELETE = 'DELETE';
    case ROUTER_GET = 'GET';
    case ROUTER_HEAD = 'HEAD';
    case ROUTER_OPTIONS = 'OPTIONS';
    case ROUTER_PATCH = 'PATCH';
    case ROUTER_POST = 'POST';
    case ROUTER_PUT = 'PUT';

}