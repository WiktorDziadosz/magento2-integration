<?php

namespace Synerise\Integration\Model\Config\Backend\Api;

class Key extends \Magento\Config\Model\Config\Backend\Encrypted
{
    /**
     * @var \Zend\Validator\Uuid
     */
    protected $uuidValidator;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Synerise\Integration\Helper\Api
     */
    private $apiHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Synerise\Integration\Helper\Api
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Psr\Log\LoggerInterface $logger,
        \Zend\Validator\Uuid $uuidValidator,
        \Synerise\Integration\Helper\Api $apiHelper,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->uuidValidator = $uuidValidator;
        $this->apiHelper = $apiHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $encryptor, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $value = trim($this->getValue());

        // don't save value, if an obscured value was received. This indicates that data was not changed.
        if (!preg_match('/^\*+$/', $value) && !empty($value)) {
            if ($value != '' && !$this->uuidValidator->isValid($value)) {
                throw new \Magento\Framework\Exception\ValidatorException(__('Invalid api key format'));
            }

            $business_profile_authentication_request = new \Synerise\ApiClient\Model\BusinessProfileAuthenticationRequest([
                'api_key' => $value
            ]);

            try {
                $this->apiHelper->getAuthApiInstance()->profileLoginUsingPOST($business_profile_authentication_request);
            } catch (\Synerise\ApiClient\ApiException $e) {
                if ($e->getCode() === 401) {
                    throw new \Magento\Framework\Exception\ValidatorException(
                        __('Test request failed. Please make sure this a valid, profile scoped api key and try again.')
                    );
                } else {
                    $this->logger->error('Synerise Api request failed', ['exception' => $e]);
                    throw $e;
                }
            }

            $this->setValue($value);
        }

        parent::beforeSave();
    }
}