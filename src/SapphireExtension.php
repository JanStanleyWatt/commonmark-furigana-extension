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

namespace JSW\Sapphire;

use JSW\Sapphire\Delimiter\SapphireDelimiterProcesser;
use JSW\Sapphire\Event\SapphirePostParseDispatcher;
use JSW\Sapphire\Node\RPNode;
use JSW\Sapphire\Node\RTNode;
use JSW\Sapphire\Node\RubyNode;
use JSW\Sapphire\Parser\SapphireCloseParser;
use JSW\Sapphire\Parser\SapphireOpenParser;
use JSW\Sapphire\Renderer\RPNodeRenderer;
use JSW\Sapphire\Renderer\RTNodeRenderer;
use JSW\Sapphire\Renderer\RubyNodeRenderer;
use JSW\Sapphire\Util\SapphireKugiri;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

final class SapphireExtension implements ConfigurableExtensionInterface
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
        $patterns = new SapphireKugiri();
        $priority = 100;

        // インラインパーサ登録
        $environment->addInlineParser(new SapphireCloseParser(), $priority);
        // JSW\Sapphire\Util\SapphireKugiriのパターンをパーサに注入する
        foreach ($patterns->getKugiri() as $pattern) {
            $environment->addInlineParser(new SapphireOpenParser($pattern), $priority);
            --$priority;
        }

        // 区切り文字プロセサ登録
        $environment->addDelimiterProcessor(new SapphireDelimiterProcesser());

        // イベントディスパッチャ登録
        $class = DocumentParsedEvent::class;
        $dispatch = new SapphirePostParseDispatcher();
        $environment->addEventListener($class, [$dispatch, 'useSutegana'])
                    ->addEventListener($class, [$dispatch, 'useRPTag']);

        // レンダラ登録
        $environment->addRenderer(RTNode::class, new RTNodeRenderer())
                    ->addRenderer(RubyNode::class, new RubyNodeRenderer())
                    ->addRenderer(RPNode::class, new RPNodeRenderer());
    }
}
