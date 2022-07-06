<?php

namespace CodeIgniter\Shield\Exceptions;

use CodeIgniter\HTTP\Exceptions\HTTPException as FrameworkHTTPException;

class HTTPException extends FrameworkHTTPException implements BaseException
{
}
