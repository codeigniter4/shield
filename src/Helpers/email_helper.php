<?php

if (! defined('emailer')) {
    /**
     * Provides convenient access to the CodeIgniter Email class.
     *
     * @internal
     */
    function emailer(?array $overrides = null)
    {
        helper('setting');

        $config = [
            'userAgent'     => setting('Email.userAgent'),
            'protocol'      => setting('Email.protocol'),
            'mailPath'      => setting('Email.mailPath'),
            'SMTPHost'      => setting('Email.SMTPHost'),
            'SMTPUser'      => setting('Email.SMTPUser'),
            'SMTPPass'      => setting('Email.SMTPPass'),
            'SMTPPort'      => setting('Email.SMTPPort'),
            'SMTPTimeout'   => setting('Email.SMTPTimeout'),
            'SMTPKeepAlive' => setting('Email.SMTPKeepAlive'),
            'SMTPCrypto'    => setting('Email.SMTPCrypto'),
            'wordWrap'      => setting('Email.wordWrap'),
            'wrapChars'     => setting('Email.wrapChars'),
            'mailType'      => setting('Email.mailType'),
            'charset'       => setting('Email.charset'),
            'validate'      => setting('Email.validate'),
            'priority'      => setting('Email.priority'),
            'CRLF'          => setting('Email.CRLF'),
            'newline'       => setting('Email.newline'),
            'BCCBatchMode'  => setting('Email.BCCBatchMode'),
            'BCCBatchSize'  => setting('Email.BCCBatchSize'),
            'DSN'           => setting('Email.DSN'),
        ];

        if (is_array($overrides) && count($overrides)) {
            $config = array_merge($overrides, $config);
        }

        return service('email')
            ->initialize($config);
    }
}
