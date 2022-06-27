<?php
/**
 * @package Mac_AsyncLogger
 * @author Russell Albin <russell@russellalbin.com>
 */

declare(strict_types=1);

namespace Mac\AsyncLogger\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class used to reduce the likelihood for sensitive data is added to a log
 */
class Cleanser
{
    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     */
      /* phpcs:disable */
    public function __construct(
        protected ScopeConfigInterface $config,
        protected StoreManagerInterface $storeManager,
        protected Json $json
    ) {
    }
  /* phpcs:enable */
    /**
     * Value to be substituted instead of sensitve data
     *
     * @var string
     */
    public $replaceValue = '******';

    /**
     * Search object for sensitive data and replace it if found
     *
     * @param mixed $input
     * @return mixed
     */
    public function checkForSensitiveData($input = null)
    {
        /**
         * @TODO I am not sure I like how this is handled. Investigate options
         *       Although this works, I don't like the try/catch method used to determine input type
         */
        try {
            if (null === $input) {
                return $input;
            }

            if (is_string($input)) {
                return $input;
            }

            if ($input instanceof \Exception) {
                $newinput = [
                    'message' => $input->getMessage(),
                    'trace'   => $input->getTrace()
                ];
                $input = $newinput;
            }
            if (is_object($input)) {
                $input = $this->processObject($input);
            }
            if (is_array($input)) {
                return $this->checkArrayForSensitiveData($input);
            }
            if (!empty($input) && ($json = $this->json->unserialize($input)) && is_array($json)) {
                $json = $this->checkForSensitiveData($json);
                return $this->json->serialize($json);
            }
        } catch (\Exception $e) {
            if (!is_string($input)) {
                $input = (string)$input;
            }
        } catch (\InvalidArgumentException $invalid) {
            if (!is_string($input)) {
                $input = (string)$input;
            }
        }
        return $input;
    }

    /**
     * Convert object to array
     *
     * @param \stdClass $input
     * @return array
     */
    private function processObject($input)
    {
        if (method_exists($input, 'toArray')) {
            return $input->toArray();
        }
        if (method_exists($input, 'getData')) {
            return $input->getData();
        }
        return get_object_vars($input);
    }

    /**
     * Recursively search array for sensitive data and replace it if found
     *
     * @param $input
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkArrayForSensitiveData($input)
    {
        $store = $this->storeManager->getStore();
        $privateKeysString = $this->config->getValue(
            'logger/general/privatekeys',
            ScopeInterface::SCOPE_WEBSITE,
            $store->getWebsiteId()
        );
        $privateKeys = explode(',', $privateKeysString);
        /**
         * @TODO Although this works, I do not like how this is structured.
         *       Investigate options to remove nested foreach loop
         */
        foreach ($input as $key => $value) {
            if (in_array($key, $privateKeys)) {
                $input[$key] = $this->replaceValue;
                continue;
            }
            if (is_object($value)) {
                $input[$key] = $this->checkForSensitiveData($value);
            }
            if (!is_array($value)) {
                continue;
            }
            foreach ($value as $k => $v) {
                if (in_array($k, $privateKeys) && (!is_array($v) && !is_object($v))) {
                    $input[$key][$k] =$this->replaceValue;
                } else {
                    $input[$key][$k] = $this->checkForSensitiveData($v);
                }

            }
        }
        return $input;
    }
}
