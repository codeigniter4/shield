<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Email\Email;

if (! defined('emailer')) {
    /**
     * Provides convenient access to the CodeIgniter Email class.
     *
     * @param array<string, mixed> $overrides Email preferences to override.
     *
     * @internal
     */
    function emailer(array $overrides = []): Email
    {
        $config = [
            'userAgent'     => setting('Email.userAgent'),
            'protocol'      => setting('Email.protocol'),
            'mailPath'      => setting('Email.mailPath'),
            'SMTPHost'      => setting('Email.SMTPHost'),
            'SMTPUser'      => setting('Email.SMTPUser'),
            'SMTPPass'      => setting('Email.SMTPPass'),
            'SMTPPort'      => (int) setting('Email.SMTPPort'),
            'SMTPTimeout'   => (int) setting('Email.SMTPTimeout'),
            'SMTPKeepAlive' => (bool) setting('Email.SMTPKeepAlive'),
            'SMTPCrypto'    => setting('Email.SMTPCrypto'),
            'wordWrap'      => (bool) setting('Email.wordWrap'),
            'wrapChars'     => (int) setting('Email.wrapChars'),
            'mailType'      => setting('Email.mailType'),
            'charset'       => setting('Email.charset'),
            'validate'      => (bool) setting('Email.validate'),
            'priority'      => (int) setting('Email.priority'),
            'CRLF'          => setting('Email.CRLF'),
            'newline'       => setting('Email.newline'),
            'BCCBatchMode'  => (bool) setting('Email.BCCBatchMode'),
            'BCCBatchSize'  => (int) setting('Email.BCCBatchSize'),
            'DSN'           => (bool) setting('Email.DSN'),
        ];

        if ($overrides !== []) {
            $config = array_merge($config, $overrides);
        }

        /** @var Email $email */
        $email = service('email');

        return $email->initialize($config);
    }
}
