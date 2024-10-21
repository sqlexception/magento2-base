<?php declare(strict_types=1);

namespace SqlException\Base\Model\Config\Source;

class LogLevel implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Returns the list of log levels as options for Magento's configuration.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'disabled', 'label' => __('Disabled')],
            ['value' => 'debug', 'label' => __('Debug')],
            ['value' => 'info', 'label' => __('Info')],
            ['value' => 'notice', 'label' => __('Notice')],
            ['value' => 'warning', 'label' => __('Warning')],
            ['value' => 'error', 'label' => __('Error')],
            ['value' => 'critical', 'label' => __('Critical')],
            ['value' => 'alert', 'label' => __('Alert')],
            ['value' => 'emergency', 'label' => __('Emergency')],
        ];
    }
}
