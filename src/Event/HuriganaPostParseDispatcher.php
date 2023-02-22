<?php

/**
 * Copyright 2023 Jan Stanley Watt

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

namespace JSW\Hurigana\Event;

use JSW\Hurigana\Node\RubyParentheses;
use JSW\Hurigana\Node\RubyText;
use League\CommonMark\Event\DocumentParsedEvent;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

final class HuriganaPostParseDispatcher implements ConfigurationAwareInterface
{
    private ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function useRPTag(DocumentParsedEvent $event)
    {
        if ($this->config->get('hurigana/use_rp_tag')) {
            $document = $event->getDocument();
            foreach ($document->iterator() as $node) {
                if ($node instanceof RubyText) {
                    $node->insertBefore(new RubyParentheses('('));
                    $node->insertAfter(new RubyParentheses(')'));
                }
            }
        }
    }
}
