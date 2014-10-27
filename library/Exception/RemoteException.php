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
    /**
     * @var array
     */
    protected $remoteTrace = [];

    /**
     * @var array
     */
    protected $details = [];

    /**
     * @return array
     */
    public function getRemoteTrace()
    {
        return $this->remoteTrace;
    }

    /**
     * @return array
     */
    public function getAdditionalDetails()
    {
        return $this->details;
    }

    public static function factory(array $stack)
    {
        if (count($stack) > 0) {
            $exData = array_shift($stack);
            $prevEx = static::factory($stack);
            if (isset($exData) && is_array($exData)) {

                $exData += ['message' => '', 'code'    => 0];

                $ex = new static($exData['message'], $exData['code'], $prevEx);
                unset($exData['message'], $exData['code']);

                if (isset($exData['line'])) {
                    $ex->line = $exData['line'];
                    unset($exData['line']);
                }

                if (isset($exData['file'])) {
                    $ex->file = $exData['file'];
                    unset($exData['file']);
                }

                if (!empty($exData['trace']) && is_array($exData['trace'])) {
                    $ex->remoteTrace = $exData['trace'];
                }

                if (isset($exData['trace'])) {
                    unset($exData['trace']);
                }

                $ex->details = $exData;

                return $ex;
            }
        }
        return null;
    }
}