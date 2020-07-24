<?php
/***********************************************
* File      :   syncsendmail.php
* Project   :   Z-Push
* Descr     :   WBXML sendmail entities that
*               can be parsed directly (as a
*               stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   30.01.2012
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

class SyncSendMail extends SyncObject
{
    public $clientid;
    public $saveinsent;
    public $replacemime;
    public $accountid;
    public $source;
    public $mime;
    public $replyflag;
    public $forwardflag;

    function __construct()
    {
        $mapping = [
                    SYNC_COMPOSEMAIL_CLIENTID                             => [  self::STREAMER_VAR      => "clientid"],

                    SYNC_COMPOSEMAIL_SAVEINSENTITEMS                      => [  self::STREAMER_VAR      => "saveinsent",
                                                                                      self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY],

                    SYNC_COMPOSEMAIL_REPLACEMIME                          => [  self::STREAMER_VAR      => "replacemime",
                                                                                      self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY],

                    SYNC_COMPOSEMAIL_ACCOUNTID                            => [  self::STREAMER_VAR      => "accountid"],

                    SYNC_COMPOSEMAIL_SOURCE                               => [  self::STREAMER_VAR      => "source",
                                                                                      self::STREAMER_TYPE     => "SyncSendMailSource"],

                    SYNC_COMPOSEMAIL_MIME                                 => [  self::STREAMER_VAR      => "mime"],

                    SYNC_COMPOSEMAIL_REPLYFLAG                            => [  self::STREAMER_VAR      => "replyflag",
                                                                                      self::STREAMER_TYPE     => self::STREAMER_TYPE_IGNORE],

                    SYNC_COMPOSEMAIL_FORWARDFLAG                          => [  self::STREAMER_VAR      => "forwardflag",
                                                                                      self::STREAMER_TYPE     => self::STREAMER_TYPE_IGNORE],
        ];

        parent::__construct($mapping);
    }
}
