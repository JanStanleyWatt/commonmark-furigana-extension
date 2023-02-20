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

namespace JSW\Hurigana;

use JSW\Hurigana\Delimiter\HuriganaDelimiterProcesser;
use JSW\Hurigana\Event\HuriganaPostParseDispatcher;
use JSW\Hurigana\Node\RPNode;
use JSW\Hurigana\Node\RTNode;
use JSW\Hurigana\Node\RubyNode;
use JSW\Hurigana\Parser\HuriganaCloseParser;
use JSW\Hurigana\Parser\HuriganaOpenParser;
use JSW\Hurigana\Renderer\RPNodeRenderer;
use JSW\Hurigana\Renderer\RTNodeRenderer;
use JSW\Hurigana\Renderer\RubyNodeRenderer;
use JSW\Hurigana\Util\HuriganaKugiri;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

final class HuriganaExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('hurigana',
            Expect::structure([
                'use_sutegana' => Expect::bool()->default(false),
                'use_rp_tag' => Expect::bool()->default(false),
            ])
        );
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $patterns = new HuriganaKugiri();
        $priority = 100;

        // インラインパーサ登録
        $environment->addInlineParser(new HuriganaCloseParser(), $priority);
        // JSW\Hurigana\Util\HuriganaKugiriのパターンをパーサに注入する
        foreach ($patterns->getKugiri() as $pattern) {
            $environment->addInlineParser(new HuriganaOpenParser($pattern), $priority);
            --$priority;
        }

        // 区切り文字プロセサ登録
        $environment->addDelimiterProcessor(new HuriganaDelimiterProcesser());

        // イベントディスパッチャ登録
        $class = DocumentParsedEvent::class;
        $dispatch = new HuriganaPostParseDispatcher();
        $environment->addEventListener($class, [$dispatch, 'useSutegana'])
                    ->addEventListener($class, [$dispatch, 'useRPTag']);

        // レンダラ登録
        $environment->addRenderer(RTNode::class, new RTNodeRenderer())
                    ->addRenderer(RubyNode::class, new RubyNodeRenderer())
                    ->addRenderer(RPNode::class, new RPNodeRenderer());
    }
}
