<?php
declare(strict_types = 1);

namespace SqlException\Base\Model;

/**
 * Model to have the state of a process as lock flag inside the database to prevent multiple executions
 */
class ProcessStateFlag extends \Magento\Framework\DataObject
{
    const STATE_RUNNING = 1;
    const STATE_STOPPED = 0;

    /**
     * current process code
     *
     * @var string
     */
    protected $code;

    /**
     * @var \Magento\Framework\FlagManager
     */
    protected $flagManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * Flag constructor.
     *
     * @param string $code
     * @param array $data
     */
    public function __construct(
        string $code,
        \Magento\Framework\FlagManager $flagManager,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = []
    ) {
        $this->code = $code;
        $this->flagManager = $flagManager;
        $this->serializer = $serializer;
        parent::__construct($data);
    }

    /**
     * release lock on class destruction
     */
    public function __destruct()
    {
        if ($this->isRunning() && $this->getData('pid') == $this->getProcessId()) {
            $this->stop();
        }
    }

    /**
     * load flag data
     */
    public function load(): self
    {
        if (false === $this->isEmpty()) {
            return $this;
        }
        $data = $this->flagManager->getFlagData($this->getProcessCode());
        if (false !== $data && null !== $data) {
            $this->setData($this->serializer->unserialize($data));
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function save(): self
    {
        $this->setData('last_action', \time());
        $this->flagManager->saveFlag($this->getProcessCode(), $this->serializer->serialize($this->getData()));
        return $this;
    }

    /**
     * get current process id
     *
     * @return int
     */
    protected function getProcessId()
    {
        return getmypid();
    }

    /**
     * @return string
     */
    protected function getProcessCode(): string
    {
        return $this->code;
    }

    /**
     * set process lock
     *
     * @return $this
     */
    public function start(): self
    {
        $this->load();
        $this->setPid($this->getProcessId());
        $this->setState(self::STATE_RUNNING);
        return $this->save();
    }

    /**
     * stop process lock
     *
     * @return $this
     */
    public function stop(): self
    {
        $this->load();
        $this->unsetData('pid');
        $this->setData('state', self::STATE_STOPPED);

        return $this->save();
    }

    /**
     * check process is running with different process id
     */
    public function isRunning(): bool
    {
        $this->load();
        return $this->hasData('state') && $this->getData('state') !== self::STATE_STOPPED;
    }
}
