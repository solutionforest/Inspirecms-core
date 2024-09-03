<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use SolutionForest\InspireCms\DataTypes\ContentStatusOption;

trait Publishable
{
    /**
     * The state representing the publishable state.
     *
     * @var string
     */
    protected string $publishableState = 'draft';

    /** @inheritDoc */
    public function setPublishableState(string $state): void
    {
        $this->publishableState = $state;
    }

    /** @inheritDoc */
    public function getPublishableState(): string
    {
        return $this->publishableState;
    }

    /** @inheritDoc */
    public function resetPublishableState(): void
    {
        $this->publishableState = 'draft';
    }

    public function save(array $options = [])
    {
        $status = inspirecms_content_statuses()->getOption($this->getPublishableState());

        $result = $this->performPublishableAction($options, $status);

        event(new \SolutionForest\InspireCms\Events\ChangeContentStatus($result, $status));

        $this->resetPublishableState();

        return $result;
    }

    public function draft(array $data)
    {
        $this->setPublishableState('draft');

        return $this->save($data);
    }

    public function publish(array $data)
    {
        $this->setPublishableState('publish');
        
        return $this->save($data);
    }

    public function unpublish()
    {
        $this->setPublishableState('unpublish');
        
        return $this->save([]);
    }

    public function setPrivateUse(array $data)
    {
        $this->setPublishableState('private');
        
        return $this->save($data);
    }

    protected function performPublishableAction(array $data, ?ContentStatusOption $option)
    {
        if ($option) {
            $this->status = $option->value;
        } else {
            $this->status = inspirecms_content_statuses()->getDefaultValue();
        }
        
        return parent::save($data);
    }
}
