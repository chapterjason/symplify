<?php

declare(strict_types=1);

namespace Symplify\SimplePhpDocParser\Tests\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;
use Symplify\SimplePhpDocParser\Tests\HttpKernel\SimplePhpDocParserKernel;
use Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey;

final class ParentConnectingPhpDocNodeVisitorTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    protected function setUp(): void
    {
        $this->bootKernel(SimplePhpDocParserKernel::class);

        $this->phpDocNodeTraverser = $this->getService(PhpDocNodeTraverser::class);

        /** @var ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor */
        $parentConnectingPhpDocNodeVisitor = $this->getService(ParentConnectingPhpDocNodeVisitor::class);
        $this->phpDocNodeTraverser->addPhpDocNodeVisitor($parentConnectingPhpDocNodeVisitor);
    }

    public function testChildNode(): void
    {
        $phpDocNode = $this->createPhpDocNode();
        $this->phpDocNodeTraverser->traverse($phpDocNode);

        $phpDocChildNode = $phpDocNode->children[0];
        $this->assertInstanceOf(PhpDocTagNode::class, $phpDocChildNode);

        $childParent = $phpDocChildNode->getAttribute(PhpDocAttributeKey::PARENT);
        $this->assertSame($phpDocNode, $childParent);
    }

    public function testTypeNode(): void
    {
        $phpDocNode = $this->createPhpDocNode();
        $this->phpDocNodeTraverser->traverse($phpDocNode);

        $phpDocChildNode = $phpDocNode->children[0];

        /** @var PhpDocTagNode $phpDocChildNode */
        $returnTagValueNode = $phpDocChildNode->value;

        $this->assertInstanceOf(ReturnTagValueNode::class, $returnTagValueNode);

        /** @var ReturnTagValueNode $returnTagValueNode */
        $returnParent = $returnTagValueNode->getAttribute(PhpDocAttributeKey::PARENT);
        $this->assertSame($phpDocChildNode, $returnParent);

        $returnTypeParent = $returnTagValueNode->type->getAttribute(PhpDocAttributeKey::PARENT);
        $this->assertSame($returnTagValueNode, $returnTypeParent);
    }

    private function createPhpDocNode(): PhpDocNode
    {
        $returnTagValueNode = new ReturnTagValueNode(new IdentifierTypeNode('string'), '');

        return new PhpDocNode([
            new PhpDocTagNode('@return', $returnTagValueNode),
            new PhpDocTextNode('some text'),
        ]);
    }
}
