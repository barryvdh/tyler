<?php
declare(strict_types=1);

namespace Bss\BrandLandingPage\Plugin\Controller\Account;

/**
 * Class CreatePost
 * No redirect to b2b registration form or login page
 */
class CreatePost
{
    /**
     * No redirect to b2b registration form or login page
     *
     * @param \Bss\B2bRegistration\Controller\Account\CreatePost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $redirectResult
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(
        \Bss\B2bRegistration\Controller\Account\CreatePost $subject,
        $redirectResult
    ) {
        if ($url = $subject->getRequest()->getPostValue('signup_sources_url')) {
            $redirectResult->setUrl($url);

            return $redirectResult;
        }

        return $redirectResult;
    }
}
