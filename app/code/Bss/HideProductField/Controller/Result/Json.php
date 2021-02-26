<?php
declare(strict_types=1);
namespace Bss\HideProductField\Controller\Result;

/**
 * Class Json
 */
class Json extends \Magento\Framework\Controller\Result\Json
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Json constructor.
     *
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($translateInline, $serializer);
    }

    /**
     * @return array|string
     */
    public function getData()
    {
        try {
            return $this->serializer->unserialize($this->json);
        } catch (\Exception $e) {
            return [];
        }
    }
}
