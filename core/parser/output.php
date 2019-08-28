<?php

/**
 * Output Parser
 *
 * Copyright 2016-2019 Jerry Shaw <jerry-shaw@live.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace core\parser;

use core\system;

class output extends system
{
    //Pretty format
    const MIME = [
        'json' => 'application/json',
        'xml'  => 'application/xml'
    ];

    //Response MIME type (UTF-8, default: json)
    private static $pretty = false;

    /**
     * Flush output content
     */
    public static function flush(): void
    {
        //Set pretty mode
        if (0 < parent::$err_lv) {
            self::$pretty = true;
        }

        //Count results
        $count = count(parent::$result);

        //Build output result
        if (!empty(parent::$error)) {
            //Merge error data
            switch ($count) {
                case 0:
                    parent::$result = parent::$error;
                    break;
                case 1:
                    parent::$result = parent::$error + ['data' => current(parent::$result)];
                    break;
                default:
                    parent::$result = parent::$error + ['data' => parent::$result];
                    break;
            }
        } elseif (1 === $count) {
            //Reduce result data
            parent::$result = current(parent::$result);
        }

        //Check MIME-Type
        $type = isset(self::MIME[parent::$mime]) ? parent::$mime : 'json';

        //Header Content-Type
        !headers_sent() && header('Content-Type: ' . self::MIME[$type] . '; charset=UTF-8');

        //Output results
        echo self::{'format_' . $type}();

        if (parent::$is_CLI) {
            echo PHP_EOL;
        }

        //Output logs
        if ('' !== parent::$logs) {
            echo PHP_EOL . PHP_EOL . parent::$logs;
        }

        unset($count, $type);
    }

    /**
     * Format JSON
     *
     * @return string
     */
    private static function format_json(): string
    {
        return json_encode(parent::$result, self::$pretty ? JSON_PRETTY : JSON_FORMAT);
    }

    /**
     * Format XML
     *
     * @return string
     */
    private static function format_xml(): string
    {
        return data::build_xml(parent::$result, true, self::$pretty);
    }
}