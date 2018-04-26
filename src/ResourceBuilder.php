<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\ResourceBuilder\BaseResourceBuilder;

/**
 * ResourceBuilder class.
 *
 * This class is responsible for turning responses from the API into instances of PHP classes.
 *
 * A ResourceBuilder will only work for one space,
 * when working with multiple spaces multiple instances have to be used.
 */
class ResourceBuilder extends BaseResourceBuilder
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var InstanceRepository
     */
    private $instanceRepository;

    /**
     * @var string[]
     */
    private static $availableTypes = [
        'Asset',
        'ContentType',
        'DeletedAsset',
        'DeletedContentType',
        'DeletedEntry',
        'Environment',
        'Entry',
        'Locale',
        'Space',
    ];

    /**
     * ResourceBuilder constructor.
     *
     * @param Client             $client
     * @param InstanceRepository $instanceRepository
     */
    public function __construct(Client $client, InstanceRepository $instanceRepository)
    {
        $this->client = $client;
        $this->instanceRepository = $instanceRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function getMapperNamespace()
    {
        return __NAMESPACE__.'\\Mapper';
    }

    /**
     * {@inheritdoc}
     */
    protected function createMapper($fqcn)
    {
        return new $fqcn($this, $this->client);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSystemType(array $data)
    {
        if ('Array' === $data['sys']['type']) {
            return 'ResourceArray';
        }

        if (\in_array($data['sys']['type'], self::$availableTypes, true)) {
            return $data['sys']['type'];
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unexpected system type "%s" while trying to build a resource.',
            $data['sys']['type']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $data, ResourceInterface $resource = null)
    {
        $type = $data['sys']['type'];

        if ('Array' !== $type && $this->instanceRepository->has($type, $data['sys']['id'])) {
            return $this->instanceRepository->get($type, $data['sys']['id']);
        }

        if ('Array' === $type) {
            $ids = $this->buildContentTypeCollection($data);
            if ($ids) {
                $query = (new Query())
                    ->where('sys.id', \implode(',', $ids), 'in');
                $this->client->getContentTypes($query);
            }

            $this->buildIncludes($data);
        }

        $resource = parent::build($data, $resource);

        if ($resource instanceof ResourceInterface) {
            $this->instanceRepository->set($resource);
        }

        return $resource;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function buildIncludes(array $data)
    {
        $items = \array_merge(
            isset($data['includes']['Entry']) ? $data['includes']['Entry'] : [],
            isset($data['includes']['Asset']) ? $data['includes']['Asset'] : []
        );
        foreach ($items as $item) {
            $this->build($item);
        }
    }

    /**
     * We extract content types that need to be fetched from a response array.
     * This way we can use a collective query rather than making separate queries
     * for every content type.
     *
     * @param array $data
     *
     * @return string[]
     */
    private function buildContentTypeCollection(array $data)
    {
        $items = \array_merge(
            $data['items'],
            isset($data['includes']['Entry']) ? $data['includes']['Entry'] : []
        );

        $ids = \array_map(function ($item) {
            return 'Entry' === $item['sys']['type']
                ? $item['sys']['contentType']['sys']['id']
                : null;
        }, $items);

        return \array_filter(\array_unique($ids), function ($id) {
            return $id && !$this->instanceRepository->has('ContentType', $id);
        });
    }
}
