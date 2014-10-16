<?php
/**
 * REST matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/rest-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Rest\Response\Decoder;

use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Stdlib\ArrayUtils;
class HalJson implements DecoderInterface
{

//     /**
//      * @var int 0/1
//      */
//     protected $returnType = Json::TYPE_ARRAY;

//     /**
//      * @return int
//      */
//     public function getReturnType()
//     {
//         return $this->returnType;
//     }

//     /**
//      * @param $returnType
//      * @return $this
//      */
//     public function setReturnType($returnType)
//     {
//         $this->returnType = $returnType;
//         return $this;
//     }

    public function decode(Response $response)
    {
        $bodyResponse = $response->getBody();
        $data = Json::decode($bodyResponse, Json::TYPE_ARRAY);

        if  ($response->getMetadata('Content-Type') == 'application/hal+json') {
            return $this->decodeHal($data);
        }
        //else
        return $data;
    }

    protected function decodeHal(array $data)
    {
        if (array_key_exists('_links', $data)) {
            unset($data['_links']);
        }

        if (array_key_exists('_embedded', $data)) {
            $embedded = $data['_embedded'];
            if (ArrayUtils::hasStringKeys($embedded)) {
                $resourceNode = array_shift($embedded);
                if (ArrayUtils::isList($resourceNode)) {
                    $data = [];
                    foreach ($resourceNode as $resource) {
                        $data[] = $this->decodeHal($resource);
                    }
                }
            }
        }

        return $data;
    }


}