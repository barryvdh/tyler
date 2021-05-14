<?php
declare(strict_types=1);
namespace Bss\FixAdminActionLog\Model;

/**
 * Class Log
 * Fix bug ko save duoc cron
 */
class Log extends \Bss\AdminActionLog\Model\Log
{
    /**
     * @inheritDoc
     */
    public function doRestoreToDefaultValue($model)
    {
        $fieldId = $model->getField();

        if (!$model->getFieldConfig()) {
            return $model->getValue();
        }

        $modelValue = $model->getData();
        $configPaths = explode('/', $model->getFieldConfig()['path']);
        unset($configPaths[0]);
        foreach ($configPaths as $configPath) {
            $modelValue = $modelValue['groups'][$configPath];
        }
        $modelValue = $modelValue['fields'][$fieldId];
        if (isset($modelValue['inherit']) &&
            $modelValue['inherit'] == 1 &&
            $this->helper->getDefaultValue($model->getPath())) {
            return $this->helper->getDefaultValue($model->getPath());
        }
        return $model->getValue();
    }
}
