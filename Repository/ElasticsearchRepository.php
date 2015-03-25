<?php


namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzedFile;
use Dontdrinkandroot\GitkiBundle\Model\SearchResult;
use Dontdrinkandroot\Path\FilePath;
use Elasticsearch\Client;

class ElasticsearchRepository implements ElasticsearchRepositoryInterface
{

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $index;

    /**
     * @param string $host
     * @param int    $port
     * @param string $index
     */
    public function __construct($host, $port, $index)
    {
        $this->host = $host;
        $this->port = $port;
        $this->index = strtolower($index);

        $params = [];
        $params['hosts'] = [$host . ':' . $port];
        $this->client = new Client($params);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        /* TODO: Delete without id is not supported by current elasticsearch PHP API
        $params = array(
            'index' => $this->index,
            'type' => self::MARKDOWN_DOCUMENT_TYPE,
        );

        return $this->client->delete($params);*/

        $params = [
            'index' => $this->index,
            'fields' => array('_id')
        ];

        $params['body']['query']['match_all'] = [];
        $params['body']['size'] = 10000;

        $result = $this->client->search($params);

        foreach ($result['hits']['hits'] as $hit) {
            $params = [
                'id'   => $hit['_id'],
                'index' => $this->index,
                'type' => $hit['_type']
            ];

            $this->client->delete($params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search($searchString)
    {
        $params = [
            'index'  => $this->index,
            'fields' => ['title']
        ];

        $searchStringParts = explode(' ', $searchString);
        foreach ($searchStringParts as $searchStringPart) {
            $params['body']['query']['bool']['should'][]['wildcard']['content'] = $searchStringPart . '*';
        }

        $result = $this->client->search($params);
        $numHits = $result['hits']['total'];
        if ($numHits == 0) {
            return [];
        }

        $searchResults = [];
        foreach ($result['hits']['hits'] as $hit) {
            $searchResult = new SearchResult();
            $searchResult->setPath(FilePath::parse($hit['_id']));
            $searchResult->setScore($hit['_score']);
            if (isset($hit['fields'])) {
                if (isset($hit['fields']['title'][0])) {
                    $searchResult->setTitle($hit['fields']['title'][0]);
                }
            }
            $searchResults[] = $searchResult;
        }

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function indexFile(FilePath $path, AnalyzedFile $analyzedFile)
    {

        $params = [
            'id'   => $path->toAbsoluteString(),
            'index' => $this->index,
            'type' => $path->getExtension(),
            'body' => [
                'title'        => $analyzedFile->getTitle(),
                'content'      => $analyzedFile->getContent(),
                'linked_paths' => $analyzedFile->getLinkedPaths()
            ]
        ];

        return $this->client->index($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $path)
    {
        $params = [
            'id'    => $path->toAbsoluteString(),
            'index' => $this->index,
            'type'  => $path->getExtension()
        ];

        return $this->client->delete($params);
    }

    /**
     * @param FilePath $path
     *
     * @return string
     */
    public function getTitle(FilePath $path)
    {
        $params = [
            'index'           => $this->index,
            'id'              => $path->toAbsoluteString(),
            '_source_include' => ['title']
        ];
        $result = $this->client->get($params);
        if (null === $result) {
            return null;
        }

        return $result['_source']['title'];
    }
}
