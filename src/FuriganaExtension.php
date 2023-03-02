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

namespace JSW\Furigana;

use JSW\Furigana\Delimiter\FuriganaDelimiterProcesser;
use JSW\Furigana\Node\Ruby;
use JSW\Furigana\Node\RubyParentheses;
use JSW\Furigana\Node\RubyText;
use JSW\Furigana\Parser\FuriganaCloseParser;
use JSW\Furigana\Parser\FuriganaOpenParser;
use JSW\Furigana\Parser\RubyTextParser;
use JSW\Furigana\Renderer\RubyParenthesesRenderer;
use JSW\Furigana\Renderer\RubyRenderer;
use JSW\Furigana\Renderer\RubyTextRenderer;
use JSW\Furigana\Util\FuriganaKugiri;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

final class FuriganaExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('furigana',
            Expect::structure([
                'use_sutegana' => Expect::bool()->default(false),
                'use_rp_tag' => Expect::bool()->default(false),
            ])
        );
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $patterns = new FuriganaKugiri();
        $priority = 100;

        // インラインパーサ登録
        $environment->addInlineParser(new FuriganaCloseParser(), $priority)
                    ->addInlineParser(new RubyTextParser(), $priority);
        // JSW\Furigana\Util\FuriganaKugiriのパターンをパーサに注入する
        foreach ($patterns->getKugiri() as $pattern) {
            $environment->addInlineParser(new FuriganaOpenParser($pattern), $priority);
            --$priority;
        }

        // 区切り文字プロセサ登録
        $environment->addDelimiterProcessor(new FuriganaDelimiterProcesser());

        // レンダラ登録
        $environment->addRenderer(RubyText::class, new RubyTextRenderer())
                    ->addRenderer(Ruby::class, new RubyRenderer())
                    ->addRenderer(RubyParentheses::class, new RubyParenthesesRenderer());
    }
}
