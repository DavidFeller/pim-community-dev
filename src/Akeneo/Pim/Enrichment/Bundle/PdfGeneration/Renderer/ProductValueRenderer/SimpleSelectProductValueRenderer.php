<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Twig\Environment;

class SimpleSelectProductValueRenderer implements ProductValueRenderer
{
    private IdentifiableObjectRepositoryInterface $attributeOptionRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    public function render(Environment $environment, AttributeInterface $attribute, ?ValueInterface $value, string $localeCode): ?string
    {
        if (!$value instanceof OptionValue) {
            return null;
        }

        $optionCode = $value->getData();

        return $this->getOptionLabel($attribute, $optionCode, $localeCode);
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return $attributeType === AttributeTypes::OPTION_SIMPLE_SELECT;
    }

    private function getOptionLabel(AttributeInterface $attribute, string $optionCode, string $localeCode): string
    {
        $option = $this->attributeOptionRepository->findOneByIdentifier($attribute->getCode() . '.' . $optionCode);

        if (null === $option) {
            return sprintf('[%s]', $optionCode);
        }

        $option->setLocale($localeCode);
        $translation = $option->getTranslation();

        return null !== $translation->getValue() ? $translation->getValue() : sprintf('[%s]', $option->getCode());
    }
}
