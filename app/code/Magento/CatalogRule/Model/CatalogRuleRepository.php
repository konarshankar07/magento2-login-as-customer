<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model;

use Magento\CatalogRule\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class CatalogRuleRepository implements \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface
{
    /**
     * @var ResourceModel\Rule
     */
    protected $ruleResource;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @param ResourceModel\Rule $ruleResource
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\Rule $ruleResource,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\RuleInterface $rule)
    {
        if ($rule->getRuleId()) {
            $rule = $this->get($rule->getRuleId())->addData($rule->getData());
        }

        try {
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save rule %1', $rule->getRuleId()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        if (!isset($this->rules[$ruleId])) {
            /** @var \Magento\CatalogRule\Model\RuleFactory $rule */
            $rule = $this->ruleFactory->create();

            /* TODO: change to resource model after entity manager will be fixed */
            $rule->load($ruleId);
            if (!$rule->getRuleId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $ruleId));
            }
            $this->rules[$ruleId] = $rule;
        }
        return $this->rules[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove rule %1', $rule->getRuleId()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $model = $this->get($ruleId);
        $this->delete($model);
        return true;
    }
}
