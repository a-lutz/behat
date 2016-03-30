<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;


/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{

    /**
     * Checks that option from select with specified id|name|label|value is selected.
     *
     * @Then /^the "(?P<option>(?:[^"]|\\")*)" option from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected/
     * @Then /^the option "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     * @Then /^"(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" (?:is|should be) selected$/
     * @param string $option : option text
     * @param string $select : select field id, name, label or value
     * @throws ExpectationException
     * @throws ElementNotFoundException
     */
    public function theOptionFromShouldBeSelected($option, $select)
    {
        $selectField = $this->getSession()->getPage()->findField($select);
        if (null === $selectField) {
            throw new ElementNotFoundException($this->getSession(), 'select field', 'id|name|label|value', $select);
        }

        $optionField = $selectField->find('named', array(
            'option',
            "\"{$option}\"",
        ));

        if (null === $optionField) {
            throw new ElementNotFoundException($this->getSession(), 'select option field', 'id|name|label|value', $option);
        }

        if (!$optionField->isSelected()) {
            throw new ExpectationException('Select option field with value|text "'.$option.'" is not selected in the select "'.$select.'"', $this->getSession());
        }
    }

    /**
     * @Then the :select select should contain :listOfOptions
     * @throws ExpectationException
     * @param string $select : select field id, name, label or value
     * @param string $listOfOptions : list of option texts to search, separated by |
     */
    public function theSelectShouldContain($select, $listOfOptions)
    {
        $optionTextes = $this->getSelectOptionsTextes($select);

        $notFoundOptions = array_diff(explode('|', $listOfOptions), $optionTextes);
        if (count($notFoundOptions) > 0) {
            throw new ExpectationException('Options not found in the select "'.$select.'": '.implode(', ', $notFoundOptions), $this->getSession());
        }
    }

    /**
     * @Then the :select select should contain exactly :listOfOptions
     * @throws ExpectationException
     * @param string $select : select field id, name, label or value
     * @param string $listOfOptions : list of option texts to search, separated by |
     */
    public function theSelectShouldContainExactly($select, $listOfOptions)
    {
        $optionTextes = $this->getSelectOptionsTextes($select);
        $wantedTextes = explode('|', $listOfOptions);

        $notFoundOptions = array_diff($wantedTextes, $optionTextes);
        $unwantedOptions = array_diff($optionTextes, $wantedTextes);
        if (count($notFoundOptions) > 0) {
            throw new ExpectationException('Options not found in the select "'.$select.'": '.implode(', ', $notFoundOptions), $this->getSession());
        }
        if (count($unwantedOptions) > 0) {
            throw new ExpectationException('Unwanted options found in the select "'.$select.'": '.implode(', ', $unwantedOptions), $this->getSession());
        }
        if (count($optionTextes) != count($wantedTextes)) {
            throw new ExpectationException('Duplicated options found in the select "'.$select.'": '.implode(', ', $optionTextes), $this->getSession());
        }
    }

    /**
     * @Then the :select select should not contain :listOfOptions
     * @throws ExpectationException
     * @param string $select : select field id, name, label or value
     * @param string $listOfOptions : list of option texts to search, separated by |
     */
    public function theSelectShouldNotContain($select, $listOfOptions)
    {
        $optionTextes = $this->getSelectOptionsTextes($select);

        $foundOptions = array_intersect(explode('|', $listOfOptions), $optionTextes);
        if (count($foundOptions) > 0) {
            throw new ExpectationException('Not wanted options were found in the select "'.$select.'": '.implode(', ', $foundOptions), $this->getSession());
        }
    }

    /**
     * @Then the :select select should be empty
     * @throws ExpectationException
     * @param string $select : select field id, name, label or value
     */
    public function theSelectShouldBeEmpty($select)
    {
        $optionTextes = $this->getSelectOptionsTextes($select);

        if (count($optionTextes) > 0) {
            throw new ExpectationException('Select is not empty ("'.$select.'")', $this->getSession());
        }
    }


    /**
     * @Then the :field field should be disabled
     * @param string $field : field id, label, name or value
     * @throws ElementNotFoundException
     * @throws ExpectationException
     */
    public function theFieldShouldBeDisabled($field)
    {
        $elementField = $this->getSession()->getPage()->findField($field);
        if (null === $elementField) {
            throw new ElementNotFoundException($this->getSession(), 'field', 'id|name|label|value', $field);
        }

        if (!$elementField->hasAttribute('disabled')) {
            throw new ExpectationException('Field is not disabled ("'.$field.'") !', $this->getSession());
        }
    }

    /**
     * @Then the :field field should not be disabled
     * @param string $field : field id, label, name or value
     * @throws ElementNotFoundException
     * @throws ExpectationException
     */
    public function theFieldShouldNotBeDisabled($field)
    {
        $elementField = $this->getSession()->getPage()->findField($field);
        if (null === $elementField) {
            throw new ElementNotFoundException($this->getSession(), 'field', 'id|name|label|value', $field);
        }

        if ($elementField->hasAttribute('disabled')) {
            throw new ExpectationException('Field is disabled ("'.$field.'") !', $this->getSession());
        }
    }

    /**
     * @Then the :label label should be styled as error
     * @param string $label : fragment of a label text
     * @throws ElementNotFoundException
     * @throws ExpectationException
     */
    public function theLabelShouldBeStyledAsError($label)
    {
        $labelElement = $this->getSession()->getPage()->find('xpath', '//label[contains(., "'.$label.'")]');
        if (null === $labelElement) {
            throw new ElementNotFoundException($this->getSession(), 'label', 'name', $label);
        }

        if (!$labelElement->hasClass('alerte')) {
            throw new ExpectationException('Label is not shown as alert ("'.$label.'") !', $this->getSession());
        }
    }

    /**
     * @Then the :label label should not be styled as error
     * @param string $label : fragment of a label text
     * @throws ElementNotFoundException
     * @throws ExpectationException
     */
    public function theLabelShouldNotBeStyledAsError($label)
    {
        $labelElement = $this->getSession()->getPage()->find('xpath', '//label[contains(., "'.$label.'")]');
        if (null === $labelElement) {
            throw new ElementNotFoundException($this->getSession(), 'label', 'name', $label);
        }

        if ($labelElement->hasClass('alerte')) {
            throw new ExpectationException('Label is shown as alert ("'.$label.'") !', $this->getSession());
        }
    }


    /**
     * Builds an array with all options texts of a given select
     *
     * @param $select: select field (by id, name, label or value)
     * @return array of options texts
     * @throws ElementNotFoundException
     */
    private function getSelectOptionsTextes($select)
    {
        $selectField = $this->getSession()->getPage()->findField($select);
        if (null === $selectField) {
            throw new ElementNotFoundException($this->getSession(), 'select field', 'id|name|label|value', $select);
        }

        $optionTextes = array();
        $optionElements = $selectField->findAll('css', 'option');
        foreach ($optionElements as $optionElement) {
            if (!empty($optionElement->getValue())) {
                $optionTextes[] = $optionElement->getText();
            }
        }

        return $optionTextes;
    }


}
