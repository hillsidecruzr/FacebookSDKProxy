<?php


namespace FacebookPhotoDownload;
use Facebook\Facebook;

include __DIR__ . '/vendor/autoload.php';

class FacebookPhotoDownloadProxy
{
    const DEFAULT_REQUEST_LIMIT = 10;
    const DEFAULT_GRAPH_VERSION = 'v2.5';

    private $fb;
    private $appId;
    private $appSecret;
    private $accessToken;

    /**
     * FacebookPhotoDownloadProxy constructor.
     * @param string $secretsFile Location of file with app_id, app_secret, and optionally a user access token.
     */
    public function __construct($secretsFile = '')
    {
        list($appId, $appSecret, $accessToken) = $this->parseSecretsFile($secretsFile);
        $this->setAppId($appId);
        $this->setAppSecret($appSecret);

        if (empty($accessToken)) {
            $accessToken = $this->retrieveUserAccessToken();
        }

        $this->setAccessToken($accessToken);
    }

    /**
     * @return string
     * @todo Implement so that this does not need to be generated manually through the graph api explorer.
     */
    protected function retrieveUserAccessToken()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     */
    private function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @param string $appSecret
     */
    private function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    private function parseSecretsFile($fileName)
    {
        if (!file_exists($fileName)) {
            return ['', '', ''];
        }

        $string = file_get_contents($fileName);
        $secrets = json_decode($string, true);

        // Return numerically indexed due to the use of 'list'.
        return [
            0 => isset($secrets['app_id']) ? $secrets['app_id'] : '',
            1 => isset($secrets['app_secret']) ? $secrets['app_secret'] : '',
            2 => isset($secrets['access_token']) ? $secrets['access_token'] : ''
        ];
    }

    /**
     * @return object Facebook
     */
    public function getFbInstance()
    {
        if (isset($this->fbInstance)) {
            return $this->fbInstance;
        }

        $this->fb = new Facebook([
            'app_id'                  => $this->getAppId(),
            'app_secret'              => $this->getAppSecret(),
            'default_graph_version'   => $this::DEFAULT_GRAPH_VERSION
        ]);

        $this->fb->setDefaultAccessToken($this->getAccessToken());

        return $this->fb;
    }

    /**
     * Sends an API request to fetch the specified number of images from an account.
     *
     * @param int $limit
     * @return mixed
     */
    public function fetchImages($limit)
    {
        if (!isset($this->fb)) {
            $this->fb = $this->getFbInstance();
        }

        if (is_numeric($limit)) {
            $limit = (int) $limit;
        } else {
            $limit = $this::DEFAULT_REQUEST_LIMIT;
        }

        try {
          $response = $this->fb->get("/me/photos?fields=id,source,name&amp;limit={$limit}");
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        return $response->getDecodedBody();
    }
}

$FbProxy = new FacebookPhotoDownloadProxy(__DIR__ . '/secrets.json');
var_dump($FbProxy->fetchImages(10));

?>
