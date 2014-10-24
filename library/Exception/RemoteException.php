<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Exception;

use Exception as BaseException;

class RemoteException extends BaseException implements ExceptionInterface
{
    protected $remoteTrace = [];

    public function getRemoteTrace()
    {
        return $this->remoteTrace;
    }

    public static function factory(array $stack)
    {
        if (count($stack) > 0) {
            $exData = array_shift($stack);
            $prevEx = static::factory($stack);
            if (!empty($exData) && is_array($exData)) {

                $exData += [
                    'message' => '',
                    'code'    => 0,
                ];

                $ex = new static($exData['message'], $exData['code'], $prevEx);

                if (isset($exData['line'])) {
                    $ex->line = $exData['line'];
                }

                if (isset($exData['file'])) {
                    $ex->file = $exData['file'];
                }

                if (!empty($exData['trace']) && is_array($exData['trace'])) {
                    $ex->remoteTrace = $exData['trace'];
                }

                return $ex;
            }
        }
        return null;
    }
}