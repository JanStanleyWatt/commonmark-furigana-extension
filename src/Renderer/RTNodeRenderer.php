<?php

/**
 * Copyright 2023 Jan stanray watt

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 *  http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace JSW\Hurigana\Renderer;

use JSW\Hurigana\Node\RTNode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use League\CommonMark\Xml\XmlNodeRendererInterface;

final class RTNodeRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param RTNode $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        RTNode::assertInstanceOf($node);

        $attrs = $node->data->get('attributes');

        return new HtmlElement('rt', $attrs, Xml::escape($node->getLiteral()));
    }

    public function getXmlTagName(Node $node): string
    {
        return 'rt';
    }

    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}
