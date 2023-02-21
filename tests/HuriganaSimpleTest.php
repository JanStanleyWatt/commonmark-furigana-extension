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

namespace JSW\Tests;

use JSW\Hurigana\HuriganaExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \JSW\Hurigana\Parser\HuriganaEscapeParser
 *
 * @group unit
 * @group jisage
 */
final class HuriganaSimpleTest extends TestCase
{
    private const DEFAULT_RULE = [
            'hurigana' => [
                'use_sutegana' => false,
                'use_rp_tag' => false,
            ],
    ];
    /**
     * @covers ::parse
     */
    public function testEscape(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new HuriganaExtension());
                    
        $converter = new MarkdownConverter($environment);
                    
        $expect = '<p>｜この拡張<ruby>機能<rt>きのう</rt></ruby>は素晴らしい</p>'."\n";
        $actual = $converter->convert('\｜この拡張｜機能《きのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }
}
