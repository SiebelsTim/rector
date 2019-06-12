<?php declare(strict_types=1);

namespace Rector\Shopware\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
/**
 * @see https://github.com/shopware/shopware/blob/5.6/UPGRADE-5.6.md
 */
final class ShopRegistrationServiceRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace $shop->registerResources() with ShopRegistrationService', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $shop = new \Shopware\Models\Shop\Shop();
        $shop->registerResources();
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $shop = new \Shopware\Models\Shop\Shop();
        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop($shop);
    }
}
CODE_SAMPLE

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'registerResources')) {
            return null;
        }

        if (! $this->isType($node, 'Shopware\Models\Shop\Shop')) {
            return null;
        }

        $shopwareFunction = new FuncCall(new Node\Name('Shopware'));
        $containerCall = new MethodCall($shopwareFunction, new Node\Identifier('Container'));
        $methodCall = new MethodCall($containerCall, new Node\Identifier('get'), [new Node\Arg(new Node\Scalar\String_('shopware.components.shop_registration_service'))]);

        return new MethodCall($methodCall, new Node\Identifier('registerShop'), [new Node\Arg($node->var)]);
    }
}
