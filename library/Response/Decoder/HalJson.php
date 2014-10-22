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
    /**
     * @var array|null
     */
    protected $originalData = null;

    /**
     * @return array|null
     */
    public function getRawDecodedData()
    {
        return $this->originalData;
    }

    public function decode(Response $response)
    {
        $bodyResponse = $response->getBody();
        $data = Json::decode($bodyResponse, Json::TYPE_ARRAY);
        $this->originalData = $data;

        $headers = $response->getHeaders();
        if  ($headers->has('Content-Type') && $headers->get('Content-Type')->getFieldValue() == 'application/hal+json') {
            return $this->decodeHal($data);
        }
        //else
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
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