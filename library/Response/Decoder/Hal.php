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
use Matryoshka\Model\Wrapper\Rest\Exception;
use ZendXml\Security;

class Hal implements DecoderInterface
{

    protected $lastPayload;

    /**
     * @return array|null
     */
    public function getLastPayload()
    {
        return $this->lastPayload;
    }

    public function decode(Response $response)
    {
        $headers = $response->getHeaders();
        if (!$headers->has('Content-Type')) {
            throw new Exception\InvalidResponseException('Content-Type missing');
        }
        /* @var $contentType \Zend\Http\Header\ContentType */
        $contentType = $headers->get('Content-Type');
        switch (true) {
            case $contentType->match('*/json'):
                $payload = Json::decode($response->getBody(), Json::TYPE_ARRAY);
                break;
                //TODO: xml
//             case $contentType->match('*/xml'):
//                 $xml = Security::scan($response->getBody());
//                 $payload = Json::decode(Json::encode((array) $xml), Json::TYPE_ARRAY);
//                 break;

            default:
                throw new Exception\InvalidFormatException(sprintf(
                    'The "%s" media type is invalid or not supported',
                    $contentType->getMediaType()
                ));
                break;
        }

        $this->lastPayload = $payload;

        if  ($contentType->match('application/hal+*')) {
            return $this->decodeHal($payload);
        }
        //else
        return $payload;
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